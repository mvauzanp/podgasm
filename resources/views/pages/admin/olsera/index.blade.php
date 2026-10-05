@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-sm-center mb-3 flex-column flex-sm-row gap-2">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;"><i class="fas fa-cash-register text-primary me-2"></i> Integrasi Olsera POS &amp; Stok Realtime</h4>
            <p class="text-muted small mb-0">Kelola sinkronisasi otomatis katalog, stok fisik cabang, polling 5 menit, dan push transaksi penjualan Olsera.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm fw-semibold" id="btnTestConnection" onclick="testOlseraConnection()" style="font-size: 0.8rem;">
                <i class="fas fa-satellite-dish me-1.5 text-info"></i> Uji Koneksi API
            </button>
            <form action="{{ route('admin.olsera.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Mulai sinkronisasi delta (perubahan terakhir) stok dari Olsera POS sekarang?');">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" style="font-size: 0.8rem;">
                    <i class="fas fa-arrows-rotate me-1.5"></i> Sinkronkan Stok (Delta)
                </button>
            </form>
            <form action="{{ route('admin.olsera.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('Mulai FULL SYNC seluruh katalog dan stok dari Olsera POS? Proses ini akan mencocokkan ulang seluruh stok dari 600+ produk.');">
                @csrf
                <input type="hidden" name="force_full" value="1">
                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3.5 shadow-sm fw-semibold" style="font-size: 0.8rem;">
                    <i class="fas fa-database me-1.5"></i> Sinkronkan Total (Full Sync)
                </button>
            </form>
        </div>
    </div>

    {{-- Status Card Banner --}}
    <div class="row g-2.5 g-md-3 mb-3">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 {{ $isMockMode ? 'bg-warning-subtle border-start border-warning border-4' : 'bg-success-subtle border-start border-success border-4' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Status Driver Olsera</div>
                        <h6 class="fw-bold mb-0.5 {{ $isMockMode ? 'text-warning' : 'text-success' }}" style="font-size: 1rem;">
                            {{ $isMockMode ? 'SIMULASI (Mock Mode)' : 'TERHUBUNG (Live API)' }}
                        </h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                            {{ $isMockMode ? 'Menunggu API Key live resmi.' : 'Terhubung langsung ke Olsera Cloud Server.' }}
                        </small>
                    </div>
                    <div class="rounded-circle p-2.5 {{ $isMockMode ? 'bg-warning text-dark' : 'bg-success text-white' }} bg-opacity-25">
                        <i class="fas {{ $isMockMode ? 'fa-vial' : 'fa-signal' }} fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Terakhir Sinkronisasi</div>
                        <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 1rem;">{{ $lastSync }}</h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Otomatis Smart Delta Polling tiap 5 menit</small>
                    </div>
                    <div class="rounded-circle p-2.5 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white border-start border-info border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Fitur Tambahan Aktif</div>
                        <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 1rem;">Smart Delta &amp; Rate-Limit Safety</h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Delta query + 429 auto exponential backoff</small>
                    </div>
                    <div class="rounded-circle p-2.5 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-bolt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KOTAK INFORMASI ARSITEKTUR API POLLING (SETIAP 5 MENIT) --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-header bg-white py-2.5 px-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark small">
                <i class="fas fa-rotate text-primary me-2"></i> Arsitektur Sinkronisasi: Automated API Polling (Delta Sync)
            </h6>
            <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                <i class="fas fa-check-circle me-1"></i> Pull-Based Active (Tiap 5 Menit)
            </span>
        </div>
        <div class="card-body pt-0 pb-3 px-3">
            <div class="row g-2.5 align-items-center">
                <div class="col-lg-7 col-12">
                    <div class="p-2.5 bg-light rounded-3 border border-light">
                        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Metode: Delta Polling</span>
                            <span class="text-muted small">|</span>
                            <span class="text-dark fw-semibold small" style="font-size: 0.78rem;">Frekuensi: Setiap 5 Menit</span>
                        </div>
                        <p class="text-secondary small mb-1.5" style="font-size: 0.78rem;">
                            Sistem secara berkala menjemput (pull) data perubahan stok dan katalog terbaru langsung dari endpoint resmi Olsera POS dengan parameter <code>updated_at_min</code>.
                        </p>
                        <div class="d-flex align-items-center gap-2 small text-muted font-monospace" style="font-size: 0.72rem;">
                            <i class="fas fa-terminal text-primary"></i>
                            <span>Scheduler: <code>Schedule::command('olsera:sync')->everyFiveMinutes()</code></span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 col-12">
                    <div class="p-2.5 bg-light rounded-3 border border-light h-100 d-flex flex-column justify-content-center">
                        <label class="fw-bold small text-muted mb-1.5" style="font-size: 0.75rem;">Endpoint Target yang Dipantau:</label>
                        <div class="d-flex flex-column gap-1 small" style="font-size: 0.78rem;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-box text-primary me-1.5"></i> Katalog &amp; Varian</span>
                                <code class="text-dark">GET /product</code>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-cubes text-info me-1.5"></i> Inventori Stok</span>
                                <code class="text-dark">GET /inventory/stock</code>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-tags text-success me-1.5"></i> Kategori Produk</span>
                                <code class="text-dark">GET /product/category</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panduan Aktivasi Live API --}}
    @if($isMockMode)
    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-light">
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2.5">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-2 flex-shrink-0">
                    <i class="fas fa-key fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold text-dark mb-0.5 small">Siap untuk Menghubungkan API Olsera Resmi?</h6>
                    <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                        Begitu Anda menerima Kredensial Resmi dari tim Olsera (App ID, Secret Key, Access Token), cukup tambahkan parameter berikut di file <code>.env</code> Anda:
                    </p>
                    <div class="p-2.5 bg-dark text-light rounded-2 font-monospace small" style="font-size: 0.75rem;">
                        OLSERA_MOCK_MODE=false<br>
                        OLSERA_API_BASE_URL=https://api.olsera.co.id/api/open-api/v1<br>
                        OLSERA_APP_ID=masukkan_app_id_anda<br>
                        OLSERA_SECRET_KEY=masukkan_secret_key_anda<br>
                        OLSERA_ACCESS_TOKEN=masukkan_access_token_anda<br>
                        OLSERA_DEFAULT_OUTLET_ID=1
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 2 KOLOM: MAPPING OUTLET & LOG RIWAYAT AKTIVITAS --}}
    <div class="row g-3 mb-2">
        {{-- Daftar Outlet Cabang Terhubung --}}
        <div class="col-lg-7 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-2.5 px-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark small"><i class="fas fa-network-wired me-2 text-primary"></i> Mapping Outlet &amp; Cabang Olsera POS</h6>
                    <span class="badge bg-secondary rounded-pill" style="font-size: 0.7rem;">{{ count($outlets) }} Terdeteksi</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2.5">Outlet ID</th>
                                    <th>Nama Cabang / Toko</th>
                                    <th>Tipe Gudang</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outlets as $o)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#OUTLET-{{ $o['id'] }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $o['name'] }}</div>
                                        <span class="badge bg-light text-muted border small">{{ $o['code'] }}</span>
                                    </td>
                                    <td>
                                        @if(!empty($o['is_central']))
                                            <span class="badge bg-primary text-white"><i class="fas fa-warehouse me-1"></i> Pusat</span>
                                        @else
                                            <span class="badge bg-info text-white"><i class="fas fa-store-alt me-1"></i> Cabang</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.olsera.sync') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="outlet_id" value="{{ $o['id'] }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-sync me-1"></i> Sync
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada outlet terdaftar di Olsera.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Log Riwayat Sinkronisasi & Webhook --}}
        <div class="col-lg-5 col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check me-2 text-primary"></i> Log Aktivitas Olsera Terkini</h6>
                    <span class="badge bg-light text-muted border">Audit Trail</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentLogs as $log)
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary mb-1">{{ $log->event ?? 'SYNC' }}</span>
                                        <div class="small fw-semibold text-dark">{{ $log->actor_type ?? 'Olsera POS' }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            {{ $log->user_agent ?? 'System Automation' }}
                                        </div>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fas fa-clock-rotate-left fa-2x opacity-25 mb-2"></i>
                                <p class="mb-0">Belum ada aktivitas mutasi Olsera yang tercatat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testOlseraConnection() {
    const btn = document.getElementById('btnTestConnection');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1.5"></i> Menghubungi Olsera...';
    btn.disabled = true;

    fetch("{{ route('admin.olsera.test-connection') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: data.mode === 'live' ? 'Koneksi Live Sukses!' : 'Driver Simulasi Aktif',
                html: `
                    <p class="mb-2">${data.message}</p>
                    <div class="p-2.5 bg-light rounded text-start small">
                        <div><strong>Mode:</strong> ${data.mode.toUpperCase()}</div>
                        <div><strong>Response Latency:</strong> ${data.latency_ms || 0} ms</div>
                        <div><strong>Outlet Terdeteksi:</strong> ${data.outlets_count || 0} Outlet</div>
                    </div>
                `,
                confirmButtonColor: '#09afb9'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Uji Koneksi Gagal',
                text: data.message,
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal melakukan request uji koneksi: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    });
}
</script>
@endpush
@endsection
