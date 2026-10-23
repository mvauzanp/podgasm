<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OlseraApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OlseraSyncController extends Controller
{
    protected OlseraApiService $olseraService;

    public function __construct(OlseraApiService $olseraService)
    {
        $this->olseraService = $olseraService;
    }

    /**
     * Tampilkan halaman status dan kontrol sinkronisasi Olsera POS
     */
    public function index()
    {
        $connectionStatus = $this->olseraService->testConnection();
        $outlets = $this->olseraService->getOutlets();
        $rawLastSync = Cache::get('olsera_last_sync_at');
        if ($rawLastSync && $rawLastSync !== 'Belum pernah disinkronkan') {
            try {
                $lastSync = \Carbon\Carbon::parse($rawLastSync)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB';
            } catch (\Exception $e) {
                $lastSync = $rawLastSync . ' WIB';
            }
        } else {
            $lastSync = 'Belum pernah disinkronkan';
        }
        $isMockMode = $this->olseraService->isMockMode();
        $pollingInterval = '5 Menit';
        $pollingMethod = 'Smart Polling Delta & Webhook Dual-Way Ingestion';

        $tokenInfo = $this->olseraService->getTokenInfo();
        $persistent = $this->olseraService->loadPersistentSettings();
        $credentials = [
            'app_id'            => $persistent['app_id'] ?? config('services.olsera.app_id'),
            'secret_key'        => $persistent['secret_key'] ?? config('services.olsera.secret_key'),
            'access_token'      => $persistent['access_token'] ?? config('services.olsera.access_token'),
            'default_outlet_id' => $persistent['default_outlet_id'] ?? config('services.olsera.default_outlet_id', '103944'),
            'mock_mode'         => isset($persistent['mock_mode']) ? (bool)$persistent['mock_mode'] : (bool)config('services.olsera.mock_mode', false),
        ];

        $recentLogs = \App\Models\ActivityLog::where('actor_type', 'like', '%Olsera%')
            ->latest()
            ->take(8)
            ->get();

        return view('pages.admin.olsera.index', compact(
            'connectionStatus',
            'outlets',
            'lastSync',
            'isMockMode',
            'pollingInterval',
            'pollingMethod',
            'recentLogs',
            'tokenInfo',
            'credentials'
        ));
    }

    /**
     * Simpan pembaruan kredensial API Olsera POS
     */
    public function updateCredentials(Request $request)
    {
        $validated = $request->validate([
            'app_id'            => 'nullable|string|max:100',
            'secret_key'        => 'nullable|string|max:150',
            'access_token'      => 'nullable|string',
            'default_outlet_id' => 'nullable|string|max:50',
            'mock_mode'         => 'nullable|boolean',
        ]);

        $mockMode = $request->boolean('mock_mode');
        $validated['mock_mode'] = $mockMode;

        $this->olseraService->savePersistentSettings($validated);
        $this->olseraService->invalidateToken();

        return redirect()->route('admin.olsera.index')
            ->with('success', 'Konfigurasi & Kredensial Olsera POS berhasil disimpan.');
    }

    /**
     * Perpanjang / generate Access Token baru secara langsung via AJAX
     */
    public function refreshToken()
    {
        $start = microtime(true);
        $token = $this->olseraService->getAccessToken(forceRefresh: true);
        $latencyMs = round((microtime(true) - $start) * 1000, 2);
        $tokenInfo = $this->olseraService->getTokenInfo();

        if ($token && !$tokenInfo['is_expired']) {
            return response()->json([
                'success'    => true,
                'message'    => 'Access Token Olsera berhasil diperbarui!',
                'latency_ms' => $latencyMs,
                'token_info' => $tokenInfo,
            ]);
        }

        return response()->json([
            'success'    => false,
            'message'    => 'Gagal memperbarui token otomatis. Pastikan App ID dan Secret Key valid di halaman konsol pengembang Olsera, atau tempelkan token baru secara manual.',
            'latency_ms' => $latencyMs,
            'token_info' => $tokenInfo,
        ], 400);
    }

    /**
     * Uji koneksi langsung ke server Olsera via AJAX
     */
    public function testConnection()
    {
        $start = microtime(true);
        $result = $this->olseraService->testConnection();
        $latencyMs = round((microtime(true) - $start) * 1000, 2);
        $result['latency_ms'] = $latencyMs;

        return response()->json($result);
    }

    /**
     * Eksekusi sinkronisasi/pull stok dan produk secara manual oleh admin
     */
    public function sync(Request $request)
    {
        $outletId = $request->input('outlet_id');
        $forceFull = $request->boolean('force_full');
        $result = $this->olseraService->syncCatalogAndStock($outletId, $forceFull);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->withErrors(['msg' => $result['message']]);
    }
}
