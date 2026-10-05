@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <h4 class="fw-bold m-0 text-dark" style="font-size: 1.25rem;">Dashboard Gudang Pusat</h4>
        <small class="text-muted"><i class="fas fa-calendar-alt me-1.5"></i> Hari ini: {{ date('d M Y') }}</small>
    </div>

    <!-- 1. WIDGET RINGKASAN OPERASIONAL (Data Riil) -->
    <div class="row g-2.5 g-md-3 mb-3">
        <!-- Widget Stok Kritis -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Risiko Stockout</h6>
                    <h3 class="fw-bold text-danger mb-0.5" style="font-size: 1.4rem;">{{ $jumlahKritis }}</h3>
                    <p class="mb-0 small text-muted" style="font-size: 0.75rem;">Produk butuh reorder segera</p>
                </div>
            </div>
        </div>

        <!-- Widget Penjualan Hari Ini -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Pendapatan Hari Ini</h6>
                    <h3 class="fw-bold text-primary mb-0.5" style="font-size: 1.35rem;">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
                    <p class="mb-0 small text-muted" style="font-size: 0.75rem;">Total transaksi B2C</p>
                </div>
            </div>
        </div>

        <!-- Widget Depresiasi -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Potensi Kerugian</h6>
                    <h3 class="fw-bold text-warning mb-0.5" style="font-size: 1.35rem;">Rp {{ number_format($potensiKerugian, 0, ',', '.') }}</h3>
                    <p class="mb-0 small text-muted" style="font-size: 0.75rem;">Stok mendekati expired/cukai lama</p>
                </div>
            </div>
        </div>

        <!-- Widget Baru: Pending Orders -->
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-start border-4 border-info shadow-sm h-100">
                <div class="card-body p-3">
                    <h6 class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Pending Orders</h6>
                    <h3 class="fw-bold text-info mb-0.5" style="font-size: 1.4rem;">{{ $pendingOrdersCount }}</h3>
                    <p class="mb-0 small text-muted" style="font-size: 0.75rem;">Rp {{ number_format($pendingOrdersValue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. STATISTIK TAMBAHAN -->
    <div class="row g-2 g-md-2.5 mb-3">
        <!-- Total Orders -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-light text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-dark mb-0.5" style="font-size: 1.15rem;">{{ $totalOrders }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">Total Orders</small>
                </div>
            </div>
        </div>

        <!-- Paid Orders -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-success mb-0.5" style="font-size: 1.15rem;">{{ $paidOrders }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">Orders Paid</small>
                </div>
            </div>
        </div>

        <!-- Cancelled Orders -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-danger mb-0.5" style="font-size: 1.15rem;">{{ $cancelledOrders }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">Cancelled</small>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-primary mb-0.5" style="font-size: 1.15rem;">{{ $totalCustomers }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">Customers</small>
                </div>
            </div>
        </div>

        <!-- New Customers -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-info mb-0.5" style="font-size: 1.15rem;">{{ $newCustomersThisMonth }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">New (Bulan Ini)</small>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xxl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 text-center h-100">
                <div class="card-body py-2 px-1">
                    <h5 class="fw-bold text-warning mb-0.5" style="font-size: 1.15rem;">{{ $totalProducts }}</h5>
                    <small class="text-muted" style="font-size: 0.72rem;">Total SKU Produk</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. REVENUE CHART (7 HARI TERAKHIR) & STOCK ALERTS -->
    <div class="row g-3 mb-3">
        <div class="col-xl-8 col-lg-7 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-2.5 px-3">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-chart-line text-primary me-2"></i>Revenue Chart (7 Hari)</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="revenueChart" height="90"></canvas>
                </div>
            </div>
        </div>

        <!-- STOCK ALERTS -->
        <div class="col-xl-4 col-lg-5 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-2.5 px-3">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-bell text-warning me-2"></i>Stock Alerts</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2.5 pb-2.5 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="font-size: 0.82rem;">Low Stock</span>
                            <span class="badge bg-warning">{{ $lowStockProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted" style="font-size: 0.72rem;">Produk di bawah safety stock</p>
                    </div>

                    <div class="mb-2.5 pb-2.5 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="font-size: 0.82rem;">Out of Stock</span>
                            <span class="badge bg-danger">{{ $outOfStockProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted" style="font-size: 0.72rem;">Produk tidak tersedia</p>
                    </div>

                    <div class="mb-2.5 pb-2.5 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="font-size: 0.82rem;">Almost Expired</span>
                            <span class="badge bg-info">{{ $almostExpiredProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted" style="font-size: 0.72rem;">Kadaluarsa < 30 hari</p>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold" style="font-size: 0.82rem;">Expired</span>
                            <span class="badge bg-dark">{{ $expiredProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted" style="font-size: 0.72rem;">Sudah melewati tanggal expired</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. TOP SELLING PRODUCTS -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-fire text-danger me-2"></i>Top 5 Produk Terlaris</h6>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2.5" style="font-size: 0.75rem;">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Produk</th>
                                    <th class="text-center">Total Terjual</th>
                                    <th class="text-center">Jumlah Order</th>
                                    <th class="text-center">Stok Saat Ini</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSellingProducts as $item)
                                <tr>
                                    <td class="ps-3 fw-semibold text-truncate" style="max-width: 250px;">{{ $item->product->nama_barang ?? 'Produk Dihapus' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-2.5">{{ $item->total_quantity }} pcs</span>
                                    </td>
                                    <td class="text-center">{{ $item->order_count }}</td>
                                    <td class="text-center">
                                        @if($item->product)
                                            {{ $item->product->stok_aktual }} pcs
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->product)
                                            @if($item->product->stok_aktual <= 0)
                                                <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Out of Stock</span>
                                            @elseif($item->product->stok_aktual <= $item->product->nilai_ss)
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Low Stock</span>
                                            @else
                                                <span class="badge bg-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Available</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Belum ada data penjualan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. MONITORING STOK KRITIS -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-exclamation-circle text-danger me-2"></i>Monitoring Stok Kritis ({{ $jumlahKritis }})</h6>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2.5" style="font-size: 0.75rem;">Lihat Semua Stok</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Produk</th>
                                    <th>Stok Aktual</th>
                                    <th>Safety Stock</th>
                                    <th>Selisih</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stokKritis as $item)
                                <tr>
                                    <td class="ps-3 fw-semibold text-truncate" style="max-width: 250px;">{{ $item->nama_barang }}</td>
                                    <td>
                                        <span class="badge bg-danger rounded-pill">{{ $item->stok_aktual }} pcs</span>
                                    </td>
                                    <td class="text-primary fw-bold">{{ $item->nilai_ss }} pcs</td>
                                    <td>
                                        @php
                                            $diff = $item->stok_aktual - $item->nilai_ss;
                                        @endphp
                                        <span class="badge bg-danger rounded-pill">{{ $diff }} pcs</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.products.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fas fa-pen-to-square me-1"></i> Edit Produk
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Aman! Belum ada produk di bawah Safety Stock.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. QUICK ACTION MENU -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="fw-bold small mb-2.5">Menu Cepat Operasional</h6>
                    <div class="row g-2">
                        <div class="col-xl-3 col-sm-6 col-12">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-primary w-100 py-2 text-start rounded-3 shadow-sm" style="font-size: 0.82rem;">
                                <i class="fas fa-shopping-cart me-2"></i> Proses Pesanan Masuk
                            </a>
                        </div>
                        <div class="col-xl-3 col-sm-6 col-12">
                            <a href="{{ route('admin.olsera.index') }}" class="btn btn-outline-primary w-100 py-2 text-start rounded-3 shadow-sm" style="font-size: 0.82rem;">
                                <i class="fas fa-rotate me-2"></i> Panel Sinkron Olsera
                            </a>
                        </div>
                        <div class="col-xl-3 col-sm-6 col-12">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark w-100 py-2 text-start rounded-3 shadow-sm" style="font-size: 0.82rem;">
                                <i class="fas fa-boxes-stacked me-2"></i> Kelola Katalog Produk
                            </a>
                        </div>
                        <div class="col-xl-3 col-sm-6 col-12">
                            <a href="{{ route('admin.reports.inventory') }}" class="btn btn-outline-dark w-100 py-2 text-start rounded-3 shadow-sm" style="font-size: 0.82rem;">
                                <i class="fas fa-file-chart-column me-2"></i> Laporan Stok Barang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.JS untuk Revenue Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($last7Days) !!},
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: {!! json_encode($revenueData) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
