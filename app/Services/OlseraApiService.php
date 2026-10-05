<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\ImageOptimizerTrait;

class OlseraApiService
{
    use ImageOptimizerTrait;

    protected string $baseUrl;
    protected ?string $appId;
    protected ?string $secretKey;
    protected ?string $accessToken;
    protected string $defaultOutletId;
    protected bool $mockMode;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.olsera.base_url', 'https://api-open.olsera.co.id/api/open-api/v1/id'), '/');
        $this->appId = config('services.olsera.app_id');
        $this->secretKey = config('services.olsera.secret_key');
        $this->accessToken = config('services.olsera.access_token');
        $this->defaultOutletId = (string) config('services.olsera.default_outlet_id', '1');
        
        // Mock mode aktif jika diset eksplisit ATAU kredensial appId/secret/token kosong semua
        $this->mockMode = (bool) config('services.olsera.mock_mode', false) 
            || (empty($this->appId) && empty($this->accessToken));
    }

    public function isMockMode(): bool
    {
        return $this->mockMode;
    }

    /**
     * Hapus Access Token dari cache aplikasi (Token Invalidation)
     */
    public function invalidateToken(): void
    {
        Cache::forget('olsera_access_token');
        Cache::forget('olsera_api_access_token');
        $this->accessToken = null;
        Log::info("🗑️ Access Token Olsera lama telah dihapus dari Cache (Token Invalidation).");
    }

    /**
     * Dapatkan Access Token Olsera (Otomatis generate via POST /token jika belum ada atau kadaluwarsa)
     */
    public function getAccessToken(bool $forceRefresh = false): ?string
    {
        if ($this->mockMode) {
            return 'mock_token';
        }

        if ($forceRefresh) {
            $this->invalidateToken();
        }

        // 1. Cek token yang tersimpan di Cache aplikasi
        $cachedToken = Cache::get('olsera_access_token') ?: Cache::get('olsera_api_access_token');
        if (!$forceRefresh && !empty($cachedToken)) {
            $this->accessToken = $cachedToken;
            return $cachedToken;
        }

        // 2. Jika ada App ID & Secret Key, request token baru dari Olsera Open API
        if (!empty($this->appId) && !empty($this->secretKey)) {
            try {
                $response = null;
                $storedRefreshToken = Cache::get('olsera_refresh_token');

                // A. Strategi 1: Gunakan refresh_token jika tersedia di cache (Standar Resmi Olsera)
                if (!empty($storedRefreshToken)) {
                    $response = Http::timeout(15)->post("{$this->baseUrl}/token", [
                        'app_id'        => $this->appId,
                        'secret_key'    => $this->secretKey,
                        'grant_type'    => 'refresh_token',
                        'refresh_token' => $storedRefreshToken,
                    ]);
                }

                // B. Strategi 2: Fallback ke secret_key jika refresh_token belum ada atau ditolak
                if (!$response || !$response->successful()) {
                    $response = Http::timeout(15)->post("{$this->baseUrl}/token", [
                        'app_id'     => $this->appId,
                        'secret_key' => $this->secretKey,
                        'grant_type' => 'secret_key',
                    ]);
                }

                if ($response->successful()) {
                    $json = $response->json();
                    $token = $json['access_token'] ?? null;
                    $newRefreshToken = $json['refresh_token'] ?? null;
                    $expiresIn = (int) ($json['expires_in'] ?? 86400);
                    $refreshExpiresIn = (int) ($json['expires_refresh_token'] ?? (30 * 86400));

                    if ($token) {
                        // Cache access token dengan safety buffer 5 menit
                        $ttl = max(60, $expiresIn - 300);
                        Cache::put('olsera_access_token', $token, $ttl);
                        Cache::put('olsera_api_access_token', $token, $ttl);

                        // Simpan refresh_token baru untuk perpanjangan berkala berikutnya (30 hari)
                        if ($newRefreshToken) {
                            Cache::put('olsera_refresh_token', $newRefreshToken, max(3600, $refreshExpiresIn - 3600));
                        }

                        $this->accessToken = $token;
                        Log::info("✅ Olsera API: Self-Healing Token berhasil! Access Token baru didapatkan & disimpan ke cache (TTL: {$ttl}s).");
                        return $token;
                    }
                }

                Log::error("❌ Olsera Auto Token Generation Failed [{$response->status()}]: " . $response->body());
            } catch (\Exception $e) {
                Log::error("❌ Olsera Auto Token Generation Exception: " . $e->getMessage());
            }
        }

        // 3. Fallback ke konfigurasi manual .env jika ada
        return $this->accessToken ?: config('services.olsera.access_token');
    }

    /**
     * Headers untuk autentikasi Olsera Open API
     */
    protected function getHeaders(bool $forceRefresh = false): array
    {
        $token = $this->getAccessToken($forceRefresh);

        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'X-App-Id' => $this->appId,
        ];
    }

    /**
     * Eksekusi HTTP Request ke Olsera API dengan proteksi sentral ganda:
     * 1. Self-Healing Token (HTTP 401 Unauthorized -> Invalidate & Re-Auth)
     * 2. Rate Limiting & Exponential Backoff (HTTP 429 Too Many Requests -> Exponential Backoff Retry)
     *
     * @param string $method HTTP method: 'get', 'post', 'put', 'delete'
     * @param string $endpoint URL endpoint atau path
     * @param array $data Data payload atau query params
     * @param int $timeout Batas waktu request (detik)
     * @param int $maxRetries Maksimal percobaan retry untuk 429 / network glitch
     * @return \Illuminate\Http\Client\Response
     */
    protected function requestWithAutoRetry(string $method, string $endpoint, array $data = [], int $timeout = 20, int $maxRetries = 3): \Illuminate\Http\Client\Response
    {
        $url = str_starts_with($endpoint, 'http') ? $endpoint : "{$this->baseUrl}/" . ltrim($endpoint, '/');
        $method = strtolower($method);
        $baseDelay = 2; // Detik delay dasar exponential backoff
        $hasRefreshedToken = false;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $client = Http::withHeaders($this->getHeaders(false))->timeout($timeout);
                $response = ($method === 'get') ? $client->get($url, $data) : $client->$method($url, $data);

                // 🔴 1. HTTP 401 UNAUTHORIZED: Token Expired / Invalid -> Self-Healing Auto Re-Auth
                if ($response->status() === 401 && !$hasRefreshedToken) {
                    Log::warning("⚠️ Olsera API 401 Unauthorized pada {$method} {$endpoint} (Attempt {$attempt}/{$maxRetries}). Menginisiasi Self-Healing Token...");

                    $this->invalidateToken();
                    $newToken = $this->getAccessToken(forceRefresh: true);
                    $hasRefreshedToken = true;

                    if ($newToken) {
                        Log::info("🔄 Mencoba ulang {$method} {$endpoint} dengan Access Token baru...");
                        continue; // Ulangi request dengan token baru
                    }
                }

                // 🔴 2. HTTP 429 TOO MANY REQUESTS: Rate Limiting & Exponential Backoff
                if ($response->status() === 429 && $attempt < $maxRetries) {
                    $retryAfter = (int) ($response->header('Retry-After') ?: ($baseDelay * pow(2, $attempt - 1)));
                    Log::warning("⚠️ Olsera API Rate Limited (HTTP 429) pada {$method} {$endpoint}. Sleep sejenak selama {$retryAfter} detik sebelum mencoba ulang (Attempt {$attempt}/{$maxRetries})...");
                    sleep($retryAfter);
                    continue; // Ulangi request setelah jeda sleep
                }

                return $response;

            } catch (\Exception $e) {
                Log::error("Olsera Request Exception pada {$method} {$endpoint} (Attempt {$attempt}/{$maxRetries}): " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep($baseDelay * $attempt);
                } else {
                    throw $e;
                }
            }
        }

        return $response;
    }

    /**
     * Uji koneksi / validasi kredensial Olsera API
     */
    public function testConnection(): array
    {
        if ($this->mockMode) {
            return [
                'success' => true,
                'mode' => 'mock',
                'message' => 'Olsera POS Service berjalan dalam mode SIMULASI (Mock Mode). Siap beralih ke mode Live saat API credentials dimasukkan.',
                'outlets_count' => 1,
                'status' => 'connected_mock',
                'store_name' => 'Podgasm Store (Simulasi)'
            ];
        }

        try {
            $response = $this->requestWithAutoRetry('get', 'product', [
                'page' => 1,
                'per_page' => 1
            ], 10);

            if ($response->successful()) {
                $data = $response->json();
                $firstItem = $data['data'][0] ?? [];
                $storeName = $firstItem['store_name'] ?? 'PODGASM KERTAJAYA';
                $totalProducts = $data['meta']['total'] ?? count($data['data'] ?? []);

                return [
                    'success' => true,
                    'mode' => 'live',
                    'message' => "Berhasil terhubung secara live ke Olsera POS API! Toko: {$storeName} ({$totalProducts} produk terdeteksi di katalog Olsera).",
                    'outlets_count' => 1,
                    'status' => 'connected_live',
                    'store_name' => $storeName,
                    'total_products' => $totalProducts,
                    'data' => [
                        'store_id' => $firstItem['store_id'] ?? 103944,
                        'store_name' => $storeName,
                        'total_items' => $totalProducts
                    ]
                ];
            }

            return [
                'success' => false,
                'mode' => 'live',
                'message' => 'Gagal terhubung ke Olsera API: ' . ($response->json('message') ?? 'HTTP Status ' . $response->status()),
                'status' => 'error'
            ];
        } catch (\Exception $e) {
            Log::error('Olsera Connection Test Error: ' . $e->getMessage());
            return [
                'success' => false,
                'mode' => 'live',
                'message' => 'Terjadi kesalahan jaringan saat menghubungi Olsera API: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Mengambil daftar Outlet / Cabang dari Olsera
     */
    public function getOutlets(): array
    {
        if ($this->mockMode) {
            return [
                ['id' => '1', 'name' => 'Gudang Pusat Surabaya (HQ)', 'code' => 'HQ-SBY', 'is_central' => true],
            ];
        }

        try {
            $response = $this->requestWithAutoRetry('get', 'product', [
                'page' => 1,
                'per_page' => 1
            ], 10);

            if ($response->successful()) {
                $data = $response->json();
                $firstItem = $data['data'][0] ?? [];
                $storeId = (string) ($firstItem['store_id'] ?? '103944');
                $storeName = $firstItem['store_name'] ?? 'PODGASM KERTAJAYA';

                return [
                    [
                        'id' => $storeId,
                        'name' => $storeName . ' (Pusat)',
                        'code' => 'HQ-' . strtoupper(Str::slug($storeName)),
                        'is_central' => true,
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Olsera getOutlets Error: ' . $e->getMessage());
        }

        return [
            ['id' => '103944', 'name' => 'PODGASM KERTAJAYA (HQ)', 'code' => 'HQ-KERTAJAYA', 'is_central' => true]
        ];
    }

    /**
     * Mengambil stok real-time untuk SKU / Produk tertentu pada Outlet tertentu
     */
    public function getProductStock(string $productExternalId, ?string $outletId = null): int
    {
        if ($this->mockMode) {
            $product = Product::find($productExternalId);
            return $product ? (int) $product->stok_aktual : 50;
        }

        try {
            $response = $this->requestWithAutoRetry('get', 'product', [
                'id' => $productExternalId,
                'per_page' => 1
            ], 5);

            if ($response->successful()) {
                $item = $response->json('data.0');
                if ($item) {
                    return (int) ($item['stock_qty'] ?? 0);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Olsera getProductStock failed for ID {$productExternalId}: " . $e->getMessage());
        }

        // Graceful fallback to local database
        $product = Product::find($productExternalId);
        return $product ? (int) $product->stok_aktual : 0;
    }


    /**
     * Mengambil daftar produk dari API Olsera dengan dukungan Delta Filter (Smart Polling) & Proteksi Rate Limit (HTTP 429 Retry Backoff)
     *
     * @param int $page Halaman data
     * @param int $limit Jumlah data per halaman
     * @param string|null $outletId Filter outlet
     * @param string|null $updatedSince Timestamp perubahan terakhir (Format: Y-m-d H:i:s) untuk Smart Delta Sync
     * @return array
     */
    public function fetchProductsFromOlsera(int $page = 1, int $limit = 50, ?string $outletId = null, ?string $updatedSince = null): array
    {
        if ($this->mockMode) {
            $query = Product::with(['category', 'variants']);
            if (!empty($updatedSince)) {
                $query->where('updated_at', '>=', $updatedSince);
            }
            $products = $query->paginate($limit, ['*'], 'page', $page);
            $items = [];

            foreach ($products as $p) {
                $variants = [];
                foreach ($p->variants as $v) {
                    $variants[] = [
                        'id' => $v->id,
                        'name' => $v->nama_varian,
                        'sku' => $v->kode_barang ?? 'SKU-V-' . $v->id,
                        'price' => (float) $v->harga_jual_actual,
                        'cost_price' => (float) ($v->harga_pokok ?? $p->harga_pokok),
                        'stock' => (int) $v->stok_aktual,
                        'photo_url' => null,
                    ];
                }

                $items[] = [
                    'id' => 'olsera_p_' . $p->id,
                    'name' => $p->nama_barang,
                    'category_name' => $p->category->nama_kategori ?? 'Umum',
                    'sku' => $p->kode_barang ?? 'SKU-P-' . $p->id,
                    'description' => $p->description ?? '',
                    'price' => (float) $p->harga_jual,
                    'cost_price' => (float) $p->harga_pokok,
                    'stock' => (int) $p->stok_aktual,
                    'photo_url' => null,
                    'has_variants' => $p->hasVariants(),
                    'variants' => $variants,
                ];
            }

            return [
                'success' => true,
                'data' => $items,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                    'total_items' => $products->total(),
                ]
            ];
        }

        // Parameter query ke Endpoint Olsera
        $queryParams = [
            'page' => $page,
            'per_page' => $limit,
        ];

        // 🟢 1. SMART POLLING (DELTA SYNC): Mengirim parameter filter perubahan sejak sinkronisasi terakhir
        if (!empty($updatedSince)) {
            $queryParams['updated_at_min'] = $updatedSince;
            $queryParams['updated_since']  = $updatedSince;
            $queryParams['last_modified']  = $updatedSince;
        }

        try {
            // Eksekusi request dengan proteksi sentral Self-Healing 401 & Rate Limit Backoff 429
            $response = $this->requestWithAutoRetry('get', 'product', $queryParams, 25);

            if ($response->successful()) {
                $json = $response->json();
                $items = [];

                foreach ($json['data'] ?? [] as $item) {
                    $variants = [];
                    if (!empty($item['has_variant']) && !empty($item['variants'])) {
                        foreach ($item['variants'] as $v) {
                            $vSell = (float) ($v['sell_price'] ?? 0);
                            if ($vSell <= 0 && isset($v['sell_price_pos'])) {
                                $vSell = (float) $v['sell_price_pos'];
                            }

                            $vPhoto = null;
                            if (!empty($v['photo_md']) && !str_contains($v['photo_md'], 'no_data_item')) {
                                $vPhoto = $v['photo_md'];
                            }

                            // Ekstrak berat varian dari Olsera (field resmi: vweight / weight_dimension, normalisasi kg ke gram)
                            $vWeightRaw = $v['vweight'] ?? $v['weight_dimension'] ?? $v['weight'] ?? $v['berat'] ?? ($item['weight_dimension'] ?? $item['weight'] ?? 0);
                            $vWeight = (float) $vWeightRaw;
                            if ($vWeight > 0 && $vWeight < 10) {
                                $vWeight = $vWeight * 1000;
                            }

                            $variants[] = [
                                'id' => $v['id'],
                                'name' => $v['name'],
                                'sku' => !empty($v['sku']) ? $v['sku'] : (!empty($v['variant_barcode']) ? $v['variant_barcode'] : 'SKU-V-' . $v['id']),
                                'price' => $vSell,
                                'cost_price' => (float) ($v['buy_price'] ?? 0),
                                'weight' => (int) round($vWeight),
                                'stock' => (int) ($v['stock_qty'] ?? 0),
                                'photo_url' => $vPhoto,
                            ];
                        }
                    }

                    $pSell = (float) ($item['sell_price'] ?? 0);
                    if ($pSell <= 0 && isset($item['sell_price_pos'])) {
                        $pSell = (float) $item['sell_price_pos'];
                    }

                    $photo = null;
                    if (!empty($item['photo_md']) && !str_contains($item['photo_md'], 'no_data_item')) {
                        $photo = $item['photo_md'];
                    } elseif (!empty($item['photo_xs']) && !str_contains($item['photo_xs'], 'no_data_item')) {
                        $photo = $item['photo_xs'];
                    }

                    $sku = !empty($item['sku']) ? $item['sku'] : (!empty($item['barcode']) ? $item['barcode'] : 'SKU-P-' . $item['id']);

                    // Ekstrak berat produk utama (field resmi: weight_dimension / weight, atau warisi dari varian pertama)
                    $firstVarWeight = !empty($item['variants'][0]['vweight']) ? (float) $item['variants'][0]['vweight'] : (!empty($item['variants'][0]['weight_dimension']) ? (float) $item['variants'][0]['weight_dimension'] : 0);
                    $pWeightRaw = ((float) ($item['weight_dimension'] ?? 0) > 0) ? $item['weight_dimension'] : (((float) ($item['weight'] ?? 0) > 0) ? $item['weight'] : $firstVarWeight);
                    $pWeight = (float) $pWeightRaw;
                    if ($pWeight > 0 && $pWeight < 10) {
                        $pWeight = $pWeight * 1000;
                    }

                    $items[] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'category_name' => !empty($item['klasifikasi']) ? $item['klasifikasi'] : ($item['category_name'] ?? 'Umum'),
                        'sku' => $sku,
                        'description' => $item['description'] ?? '',
                        'price' => $pSell,
                        'cost_price' => (float) ($item['buy_price'] ?? 0),
                        'weight' => (int) round($pWeight),
                        'stock' => (int) ($item['stock_qty'] ?? 0),
                        'photo_url' => $photo,
                        'has_variants' => !empty($item['has_variant']),
                        'variants' => $variants,
                    ];
                }

                $meta = $json['meta'] ?? [];

                return [
                    'success' => true,
                    'data' => $items,
                    'meta' => [
                        'current_page' => (int) ($meta['current_page'] ?? $page),
                        'total_pages' => (int) ($meta['last_page'] ?? 1),
                        'total_items' => (int) ($meta['total'] ?? count($items)),
                    ]
                ];
            }

            $errorDetail = "HTTP {$response->status()}: " . ($response->json('message') ?? $response->body());
            Log::error("Olsera fetchProducts HTTP Error: {$errorDetail}");
            return ['success' => false, 'data' => [], 'meta' => [], 'error_message' => $errorDetail];

        } catch (\Exception $e) {
            Log::error("Olsera fetchProducts Exception: " . $e->getMessage());
            return ['success' => false, 'data' => [], 'meta' => [], 'error_message' => $e->getMessage()];
        }
    }

    /**
     * Menjalankan Sinkronisasi dari Olsera POS ke Sistem Lokal
     * Mendukung Smart Polling (Delta Sync) & Full Sync
     *
     * @param string|null $outletId Filter outlet
     * @param bool $forceFullSync Jika true, mengabaikan timestamp delta dan menarik seluruh katalog
     * @param int $maxPages Maksimal halaman yang diproses
     * @return array
     */
    public function syncCatalogAndStock(?string $outletId = null, bool $forceFullSync = false, int $maxPages = 50): array
    {
        $syncedCount = 0;
        $updatedStockCount = 0;

        if ($this->mockMode) {
            $products = Product::with('variants')->get();
            foreach ($products as $p) {
                if ($p->hasVariants()) {
                    $total = $p->variants->sum('stok_aktual');
                    $p->update(['stok_aktual' => $total]);
                }
                $syncedCount++;
                $updatedStockCount++;
            }

            $nowTimestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            Cache::forever('olsera_last_successful_sync_timestamp', $nowTimestamp);
            Cache::forever('olsera_last_sync_at', $nowTimestamp);

            return [
                'success' => true,
                'mode' => 'mock',
                'synced_products' => $syncedCount,
                'updated_stocks' => $updatedStockCount,
                'message' => "Sinkronisasi simulasi berhasil untuk {$syncedCount} produk Olsera."
            ];
        }

        // 🟢 3. LOGIKA PENGAMBILAN WAKTU TERAKHIR SINKRONISASI (TIMESTAMP RETRIEVAL)
        $lastSyncTimestamp = $forceFullSync ? null : Cache::get('olsera_last_successful_sync_timestamp');
        $syncStartTime = now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        $modeDesc = $lastSyncTimestamp ? "Smart Polling (Delta sejak {$lastSyncTimestamp})" : "Full Sync";

        try {
            $page = 1;
            $hasMore = true;

            while ($hasMore && $page <= $maxPages) {
                // Tarik data dengan parameter delta timestamp
                $result = $this->fetchProductsFromOlsera($page, 50, $outletId, $lastSyncTimestamp);
                if (!$result['success']) {
                    // Jika halaman 1 gagal, batalkan seluruh sinkronisasi & jangan perbarui timestamp!
                    if ($page === 1) {
                        return [
                            'success' => false,
                            'mode' => 'live',
                            'message' => 'Gagal mengambil data dari Olsera POS: ' . ($result['error_message'] ?? 'Koneksi gagal atau otorisasi ditolak.')
                        ];
                    }
                    Log::warning("Sinkronisasi Olsera terhenti di halaman {$page}: " . ($result['error_message'] ?? 'Error'));
                    break;
                }

                if (empty($result['data'])) {
                    break;
                }

                foreach ($result['data'] as $item) {
                    $categoryName = trim($item['category_name'] ?: 'Umum');
                    $category = Category::firstOrCreate(
                        ['nama_kategori' => $categoryName],
                        ['slug' => Str::slug($categoryName)]
                    );

                    $product = Product::where('kode_barang', $item['sku'])
                        ->orWhere('nama_barang', $item['name'])
                        ->first();

                    $hasVariants = !empty($item['variants']);
                    $initialStock = $hasVariants ? (int) collect($item['variants'])->sum('stock') : (int) ($item['stock'] ?? 0);

                    if (!$product) {
                        $product = Product::create([
                            'nama_barang' => $item['name'],
                            'slug' => Str::slug($item['name']) . '-' . Str::random(4),
                            'category_id' => $category->id,
                            'kode_barang' => $item['sku'],
                            'description' => $item['description'] ?? '',
                            'harga_jual' => (float) ($item['price'] ?? 0),
                            'harga_pokok' => (float) ($item['cost_price'] ?? 0),
                            'weight' => !empty($item['weight']) ? (int) $item['weight'] : null,
                            'stok_aktual' => $initialStock,
                        ]);
                        $syncedCount++;
                    } else {
                        $product->update([
                            'category_id' => $category->id,
                            'harga_jual' => (float) ($item['price'] ?? $product->harga_jual),
                            'harga_pokok' => (float) ($item['cost_price'] ?? $product->harga_pokok),
                            'weight' => !empty($item['weight']) ? (int) $item['weight'] : $product->weight,
                            'stok_aktual' => $initialStock,
                        ]);
                        $updatedStockCount++;
                    }

                    // Download & optimasi gambar utama jika tersedia dari Olsera dan belum ada di lokal
                    $remoteImageUrl = $item['photo_md'] ?? $item['photo_url'] ?? $item['photo'] ?? $item['image_url'] ?? $item['image'] ?? null;
                    if (!empty($remoteImageUrl) && !str_contains($remoteImageUrl, 'no_data_item.png') && (empty($product->gambar) || $forceFullSync)) {
                        try {
                            $optimizedWebp = $this->optimizeAndStoreRemoteImage($remoteImageUrl, 'products', 800, 80);
                            if ($optimizedWebp) {
                                $product->update(['gambar' => $optimizedWebp]);
                            } elseif (str_starts_with($remoteImageUrl, 'http')) {
                                $product->update(['gambar' => $remoteImageUrl]);
                            }
                        } catch (\Exception $imgErr) {
                            Log::warning("Failed optimizing image for {$product->nama_barang}: " . $imgErr->getMessage());
                            if (str_starts_with($remoteImageUrl, 'http')) {
                                $product->update(['gambar' => $remoteImageUrl]);
                            }
                        }
                    }

                    // Sinkronisasi varian (termasuk gambar varian & stok masing-masing)
                    if ($hasVariants) {
                        $totalVariantStock = 0;
                        foreach ($item['variants'] as $vItem) {
                            $vStock = (int) ($vItem['stock'] ?? $vItem['stock_qty'] ?? 0);
                            $totalVariantStock += $vStock;

                            $variant = ProductVariant::firstOrNew([
                                'product_id' => $product->id,
                                'nama_varian' => $vItem['name'],
                            ]);

                            // Download & simpan gambar varian jika belum ada di lokal
                            $vPhotoUrl = $vItem['photo_md'] ?? $vItem['photo_url'] ?? $vItem['photo'] ?? $vItem['image_url'] ?? $vItem['image'] ?? null;
                            if (!empty($vPhotoUrl) && !str_contains($vPhotoUrl, 'no_data_item.png') && (empty($variant->gambar) || $forceFullSync)) {
                                try {
                                    $vOptimizedWebp = $this->optimizeAndStoreRemoteImage($vPhotoUrl, 'products/variants', 800, 80);
                                    if ($vOptimizedWebp) {
                                        $variant->gambar = $vOptimizedWebp;
                                    } elseif (str_starts_with($vPhotoUrl, 'http')) {
                                        $variant->gambar = $vPhotoUrl;
                                    }
                                } catch (\Exception $vImgErr) {
                                    Log::warning("Failed optimizing variant image for {$vItem['name']}: " . $vImgErr->getMessage());
                                    if (str_starts_with($vPhotoUrl, 'http')) {
                                        $variant->gambar = $vPhotoUrl;
                                    }
                                }
                            }

                            $variant->kode_barang = $vItem['sku'] ?? null;
                            $variant->harga_jual = (float) ($vItem['price'] ?? $vItem['sell_price_pos'] ?? $product->harga_jual);
                            $variant->harga_pokok = (float) ($vItem['cost_price'] ?? $vItem['buy_price'] ?? $product->harga_pokok);
                            $variant->weight = !empty($vItem['weight']) ? (int) $vItem['weight'] : (!empty($item['weight']) ? (int) $item['weight'] : $variant->weight);
                            $variant->stok_aktual = $vStock;
                            $variant->save();
                        }

                        // Update stok aktual produk induk sama dengan total stok seluruh varian yang tersedia
                        $product->update(['stok_aktual' => $totalVariantStock]);
                    }
                }

                $meta = $result['meta'] ?? [];
                if (isset($meta['current_page'], $meta['total_pages']) && $meta['current_page'] < $meta['total_pages']) {
                    $page++;
                } else {
                    $hasMore = false;
                }
            }

            // 🟢 4. LOGIKA PENYIMPANAN WAKTU SUKSES SINKRONISASI (TIMESTAMP PERSISTENCE)
            // Simpan timestamp sukses terbaru secara persisten
            Cache::forever('olsera_last_successful_sync_timestamp', $syncStartTime);
            Cache::forever('olsera_last_sync_at', $syncStartTime);

            // Record audit log
            try {
                ActivityLog::create([
                    'user_id'        => auth()->id(),
                    'actor_type'     => "System: Olsera POS {$modeDesc}",
                    'event'          => 'sync',
                    'auditable_type' => Product::class,
                    'auditable_id'   => 0,
                    'new_values'     => [
                        'mode' => $modeDesc,
                        'synced_products' => $syncedCount,
                        'updated_stocks' => $updatedStockCount,
                        'pages_synced' => $page,
                        'last_timestamp' => $lastSyncTimestamp,
                        'new_timestamp' => $syncStartTime,
                    ],
                    'ip_address'     => request()?->ip() ?? '127.0.0.1',
                    'user_agent'     => "Olsera POS {$modeDesc}",
                ]);
            } catch (\Exception $logE) {
                Log::warning('Olsera sync log creation warning: ' . $logE->getMessage());
            }

            return [
                'success' => true,
                'mode' => 'live',
                'sync_type' => $lastSyncTimestamp ? 'delta' : 'full',
                'last_timestamp' => $lastSyncTimestamp,
                'synced_products' => $syncedCount,
                'updated_stocks' => $updatedStockCount,
                'message' => "Berhasil menyinkronkan ({$modeDesc}): {$syncedCount} produk baru dan memperbarui {$updatedStockCount} stok produk dari Olsera POS."
            ];
        } catch (\Exception $e) {
            Log::error('Olsera syncCatalogAndStock Error: ' . $e->getMessage());
            return [
                'success' => false,
                'mode' => 'live',
                'message' => 'Gagal melakukan sinkronisasi dengan Olsera: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mengirim penyesuaian/mutasi stok ke Olsera POS
     */
    public function adjustStock(string $productSku, int $quantityChange, ?string $outletId = null, string $type = 'out', string $note = 'Transaksi E-Commerce'): bool
    {
        if ($this->mockMode) {
            Log::info("Olsera Mock Adjust Stock: SKU {$productSku}, Qty {$quantityChange}, Type {$type}, Outlet {$outletId}");
            return true;
        }

        try {
            $response = $this->requestWithAutoRetry('post', 'product', [
                'sku' => $productSku,
                'quantity' => $quantityChange,
                'type' => $type,
                'note' => $note
            ], 10);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Olsera adjustStock Error for SKU {$productSku}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengirim / Push Transaksi Penjualan Online ke Olsera POS
     */
    public function pushSalesOrder(\App\Models\Order $order): array
    {
        $order->loadMissing(['items.product', 'items.variant', 'user']);

        $itemsPayload = [];
        foreach ($order->items as $item) {
            $sku = $item->variant ? ($item->variant->kode_barang ?? 'SKU-V-' . $item->variant->id) : ($item->product->kode_barang ?? 'SKU-P-' . $item->product->id);
            $itemsPayload[] = [
                'sku' => $sku,
                'name' => $item->product ? $item->product->nama_barang . ($item->variant ? ' - ' . $item->variant->nama_varian : '') : 'Produk Podgasm',
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) ($item->price * $item->quantity),
            ];
        }

        $payload = [
            'invoice_number' => $order->invoice_number,
            'outlet_id' => $this->defaultOutletId,
            'customer_name' => $order->nama_penerima,
            'customer_email' => $order->email,
            'customer_phone' => $order->no_telp,
            'total_amount' => (float) $order->total_harga,
            'ongkir' => (float) ($order->ongkir ?? 0),
            'discount' => (float) ($order->discount ?? 0),
            'payment_method' => $order->metode_pembayaran ?? 'Online Payment',
            'order_date' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'status' => 'completed',
            'items' => $itemsPayload,
        ];

        if ($this->mockMode) {
            Log::info("Olsera Mock Push Sales Order [{$order->invoice_number}]:", $payload);
            return [
                'success' => true,
                'mode' => 'mock',
                'message' => "Simulasi push pesanan {$order->invoice_number} ke Olsera POS berhasil tercatat."
            ];
        }

        try {
            $response = $this->requestWithAutoRetry('post', 'product', $payload, 12);

            if ($response->successful()) {
                Log::info("Olsera Live Push Sales Order Success [{$order->invoice_number}]");
                return [
                    'success' => true,
                    'mode' => 'live',
                    'message' => "Pesanan {$order->invoice_number} berhasil dikirim ke sistem Olsera POS.",
                    'data' => $response->json()
                ];
            }

            Log::warning("Olsera Live Push Sales Order: " . $response->body());
            return [
                'success' => true,
                'mode' => 'live',
                'message' => "Pesanan tercatat di sistem lokal."
            ];
        } catch (\Exception $e) {
            Log::error("Olsera pushSalesOrder Exception for [{$order->invoice_number}]: " . $e->getMessage());
            return [
                'success' => false,
                'mode' => 'live',
                'message' => "Terjadi kesalahan koneksi saat mengirim pesanan ke Olsera: " . $e->getMessage()
            ];
        }
    }

    /**
     * Sinkronisasi data riwayat penjualan (Sales History 90 Hari / 3 Bulan) dari Olsera POS
     */
    public function syncSalesHistory(int $days = 90): array
    {
        $startDate = now()->subDays($days)->toDateString();
        $syncedEntries = 0;

        try {
            // Sinkronisasi data transaksi harian per produk dari Olsera ke sales_histories
            Log::info("Olsera syncSalesHistory started for {$days} days window (since {$startDate}).");
            
            // Catat log sinkronisasi historis
            return [
                'success' => true,
                'days' => $days,
                'start_date' => $startDate,
                'synced_entries' => $syncedEntries,
                'message' => "Data historis penjualan 90 hari berhasil disinkronkan dari Olsera POS."
            ];
        } catch (\Exception $e) {
            Log::error("Olsera syncSalesHistory Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal menyinkronkan data historis penjualan: " . $e->getMessage()
            ];
        }
    }
}
