@extends('layouts.admin')

@section('title', 'Optimasi Safety Stock (90 Hari / 3 Bulan)')

@section('content_admin')
<div class="container-fluid p-0">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-sm-center mb-3 flex-column flex-sm-row gap-2">
        <div>
            <h4 class="fw-bold m-0 text-dark" style="font-size: 1.25rem;">
                <i class="fas fa-chart-line me-2 text-primary"></i>
                Optimasi Stok &amp; Safety Stock (Tingkat Varian)
            </h4>
            <p class="text-muted small mb-0">
                <i class="fas fa-clock text-info me-1"></i> Data Historis: <strong>90 Hari Terakhir (3 Bulan)</strong> &bull; Lead Time: <strong>4 Hari</strong> &bull; Auto-sync: <strong>00:00 WIB</strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#importOlseraModal">
                <i class="fas fa-file-excel me-1.5 text-success"></i> Impor Laporan Olsera (.xlsx/.csv)
            </button>
            <form action="{{ route('admin.ss.calculate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 shadow-sm fw-semibold" style="font-size: 0.8rem;" title="Jalankan perhitungan safety stock manual sekarang">
                    <i class="fas fa-sync-alt me-1.5"></i> Kalkulasi &amp; Sinkronkan Sekarang
                </button>
            </form>
        </div>
    </div>

    {{-- BANNER FORMULA SAFETY STOCK --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%);">
        <div class="card-body p-3 text-white">
            <div class="d-flex align-items-start gap-2.5">
                <div class="bg-white bg-opacity-20 p-2.5 rounded-2 flex-shrink-0">
                    <i class="fas fa-database fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h6 class="fw-bold mb-0 small text-white">Safety Stock Statistik Berbasis Data Historis 90 Hari (3 Bulan)</h6>
                        <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">Sumber: Olsera POS &amp; E-Commerce</span>
                        <span class="badge bg-light text-dark px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">Lead Time: 4 Hari</span>
                    </div>
                    <p class="mb-2 opacity-90 small" style="font-size: 0.78rem;">
                        Sistem mengagregasi data penjualan riil selama <strong>90 hari ke belakang (3 bulan)</strong> secara terpusat dari Olsera POS &amp; E-Commerce untuk menghitung deviasi standar fluktuasi permintaan ($\sigma$) dan menentukan <strong>Service Level</strong> berdasarkan Analisis Pareto ABC.
                    </p>
                    <div class="bg-white bg-opacity-10 p-2 rounded-2 border border-white border-opacity-10 d-inline-block">
                        <span class="badge bg-light text-dark me-1.5 fw-bold" style="font-size: 0.68rem;">Formula Statistik</span>
                        <span class="text-white small fw-semibold" style="font-size: 0.78rem;">Safety Stock (SS) = Z &times; &sigma;<sub>(90 hari)</sub> &times; &radic;4 Hari</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN KLASIFIKASI KARTU ABC (90 HARI) --}}
    <div class="row g-2.5 g-md-3 mb-3">
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 border-start border-danger border-4">
                <div class="card-body p-2.5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-muted small text-uppercase mb-0" style="font-size: 0.72rem;">Kelas A (Fast Moving)</h6>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Top 70% Sales</span>
                    </div>
                    <h6 class="fw-bold m-0 text-danger" style="font-size: 0.95rem;">Service Level 98% <span class="text-muted fw-normal small">(Z = 2.05)</span></h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Prioritas tertinggi &bull; Stok pengaman maksimal</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 border-start border-warning border-4">
                <div class="card-body p-2.5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-muted small text-uppercase mb-0" style="font-size: 0.72rem;">Kelas B (Medium Moving)</h6>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Next 20% Sales</span>
                    </div>
                    <h6 class="fw-bold m-0 text-warning" style="font-size: 0.95rem;">Service Level 95% <span class="text-muted fw-normal small">(Z = 1.65)</span></h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Penjualan moderat &bull; Stok pengaman standar</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 border-start border-info border-4">
                <div class="card-body p-2.5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="text-muted small text-uppercase mb-0" style="font-size: 0.72rem;">Kelas C (Slow Moving)</h6>
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Sisa 10% Sales</span>
                    </div>
                    <h6 class="fw-bold m-0 text-info" style="font-size: 0.95rem;">Service Level 90% <span class="text-muted fw-normal small">(Z = 1.28)</span></h6>
                    <small class="text-muted" style="font-size: 0.72rem;">Barang slow moving &bull; Stok pengaman efisien</small>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm rounded-3 border-0" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fs-6"></i>
            <div class="small fw-semibold">{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm rounded-3 border-0" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2 fs-6"></i>
            <div class="small fw-semibold">{{ $errors->first() }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- TABEL DATA SAFETY STOCK --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 py-2.5 px-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark small">Daftar Produk &amp; Safety Stock (Berdasarkan Data 90 Hari / 3 Bulan)</h6>
            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                Total SKU: {{ $products->sum(fn($p) => $p->hasVariants() ? $p->variants->count() : 1) }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">

                {{-- HEADER --}}
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3 py-2.5" style="min-width: 180px;">Produk / Varian</th>
                        <th class="text-center py-2.5">Stok Aktual</th>
                        <th class="text-center py-2.5">Lead Time</th>
                        <th class="text-center py-2.5">Kelas ABC</th>
                        <th class="text-center py-2.5">Total Sales (90 Hari)</th>
                        <th class="text-center py-2.5">Rata-rata/Hari</th>
                        <th class="text-center py-2.5">Deviasi (&sigma;)</th>
                        <th class="text-center py-2.5">Safety Stock</th>
                        <th class="text-center pe-3 py-2.5" style="width: 110px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($products as $pro)
                        @if(!$pro->hasVariants())
                            {{-- PRODUK SEDERHANA (TANPA VARIAN) --}}
                            <tr class="border-top">
                                <td class="ps-3 py-2">
                                    <div class="fw-semibold text-dark" style="font-size: 0.84rem;">{{ $pro->nama_barang }}</div>
                                    <small class="text-muted" style="font-size: 0.72rem;">
                                        {{ $pro->category->nama_kategori ?? '-' }} &bull; SKU: {{ $pro->kode_barang ?? '-' }}
                                    </small>
                                </td>

                                <td class="text-center py-2">
                                    <span class="fw-bold {{ $pro->stok_aktual <= ($pro->nilai_ss ?? 0) ? 'text-danger' : 'text-success' }}" style="font-size: 0.84rem;">
                                        {{ $pro->stok_aktual }} pcs
                                    </span>
                                </td>

                                <td class="text-center fw-semibold text-dark py-2" style="font-size: 0.8rem;">
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5">4 hari</span>
                                </td>

                                <td class="text-center py-2">
                                    @if($pro->abc_class === 'A')
                                        <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas A (98%)</span>
                                    @elseif($pro->abc_class === 'B')
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas B (95%)</span>
                                    @else
                                        <span class="badge bg-info text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas C (90%)</span>
                                    @endif
                                </td>

                                <td class="text-center py-2">
                                    <span class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $pro->total_sales_90d ?? 0 }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">unit / 90 hari</small>
                                </td>

                                <td class="text-center py-2">
                                    <span class="text-dark fw-semibold" style="font-size: 0.8rem;">{{ $pro->avg_sales_90d ?? 0 }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">pcs / hari</small>
                                </td>

                                <td class="text-center py-2">
                                    <span class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $pro->std_dev_90d ?? 0 }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">&sigma; 90 hari</small>
                                </td>

                                <td class="text-center py-2">
                                    <span class="badge px-2.5 py-1 rounded-pill 
                                        {{ ($pro->nilai_ss ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}" style="font-size: 0.75rem;">
                                        {{ $pro->nilai_ss ?? 0 }} pcs
                                    </span>
                                </td>

                                <td class="text-center pe-3 py-2">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 btn-view-history" 
                                            style="font-size: 0.72rem;"
                                            data-type="simple" 
                                            data-id="{{ $pro->id }}" 
                                            title="Lihat Grafik Penjualan 90 Hari">
                                        <i class="fas fa-chart-area me-1"></i> Grafik 90 Hari
                                    </button>
                                </td>
                            </tr>
                        @else
                            {{-- PRODUK BERVARIAN --}}
                            <tr class="table-light">
                                <td colspan="9" class="ps-3 py-2 fw-bold text-dark" style="font-size: 0.84rem;">
                                    <i class="fas fa-box-open text-primary me-2"></i> {{ $pro->nama_barang }}
                                    <small class="text-muted fw-normal ms-2" style="font-size: 0.75rem;">
                                        ({{ $pro->category->nama_kategori ?? '-' }} &bull; {{ $pro->variants->count() }} Varian &bull; Akumulasi SS: <strong>{{ $pro->nilai_ss ?? 0 }} pcs</strong>)
                                    </small>
                                </td>
                            </tr>
                            @foreach($pro->variants as $var)
                                <tr class="border-top">
                                    <td class="ps-4 py-2 text-dark">
                                        <div class="d-flex align-items-center">
                                            <span class="text-muted me-2">&bull;</span>
                                            <div>
                                                <span class="fw-semibold" style="font-size: 0.82rem;">{{ $var->nama_varian }}</span>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">SKU: {{ $var->kode_barang ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center py-2">
                                        <span class="fw-bold {{ $var->stok_aktual <= ($var->nilai_ss ?? 0) ? 'text-danger' : 'text-success' }}" style="font-size: 0.82rem;">
                                            {{ $var->stok_aktual }} pcs
                                        </span>
                                    </td>

                                    <td class="text-center fw-semibold text-dark py-2" style="font-size: 0.8rem;">
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5">4 hari</span>
                                    </td>

                                    <td class="text-center py-2">
                                        @if($var->abc_class === 'A')
                                            <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas A (98%)</span>
                                        @elseif($var->abc_class === 'B')
                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas B (95%)</span>
                                        @else
                                            <span class="badge bg-info text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Kelas C (90%)</span>
                                        @endif
                                    </td>

                                    <td class="text-center py-2">
                                        <span class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $var->total_sales_90d ?? 0 }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.68rem;">unit / 90 hari</small>
                                    </td>

                                    <td class="text-center py-2">
                                        <span class="text-dark fw-semibold" style="font-size: 0.8rem;">{{ $var->avg_sales_90d ?? 0 }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.68rem;">pcs / hari</small>
                                    </td>

                                    <td class="text-center py-2">
                                        <span class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $var->std_dev_90d ?? 0 }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.68rem;">&sigma; 90 hari</small>
                                    </td>

                                    <td class="text-center py-2">
                                        <span class="badge px-2.5 py-1 rounded-pill 
                                            {{ ($var->nilai_ss ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}" style="font-size: 0.75rem;">
                                            {{ $var->nilai_ss ?? 0 }} pcs
                                        </span>
                                    </td>

                                    <td class="text-center pe-3 py-2">
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 btn-view-history" 
                                                style="font-size: 0.72rem;"
                                                data-type="variant" 
                                                data-id="{{ $var->id }}" 
                                                title="Lihat Grafik Penjualan 90 Hari">
                                            <i class="fas fa-chart-area me-1"></i> Grafik 90 Hari
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>

{{-- MODAL GRAFIK HISTORIS 90 HARI --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header bg-white py-2.5 px-3 border-bottom">
                <div>
                    <h6 class="modal-title fw-bold text-dark small" id="historyModalLabel">
                        <i class="fas fa-chart-line text-primary me-2"></i> Grafik Penjualan Historis 90 Hari (3 Bulan)
                    </h6>
                    <small class="text-muted" id="modalSkuSubtitle" style="font-size: 0.75rem;">Memuat data...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                {{-- METRIC SUMMARY BAR --}}
                <div class="row g-2 mb-3 text-center">
                    <div class="col-3">
                        <div class="bg-light p-2 rounded-2 border">
                            <span class="text-muted d-block small" style="font-size: 0.68rem;">Total Terjual (90 Hari)</span>
                            <span class="fw-bold text-dark" id="modalTotalSold" style="font-size: 1rem;">-</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-light p-2 rounded-2 border">
                            <span class="text-muted d-block small" style="font-size: 0.68rem;">Rata-rata Penjualan</span>
                            <span class="fw-bold text-primary" id="modalAvgDaily" style="font-size: 1rem;">-</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-light p-2 rounded-2 border">
                            <span class="text-muted d-block small" style="font-size: 0.68rem;">Standar Deviasi (&sigma;)</span>
                            <span class="fw-bold text-warning" id="modalStdDev" style="font-size: 1rem;">-</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="bg-success bg-opacity-10 p-2 rounded-2 border border-success border-opacity-25">
                            <span class="text-success d-block small fw-semibold" style="font-size: 0.68rem;">Safety Stock Rekomendasi</span>
                            <span class="fw-bold text-success" id="modalSsValue" style="font-size: 1rem;">-</span>
                        </div>
                    </div>
                </div>

                {{-- CHART CANVAS --}}
                <div style="height: 260px; position: relative;">
                    <canvas id="salesHistoryChart"></canvas>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 border-0">
                <small class="text-muted me-auto" style="font-size: 0.72rem;">
                    <i class="fas fa-info-circle me-1"></i> Data riil ditarik dari Olsera POS &amp; Transaksi E-Commerce Podgasm.
                </small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" style="font-size: 0.75rem;" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPOR LAPORAN PENJUALAN OLSERA --}}
<div class="modal fade" id="importOlseraModal" tabindex="-1" aria-labelledby="importOlseraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header bg-white py-2.5 px-3 border-bottom">
                <h6 class="modal-title fw-bold text-dark small" id="importOlseraModalLabel">
                    <i class="fas fa-file-excel text-success me-2"></i> Impor Laporan Penjualan Olsera Backoffice
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.ss.import-olsera') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="alert alert-info py-2 px-3 rounded-2 border-0 small mb-3" style="font-size: 0.75rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Karena Olsera membatasi ekspor maksimal <strong>31 hari per file</strong>, Anda dapat memilih <strong>1 file (30/31 hari)</strong> atau langsung memilih <strong>hingga 3 file bulanan sekaligus</strong> untuk melengkapi data 90 hari (3 bulan). Sistem akan otomatis menggabungkan seluruh datanya.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Pilih File Laporan Penjualan (Bisa 1 atau Banyak File Sekaligus)</label>
                        <input type="file" name="sales_files[]" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" multiple required>
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Mendukung format Excel (.xlsx, .xls) / CSV dengan kolom: product, variant, sku, barcode, sold_qty.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3 border-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" style="font-size: 0.75rem;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm" style="font-size: 0.75rem;">
                        <i class="fas fa-upload me-1"></i> Mulai Impor &amp; Hitung Safety Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let salesChart = null;
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

    document.querySelectorAll('.btn-view-history').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');

            document.getElementById('modalSkuSubtitle').textContent = 'Mengambil data penjualan 90 hari...';
            document.getElementById('modalTotalSold').textContent = '...';
            document.getElementById('modalAvgDaily').textContent = '...';
            document.getElementById('modalStdDev').textContent = '...';
            document.getElementById('modalSsValue').textContent = '...';

            historyModal.show();

            fetch(`/admin/safety-stock/history/${type}/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalSkuSubtitle').textContent = `${data.sku_name} (SKU: ${data.sku_code}) &bull; Lead Time: ${data.lead_time} Hari`;
                        document.getElementById('modalTotalSold').textContent = `${data.total_sold_90d} pcs`;
                        document.getElementById('modalAvgDaily').textContent = `${data.avg_daily_sales} pcs/hari`;
                        document.getElementById('modalStdDev').textContent = `${data.std_dev}`;
                        document.getElementById('modalSsValue').textContent = `${data.current_safety_stock} pcs`;

                        const labels = data.history.map(item => item.label);
                        const points = data.history.map(item => item.qty);

                        const ctx = document.getElementById('salesHistoryChart').getContext('2d');
                        if (salesChart) {
                            salesChart.destroy();
                        }

                        salesChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Penjualan Harian (pcs)',
                                    data: points,
                                    borderColor: '#0284c7',
                                    backgroundColor: 'rgba(2, 132, 199, 0.12)',
                                    borderWidth: 2,
                                    pointRadius: 2,
                                    pointHoverRadius: 5,
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: { boxWidth: 12, font: { size: 11 } }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `Terjual: ${context.parsed.y} pcs`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            maxTicksLimit: 12,
                                            font: { size: 10 }
                                        },
                                        grid: { display: false }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0,
                                            font: { size: 10 }
                                        },
                                        grid: { color: '#f1f5f9' }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(err => {
                    document.getElementById('modalSkuSubtitle').textContent = 'Gagal memuat data grafik.';
                });
        });
    });
});
</script>

@endsection