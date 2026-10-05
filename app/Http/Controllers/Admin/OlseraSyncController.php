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
        $pollingMethod = 'Endpoint Pull (GET /product & GET /inventory/stock)';
        
        $recentLogs = \App\Models\ActivityLog::where('actor_type', 'like', '%Olsera%')
            ->latest()
            ->take(8)
            ->get();

        return view('pages.admin.olsera.index', compact('connectionStatus', 'outlets', 'lastSync', 'isMockMode', 'pollingInterval', 'pollingMethod', 'recentLogs'));
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
