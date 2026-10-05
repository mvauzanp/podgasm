<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BiteshipService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.biteship.com';
    protected $originAreaId;
    protected $originAddress;
    protected $originPostalCode;
    protected $originPhone;
    protected $originName;
    protected $originLatitude;
    protected $originLongitude;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key');
        $this->originAreaId = config('services.biteship.origin_area_id', 'IDNP11IDNC434IDND5427IDZ60281');
        $this->originAddress = config('services.biteship.origin_address', 'Jl. Kertajaya No.188, Kertajaya, Kec. Gubeng, Surabaya, Jawa Timur 60281');
        $this->originPostalCode = config('services.biteship.origin_postal_code', '60281');
        $this->originPhone = config('services.biteship.origin_phone', '081217195254');
        $this->originName = config('services.biteship.origin_name', 'Podgasm Kertajaya Admin');
        $this->originLatitude = (float) config('services.biteship.origin_latitude', -7.279644);
        $this->originLongitude = (float) config('services.biteship.origin_longitude', 112.759850);
    }

    /**
     * Helper untuk HTTP Client Biteship
     */
    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(10);
    }

    /**
     * Cek apakah API Key sudah dikonfigurasi
     */
    public function hasApiKey()
    {
        return !empty($this->apiKey) && $this->apiKey !== 'xxx' && $this->apiKey !== 'YOUR_BITESHIP_API_KEY';
    }

    /**
     * Pencarian Area/Wilayah Autocomplete
     * 
     * @param string $query Nama kecamatan/kota
     * @return array
     */
    public function searchAreas(string $query)
    {
        if (!$this->hasApiKey()) {
            return $this->getMockAreas($query);
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/v1/maps/areas', [
                'countries' => 'ID',
                'input' => $query,
                'type' => 'single'
            ]);

            if ($response->successful()) {
                return $response->json()['areas'] ?? [];
            }

            Log::error('Biteship searchAreas failed: ' . $response->body());
            return $this->getMockAreas($query);
        } catch (\Exception $e) {
            Log::error('Biteship searchAreas exception: ' . $e->getMessage());
            return $this->getMockAreas($query);
        }
    }

    /**
     * Resolver Kategori Cerdas untuk Biteship API (electronic, sparepart, fashion, others)
     */
    public function resolveCategory(string $productName, ?string $categoryName = null): string
    {
        $text = strtolower($productName . ' ' . ($categoryName ?? ''));

        // 1. Sparepart & Komponen Pengganti (Prioritas Tinggi): Cartridge, Coil, OCC, RTA, RDA, Drip Tip, Kaca Cadangan, Adapter
        if (preg_match('/(cartridge|catridge|coil\b|occ\b|rta\b|rda\b|rdta\b|driptip|drip tip|glass|adapter)/i', $text)) {
            return 'sparepart';
        }

        // 2. Fashion & Apparel: Lanyard, Kaos, Shirt, Hoodie, Topi, Tas, Totebag
        if (preg_match('/(lanyard|t-shirt|shirt|hoodie|apparel|topi|bag|totebag|tote)/i', $text)) {
            return 'fashion';
        }

        // 3. Electronic: Device, Mod, Pod Kit, Disposable, Baterai, Charger, Electrical Box
        if (preg_match('/(device|mod\b|pod\b|starter kit|disposable|baterai|battery|charger|electrical|aegis|drag|caliburn|oxva|ursa|thelema|centaurus|dotmod)/i', $text)) {
            return 'electronic';
        }

        // 4. Default: Liquid, Kapas (Cotton), Toolkit, Umum
        return 'others';
    }

    /**
     * Resolver Berat Defensif (dalam Gram, Integer >= 1)
     */
    public function resolveWeight($dbWeight, string $category): int
    {
        $weight = (float) $dbWeight;

        // Jika berat tersimpan dalam format kilogram desimal (< 10 kg dan > 0, misal 0.15 atau 0.25)
        if ($weight > 0 && $weight < 10) {
            $weight = $weight * 1000;
        }

        $intWeight = (int) round($weight);

        // Jika berat di DB/Olsera kosong atau <= 0, gunakan Standard Weight Matrix
        if ($intWeight <= 0) {
            return match ($category) {
                'electronic' => 250, // Rata-rata device pod 250 gram
                'sparepart'  => 50,  // Rata-rata pack coil/cartridge 50 gram
                'fashion'    => 100, // Lanyard/Kaos 100 gram
                default      => 150, // Rata-rata Liquid 60ml + botol 150 gram
            };
        }

        return max(1, $intWeight);
    }

    /**
     * Resolver Dimensi Paket (cm) berdasarkan Kategori
     */
    public function resolveDimensions(string $category): array
    {
        return match ($category) {
            'electronic' => ['length' => 12, 'width' => 8, 'height' => 4],
            'sparepart'  => ['length' => 6,  'width' => 6, 'height' => 3],
            'fashion'    => ['length' => 20, 'width' => 15, 'height' => 3],
            default      => ['length' => 5,  'width' => 5, 'height' => 15], // Liquid 60ml
        };
    }

    /**
     * Memformat CartItems menjadi array item standar Biteship
     */
    public function formatCartItems(iterable $cartItems): array
    {
        $biteshipItems = [];

        foreach ($cartItems as $item) {
            $product = $item->product ?? null;
            $variant = $item->variant ?? null;

            $itemName = $product ? ($product->nama_barang . ($variant ? ' - ' . $variant->nama_varian : '')) : 'Item Belanja';
            $categoryName = $product?->category?->nama_kategori ?? '';
            $sku = $variant?->kode_barang ?? $product?->kode_barang ?? ('SKU-' . ($product?->id ?? rand(100, 999)));
            $price = (int) ($item->price ?? $variant?->harga_jual ?? $product?->harga_jual ?? 0);
            $qty = (int) ($item->quantity ?? 1);

            $category = $this->resolveCategory($itemName, $categoryName);
            $weight = $this->resolveWeight($variant?->weight ?? $product?->weight ?? 0, $category);
            $dimensions = $this->resolveDimensions($category);

            $biteshipItems[] = [
                'name'        => mb_substr($itemName, 0, 100),
                'sku'         => $sku,
                'value'       => max(1000, $price),
                'quantity'    => max(1, $qty),
                'weight'      => $weight,
                'category'    => $category,
                'length'      => $dimensions['length'],
                'width'       => $dimensions['width'],
                'height'      => $dimensions['height'],
            ];
        }

        return $biteshipItems;
    }

    /**
     * Memformat OrderItems (saat booking pesanan Biteship) menjadi array item standar Biteship
     */
    public function formatOrderItems(iterable $orderItems): array
    {
        $biteshipItems = [];

        foreach ($orderItems as $item) {
            $product = $item->product ?? null;
            $variant = $item->variant ?? null;

            $itemName = $product ? ($product->nama_barang . ($variant ? ' - ' . $variant->nama_varian : '')) : ($item->product_name ?? 'Produk');
            $categoryName = $product?->category?->nama_kategori ?? '';
            $sku = $variant?->kode_barang ?? $product?->kode_barang ?? ('SKU-' . ($product?->id ?? $item->id));
            $price = (int) ($item->price ?? 0);
            $qty = (int) ($item->quantity ?? 1);

            $category = $this->resolveCategory($itemName, $categoryName);
            $weight = $this->resolveWeight($variant?->weight ?? $product?->weight ?? 0, $category);
            $dimensions = $this->resolveDimensions($category);

            $biteshipItems[] = [
                'name'        => mb_substr($itemName, 0, 100),
                'sku'         => $sku,
                'value'       => max(1000, $price),
                'quantity'    => max(1, $qty),
                'weight'      => $weight,
                'category'    => $category,
                'length'      => $dimensions['length'],
                'width'       => $dimensions['width'],
                'height'      => $dimensions['height'],
            ];
        }

        return $biteshipItems;
    }

    /**
     * Hitung Tarif Ongkos Kirim Real-Time (Mendukung Reguler, Kargo, dan Instant GoSend/Grab)
     * 
     * @param string $destinationAreaId Area ID tujuan
     * @param array $items List item belanja (bisa formatted array atau raw)
     * @param float|null $destinationLat Latitude tujuan (opsional, diperlukan untuk instant)
     * @param float|null $destinationLng Longitude tujuan (opsional, diperlukan untuk instant)
     * @return array
     */
    public function getRates(string $destinationAreaId, array $items, $destinationLat = null, $destinationLng = null)
    {
        // Siapkan format item untuk Biteship API secara defensif
        $biteshipItems = [];
        $totalWeight = 0;

        foreach ($items as $item) {
            $itemName = $item['name'] ?? 'Item';
            $category = $item['category'] ?? $this->resolveCategory($itemName, $item['category_name'] ?? '');
            $weight = isset($item['weight']) ? $this->resolveWeight($item['weight'], $category) : $this->resolveWeight(0, $category);
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(1000, (int) ($item['value'] ?? $item['price'] ?? 0));
            $dimensions = $this->resolveDimensions($category);

            $totalWeight += ($weight * $qty);

            $biteshipItems[] = [
                'name'        => mb_substr($itemName, 0, 100),
                'value'       => $price,
                'weight'      => $weight,
                'quantity'    => $qty,
                'category'    => $category,
                'length'      => $item['length'] ?? $dimensions['length'],
                'width'       => $item['width'] ?? $dimensions['width'],
                'height'      => $item['height'] ?? $dimensions['height'],
            ];
        }

        if (!$this->hasApiKey()) {
            return $this->getMockRates($destinationAreaId, $totalWeight, $destinationLat, $destinationLng);
        }

        try {
            $payload = [
                'origin_area_id' => $this->originAreaId,
                'destination_area_id' => $destinationAreaId,
                'couriers' => 'jne,jnt,sicepat,tiki,anteraja,gojek,grab,lalamove,paxel',
                'items' => $biteshipItems
            ];

            // Tambahkan koordinat untuk tarif kurir instant (GoSend, Grab, Lalamove)
            if (!empty($destinationLat) && !empty($destinationLng)) {
                $payload['origin_latitude'] = $this->originLatitude;
                $payload['origin_longitude'] = $this->originLongitude;
                $payload['destination_latitude'] = (float) $destinationLat;
                $payload['destination_longitude'] = (float) $destinationLng;
            }

            $response = $this->client()->post($this->baseUrl . '/v1/rates/couriers', $payload);

            if ($response->successful()) {
                $pricing = $response->json()['pricing'] ?? [];
                if (!empty($pricing)) {
                    return $pricing;
                }
            }

            Log::error('Biteship getRates failed: ' . $response->body());
            return $this->getMockRates($destinationAreaId, $totalWeight, $destinationLat, $destinationLng);
        } catch (\Exception $e) {
            Log::error('Biteship getRates exception: ' . $e->getMessage());
            return $this->getMockRates($destinationAreaId, $totalWeight, $destinationLat, $destinationLng);
        }
    }

    /**
     * Membuat Pesanan Pengiriman ke Biteship (Booking Kurir & Request Pickup)
     * 
     * @param array $orderData
     * @return array
     */
    public function createOrder(array $orderData)
    {
        if (!$this->hasApiKey()) {
            return [
                'success' => true,
                'id' => 'ord_mock_' . Str::random(10),
                'waybill_id' => 'MOCK-AWB-' . strtoupper(Str::random(10)),
                'status' => 'placed',
                'courier' => [
                    'company' => $orderData['courier_company'],
                    'type' => $orderData['courier_type'],
                    'waybill_id' => 'MOCK-AWB-' . strtoupper(Str::random(10))
                ],
                'message' => 'Simulasi Pengiriman Sukses (Mode Sandbox/Mock)'
            ];
        }

        try {
            $formattedItems = [];
            foreach ($orderData['items'] as $item) {
                $itemName = $item['name'] ?? 'Item';
                $category = $item['category'] ?? $this->resolveCategory($itemName, $item['category_name'] ?? '');
                $weight = isset($item['weight']) ? $this->resolveWeight($item['weight'], $category) : $this->resolveWeight(0, $category);
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = max(1000, (int) ($item['value'] ?? $item['price'] ?? 0));
                $dimensions = $this->resolveDimensions($category);

                $formattedItems[] = [
                    'name'        => mb_substr($itemName, 0, 100),
                    'sku'         => $item['sku'] ?? null,
                    'value'       => $price,
                    'quantity'    => $qty,
                    'weight'      => $weight,
                    'category'    => $category,
                    'length'      => $item['length'] ?? $dimensions['length'],
                    'width'       => $item['width'] ?? $dimensions['width'],
                    'height'      => $item['height'] ?? $dimensions['height'],
                ];
            }

            $payload = [
                'shipper_contact_name' => $this->originName,
                'shipper_contact_phone' => $this->originPhone,
                'shipper_contact_email' => config('mail.from.address', 'admin@podgasm.com'),
                'origin_contact_name' => $this->originName,
                'origin_contact_phone' => $this->originPhone,
                'origin_address' => $this->originAddress,
                'origin_area_id' => $this->originAreaId,
                'origin_postal_code' => (int) $this->originPostalCode,
                
                'destination_contact_name' => $orderData['recipient_name'],
                'destination_contact_phone' => $orderData['recipient_phone'],
                'destination_contact_email' => $orderData['recipient_email'] ?? 'customer@podgasm.com',
                'destination_address' => $orderData['recipient_address'],
                'destination_area_id' => $orderData['recipient_area_id'],
                
                'courier_company' => $orderData['courier_company'],
                'courier_type' => $orderData['courier_type'],
                'delivery_type' => 'now',
                'items' => $formattedItems
            ];

            // Tambahkan koordinat jika pengiriman kurir instant (GoSend, Grab, Lalamove)
            if (!empty($orderData['destination_latitude']) && !empty($orderData['destination_longitude'])) {
                $payload['origin_coordinate'] = [
                    'latitude' => $this->originLatitude,
                    'longitude' => $this->originLongitude,
                ];
                $payload['destination_coordinate'] = [
                    'latitude' => (float) $orderData['destination_latitude'],
                    'longitude' => (float) $orderData['destination_longitude'],
                ];
            }

            $response = $this->client()->post($this->baseUrl . '/v1/orders', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'id' => $data['id'] ?? null,
                    'waybill_id' => $data['courier']['waybill_id'] ?? null,
                    'status' => $data['status'] ?? 'placed',
                    'courier' => $data['courier'] ?? [],
                    'raw' => $data
                ];
            }

            Log::error('Biteship createOrder failed: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal menghubungi Biteship API.'
            ];
        } catch (\Exception $e) {
            Log::error('Biteship createOrder exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Melacak Status Paket Real-Time
     * 
     * @param string $waybillId Nomor Resi (atau tracking_id)
     * @param string $courierCode Kode kurir
     * @return array
     */
    public function getTracking(string $waybillId, string $courierCode)
    {
        if (!$this->hasApiKey() || str_starts_with($waybillId, 'MOCK-')) {
            return $this->getMockTracking($waybillId, $courierCode);
        }

        try {
            $response = $this->client()->get($this->baseUrl . "/v1/trackings/{$waybillId}/couriers/{$courierCode}");

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::error('Biteship getTracking failed: ' . $response->body());
            return $this->getMockTracking($waybillId, $courierCode);
        } catch (\Exception $e) {
            Log::error('Biteship getTracking exception: ' . $e->getMessage());
            return $this->getMockTracking($waybillId, $courierCode);
        }
    }

    /**
     * MOCK DATA: Daftar Wilayah
     */
    protected function getMockAreas(string $query)
    {
        $allAreas = [
            [
                'id' => 'IDNP3CL10',
                'name' => 'Kebayoran Baru, Jakarta Selatan, DKI Jakarta',
                'country' => 'ID',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => 12190
            ],
            [
                'id' => 'IDNP3CL12',
                'name' => 'Coblong, Bandung, Jawa Barat',
                'country' => 'ID',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'postal_code' => 40135
            ],
            [
                'id' => 'IDNP3CL15',
                'name' => 'Tegalsari, Surabaya, Jawa Timur',
                'country' => 'ID',
                'province' => 'Jawa Timur',
                'city' => 'Surabaya',
                'district' => 'Tegalsari',
                'postal_code' => 60262
            ],
            [
                'id' => 'IDNP3CL18',
                'name' => 'Medan Baru, Medan, Sumatera Utara',
                'country' => 'ID',
                'province' => 'Sumatera Utara',
                'city' => 'Medan',
                'district' => 'Medan Baru',
                'postal_code' => 20152
            ],
            [
                'id' => 'IDNP3CL20',
                'name' => 'Danurejan, Yogyakarta, DI Yogyakarta',
                'country' => 'ID',
                'province' => 'DI Yogyakarta',
                'city' => 'Yogyakarta',
                'district' => 'Danurejan',
                'postal_code' => 55211
            ],
        ];

        // Filter berdasarkan query secara sederhana
        $filtered = array_filter($allAreas, function ($area) use ($query) {
            return Str::contains(strtolower($area['name']), strtolower($query));
        });

        return array_values($filtered);
    }

    /**
     * MOCK DATA: Tarif Ongkir (Mendukung Instant GoSend/Grab & Reguler)
     */
    protected function getMockRates(string $destinationAreaId, int $totalWeight, $destinationLat = null, $destinationLng = null)
    {
        $weightKg = max(1, ceil($totalWeight / 1000));
        
        $rates = [];

        // Jika ada koordinat atau area Surabaya/Sidoarjo, sertakan kurir Instant & Same Day
        $isLocalArea = Str::contains($destinationAreaId, ['IDNP11IDNC434', 'IDNP11IDNC435', 'IDNP11IDNC436', 'IDNP3CL12']) || (!empty($destinationLat) && !empty($destinationLng));

        if ($isLocalArea) {
            // Estimasi jarak sederhana jika ada koordinat
            $distanceKm = 6;
            if (!empty($destinationLat) && !empty($destinationLng)) {
                $lat1 = $this->originLatitude;
                $lon1 = $this->originLongitude;
                $lat2 = (float) $destinationLat;
                $lon2 = (float) $destinationLng;
                $theta = $lon1 - $lon2;
                $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                $dist = acos(max(-1.0, min(1.0, $dist)));
                $dist = rad2deg($dist);
                $distanceKm = max(2, round($dist * 60 * 1.1515 * 1.609344, 1));
            }

            // Hanya tampilkan instant jika jarak <= 40 km
            if ($distanceKm <= 40) {
                $gojekPrice = max(15000, 10000 + ($distanceKm * 2500));
                $grabPrice = max(14000, 9000 + ($distanceKm * 2400));
                $lalamovePrice = max(13000, 8000 + ($distanceKm * 2200));

                $rates[] = [
                    'company' => 'gojek',
                    'courier_name' => 'GoSend',
                    'courier_service_name' => 'GoSend Instant',
                    'courier_code' => 'gojek',
                    'service_type' => 'instant',
                    'type' => 'instant',
                    'description' => 'Pengiriman instan kilat (Driver langsung antar)',
                    'duration' => '1 - 2 Jam',
                    'shipment_duration_range' => '1 - 2',
                    'shipment_duration_unit' => 'hours',
                    'price' => (int) $gojekPrice,
                ];

                $rates[] = [
                    'company' => 'grab',
                    'courier_name' => 'GrabExpress',
                    'courier_service_name' => 'GrabExpress Instant',
                    'courier_code' => 'grab',
                    'service_type' => 'instant',
                    'type' => 'instant',
                    'description' => 'Pengiriman instan Grab Bike kurir',
                    'duration' => '1 - 2 Jam',
                    'shipment_duration_range' => '1 - 2',
                    'shipment_duration_unit' => 'hours',
                    'price' => (int) $grabPrice,
                ];

                $rates[] = [
                    'company' => 'lalamove',
                    'courier_name' => 'Lalamove',
                    'courier_service_name' => 'Lalamove Motor',
                    'courier_code' => 'lalamove',
                    'service_type' => 'instant',
                    'type' => 'instant',
                    'description' => 'Layanan kurir instan motor Lalamove',
                    'duration' => '1 - 2 Jam',
                    'shipment_duration_range' => '1 - 2',
                    'shipment_duration_unit' => 'hours',
                    'price' => (int) $lalamovePrice,
                ];

                $rates[] = [
                    'company' => 'paxel',
                    'courier_name' => 'Paxel',
                    'courier_service_name' => 'Paxel Same Day',
                    'courier_code' => 'paxel',
                    'service_type' => 'same_day',
                    'type' => 'same_day',
                    'description' => 'Pengiriman kilat tiba di hari yang sama',
                    'duration' => '6 - 8 Jam',
                    'shipment_duration_range' => '6 - 8',
                    'shipment_duration_unit' => 'hours',
                    'price' => 18000 * $weightKg,
                ];
            }
        }

        // Ekspedisi Reguler & Hemat
        $rates[] = [
            'company' => 'jnt',
            'courier_name' => 'J&T Express',
            'courier_service_name' => 'J&T EZ',
            'courier_code' => 'jnt',
            'service_type' => 'standard',
            'type' => 'ez',
            'description' => 'Layanan reguler terpercaya J&T',
            'duration' => $isLocalArea ? '1 - 2 Hari' : '2 - 3 Hari',
            'shipment_duration_range' => $isLocalArea ? '1 - 2' : '2 - 3',
            'shipment_duration_unit' => 'days',
            'price' => ($isLocalArea ? 9000 : 18000) * $weightKg,
        ];

        $rates[] = [
            'company' => 'sicepat',
            'courier_name' => 'SiCepat',
            'courier_service_name' => 'SiCepat Reguler',
            'courier_code' => 'sicepat',
            'service_type' => 'standard',
            'type' => 'reg',
            'description' => 'Layanan reguler cepat SiCepat Express',
            'duration' => $isLocalArea ? '1 - 2 Hari' : '2 - 3 Hari',
            'shipment_duration_range' => $isLocalArea ? '1 - 2' : '2 - 3',
            'shipment_duration_unit' => 'days',
            'price' => ($isLocalArea ? 10000 : 19000) * $weightKg,
        ];

        $rates[] = [
            'company' => 'jne',
            'courier_name' => 'JNE',
            'courier_service_name' => 'JNE Regular',
            'courier_code' => 'jne',
            'service_type' => 'standard',
            'type' => 'reg',
            'description' => 'Layanan reguler nasional JNE Express',
            'duration' => $isLocalArea ? '1 - 2 Hari' : '2 - 4 Hari',
            'shipment_duration_range' => $isLocalArea ? '1 - 2' : '2 - 4',
            'shipment_duration_unit' => 'days',
            'price' => ($isLocalArea ? 10000 : 20000) * $weightKg,
        ];

        $rates[] = [
            'company' => 'jne',
            'courier_name' => 'JNE',
            'courier_service_name' => 'JNE YES',
            'courier_code' => 'jne',
            'service_type' => 'express',
            'type' => 'yes',
            'description' => 'JNE Yakin Esok Sampai (1 Hari Kerja)',
            'duration' => '1 Hari',
            'shipment_duration_range' => '1',
            'shipment_duration_unit' => 'days',
            'price' => ($isLocalArea ? 17000 : 34000) * $weightKg,
        ];

        $rates[] = [
            'company' => 'anteraja',
            'courier_name' => 'Anteraja',
            'courier_service_name' => 'Anteraja Regular',
            'courier_code' => 'anteraja',
            'service_type' => 'standard',
            'type' => 'reg',
            'description' => 'Layanan reguler Anteraja dengan pelacakan akurat',
            'duration' => $isLocalArea ? '1 - 2 Hari' : '2 - 3 Hari',
            'shipment_duration_range' => $isLocalArea ? '1 - 2' : '2 - 3',
            'shipment_duration_unit' => 'days',
            'price' => ($isLocalArea ? 9500 : 18500) * $weightKg,
        ];

        return $rates;
    }

    /**
     * MOCK DATA: Tracking status
     */
    protected function getMockTracking(string $waybillId, string $courierCode)
    {
        return [
            'success' => true,
            'id' => 'track_mock_123',
            'waybill_id' => $waybillId,
            'courier' => [
                'company' => $courierCode,
                'name' => strtoupper($courierCode)
            ],
            'status' => 'delivery', // placed, picking_up, picked_up, delivering, delivered
            'history' => [
                [
                    'time' => now()->subHours(2)->format('Y-m-d H:i:s'),
                    'status' => 'delivering',
                    'note' => 'Paket sedang dibawa oleh kurir menuju alamat penerima.'
                ],
                [
                    'time' => now()->subHours(6)->format('Y-m-d H:i:s'),
                    'status' => 'picked_up',
                    'note' => 'Paket telah diserahterahkan ke kurir / Agen Terdekat.'
                ],
                [
                    'time' => now()->subDay()->format('Y-m-d H:i:s'),
                    'status' => 'placed',
                    'note' => 'Pengiriman telah berhasil dijadwalkan oleh admin toko.'
                ]
            ],
            'driver' => [
                'name' => 'Rahmat Hidayat',
                'phone' => '081299887766'
            ]
        ];
    }
}
