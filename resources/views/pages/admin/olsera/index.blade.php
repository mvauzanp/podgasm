@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-sm-center mb-3 flex-column flex-sm-row gap-2">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;"><i class="fas fa-cash-register text-primary me-2"></i> Integrasi Olsera POS &amp; Stok Realtime</h4>
            <p class="text-muted small mb-0">Kelola sinkronisasi otomatis katalog, stok fisik cabang, polling 5 menit, dan push transaksi penjualan Olsera.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalOlseraConfig" style="font-size: 0.8rem;">
                <i class="fas fa-sliders me-1.5"></i> Konfigurasi API &amp; Token
            </button>
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
        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 {{ $isMockMode ? 'bg-warning-subtle border-start border-warning border-4' : 'bg-success-subtle border-start border-success border-4' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Status Driver Olsera</div>
                        <h6 class="fw-bold mb-0.5 {{ $isMockMode ? 'text-warning' : 'text-success' }}" style="font-size: 0.95rem;">
                            {{ $isMockMode ? 'SIMULASI (Mock Mode)' : 'TERHUBUNG (Live API)' }}
                        </h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                            {{ $isMockMode ? 'Mode pengujian offline aktif.' : 'Terhubung ke Olsera Cloud Server.' }}
                        </small>
                    </div>
                    <div class="rounded-circle p-2.5 {{ $isMockMode ? 'bg-warning text-dark' : 'bg-success text-white' }} bg-opacity-25">
                        <i class="fas {{ $isMockMode ? 'fa-vial' : 'fa-signal' }} fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white border-start {{ !empty($tokenInfo['is_expired']) ? 'border-danger' : 'border-success' }} border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Status Access Token</div>
                        <h6 class="fw-bold mb-0.5 {{ !empty($tokenInfo['is_expired']) ? 'text-danger' : 'text-success' }}" style="font-size: 0.95rem;">
                            @if(!empty($tokenInfo['is_expired']))
                                <i class="fas fa-triangle-exclamation me-1"></i> Kadaluwarsa
                            @elseif(!empty($tokenInfo['has_token']))
                                <i class="fas fa-check-circle me-1"></i> Token Aktif
                            @else
                                <i class="fas fa-circle-question me-1"></i> Belum Diatur
                            @endif
                        </h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                            {{ $tokenInfo['time_remaining'] ?? 'Periksa kredensial' }}
                        </small>
                    </div>
                    <div class="rounded-circle p-2.5 {{ !empty($tokenInfo['is_expired']) ? 'bg-danger text-white' : 'bg-success text-white' }} bg-opacity-25">
                        <i class="fas fa-key fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Terakhir Sinkronisasi</div>
                        <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 0.95rem;">{{ $lastSync }}</h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Smart Delta Polling tiap 5 menit</small>
                    </div>
                    <div class="rounded-circle p-2.5 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white border-start border-info border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-0.5" style="font-size: 0.72rem;">Sinkronisasi 2 Arah</div>
                        <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 0.95rem;">POS &harr; Web Real-Time</h6>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Otomatis masuk Laporan Penutupan POS</small>
                    </div>
                    <div class="rounded-circle p-2.5 bg-info bg-opacity-10 text-info">
                        <i class="fas fa-arrows-split-up-and-left fs-5"></i>
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
    {{-- KOTAK INFORMASI SINKRONISASI 2 ARAH & LAPORAN PENUTUPAN PENJUALAN --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white border-start border-primary border-4">
        <div class="card-body p-3">
            <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-repeat text-primary me-2"></i> Cara Kerja Sinkronisasi Dua Arah (Omnichannel):</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-2.5 bg-light rounded-3 h-100 border border-light">
                        <div class="fw-bold text-primary small mb-1"><i class="fas fa-arrow-down-long me-1"></i> 1. Penjualan Kasir POS Olsera &rarr; Stok Web</div>
                        <p class="text-secondary small mb-0" style="font-size: 0.76rem;">
                            Ketika kasir toko fisik menyelesaikan transaksi di aplikasi Olsera POS, webhook real-time dikirimkan ke <code>/api/webhook/olsera</code>. Stok varian &amp; produk di website langsung terpotong seketika secara atomik tanpa keterlambatan.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2.5 bg-light rounded-3 h-100 border border-light">
                        <div class="fw-bold text-success small mb-1"><i class="fas fa-arrow-up-long me-1"></i> 2. Penjualan Website &rarr; Olsera POS (Laporan Penutupan)</div>
                        <p class="text-secondary small mb-0" style="font-size: 0.76rem;">
                            Ketika pesanan e-commerce terbayar di web, sistem otomatis membuat transaksi pesanan resmi di Olsera POS (Status Selesai/Z). Stok kasir Olsera terpotong dan total omset otomatis tercatat dalam <strong>Laporan Penjualan Harian &amp; Penutupan Penjualan Kasir POS</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIGURASI KREDENSIAL OLSERA POS --}}
    <div class="modal fade" id="modalOlseraConfig" tabindex="-1" aria-labelledby="modalOlseraConfigLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <form action="{{ route('admin.olsera.update-credentials') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalOlseraConfigLabel">
                            <i class="fas fa-sliders text-primary me-2"></i> Konfigurasi Kredensial Olsera POS API
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-info-circle me-1.5"></i>
                            Kredensial disimpan secara persisten di sistem. Perubahan akan langsung aktif untuk sinkronisasi stok dan penerimaan webhook tanpa merestart server.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">App ID (Client ID)</label>
                                <input type="text" name="app_id" class="form-control form-control-sm font-monospace" value="{{ $credentials['app_id'] ?? '' }}" placeholder="Contoh: GxRcmFTphzN3LBLXhhvw">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Secret Key</label>
                                <input type="password" name="secret_key" class="form-control form-control-sm font-monospace" value="{{ $credentials['secret_key'] ?? '' }}" placeholder="Masukkan secret key">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Default Outlet ID (Cabang Utama/Pusat)</label>
                                <input type="text" name="default_outlet_id" class="form-control form-control-sm font-monospace" value="{{ $credentials['default_outlet_id'] ?? '103944' }}" placeholder="Contoh: 103944">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">ID outlet Olsera Podgasm Kertajaya (Pusat): <code>103944</code></small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Mode Operasional</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="mockModeSwitch" name="mock_mode" value="1" {{ !empty($credentials['mock_mode']) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="mockModeSwitch">
                                        Gunakan Driver Simulasi (Mock Mode)
                                    </label>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">Matikan switch ini untuk menghubungkan langsung ke API Olsera Cloud.</small>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Access Token (Bearer JWT)</label>
                                    <button type="button" class="btn btn-outline-info btn-xs py-0.5 px-2 rounded-pill fw-semibold" id="btnRefreshToken" onclick="refreshOlseraToken()" style="font-size: 0.75rem;">
                                        <i class="fas fa-rotate me-1"></i> Perpanjang Token Otomatis
                                    </button>
                                </div>
                                <textarea name="access_token" rows="3" class="form-control form-control-sm font-monospace small" placeholder="eyJ0eXAiOiJKV1Qi... (Tempelkan token dari Olsera Developer Console)">{{ $credentials['access_token'] ?? '' }}</textarea>
                                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                    Masa aktif token: <strong>{{ $tokenInfo['expires_at'] ?? 'Belum ada' }}</strong> ({{ $tokenInfo['time_remaining'] ?? '-' }}).
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-semibold">
                            <i class="fas fa-floppy-disk me-1.5"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
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
function refreshOlseraToken() {
    const btn = document.getElementById('btnRefreshToken');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Merefresh...';
    btn.disabled = true;

    fetch("{{ route('admin.olsera.refresh-token') }}", {
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
                title: 'Token Berhasil Diperbarui',
                text: data.message,
                confirmButtonColor: '#09afb9'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Refresh Token Belum Berhasil',
                text: data.message,
                confirmButtonColor: '#f59e0b'
            });
        }
    })
    .catch(err => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal menghubungi server: ' + err.message,
            confirmButtonColor: '#ef4444'
        });
    });
}

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
