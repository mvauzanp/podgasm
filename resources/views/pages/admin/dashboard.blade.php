@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0 text-dark">Dashboard Gudang Pusat</h3>
        <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i> Hari ini: {{ date('d M Y') }}</span>
    </div>

    <!-- 1. WIDGET RINGKASAN OPERASIONAL (Data Riil) -->
    <div class="row g-3 mb-4">
        <!-- Widget Stok Kritis -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Risiko Stockout</h6>
                    <h2 class="fw-bold text-danger mb-1">{{ $jumlahKritis }}</h2>
                    <p class="mb-0 small text-muted">Produk butuh reorder segera</p>
                </div>
            </div>
        </div>

        <!-- Widget Penjualan Hari Ini -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Pendapatan Hari Ini</h6>
                    <h2 class="fw-bold text-primary mb-1">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h2>
                    <p class="mb-0 small text-muted">Total transaksi B2B & B2C</p>
                </div>
            </div>
        </div>

        <!-- Widget Depresiasi -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Potensi Kerugian</h6>
                    <h2 class="fw-bold text-warning mb-1">Rp {{ number_format($potensiKerugian, 0, ',', '.') }}</h2>
                    <p class="mb-0 small text-muted">Stok mendekati expired/cukai lama</p>
                </div>
            </div>
        </div>

        <!-- Widget Baru: Pending Orders -->
        <div class="col-md-3">
            <div class="card border-start border-4 border-info shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Pending Orders</h6>
                    <h2 class="fw-bold text-info mb-1">{{ $pendingOrdersCount }}</h2>
                    <p class="mb-0 small text-muted">Rp {{ number_format($pendingOrdersValue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. STATISTIK TAMBAHAN -->
    <div class="row g-3 mb-4">
        <!-- Total Orders -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-light text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-dark mb-1">{{ $totalOrders }}</h4>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>

        <!-- Paid Orders -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-success mb-1">{{ $paidOrders }}</h4>
                    <small class="text-muted">Orders Paid</small>
                </div>
            </div>
        </div>

        <!-- Cancelled Orders -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-danger mb-1">{{ $cancelledOrders }}</h4>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-primary mb-1">{{ $totalCustomers }}</h4>
                    <small class="text-muted">Customers</small>
                </div>
            </div>
        </div>

        <!-- New Customers -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-info mb-1">{{ $newCustomersThisMonth }}</h4>
                    <small class="text-muted">New (Bulan Ini)</small>
                </div>
            </div>
        </div>

        <!-- Branches -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 text-center h-100">
                <div class="card-body py-3">
                    <h4 class="fw-bold text-warning mb-1">{{ $totalBranches }}</h4>
                    <small class="text-muted">Branches</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. REVENUE CHART (7 HARI TERAKHIR) -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Revenue Chart (7 Hari)</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- STOCK ALERTS -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bell text-warning me-2"></i>Stock Alerts</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold">Low Stock</span>
                            <span class="badge bg-warning">{{ $lowStockProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted">Produk di bawah safety stock</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold">Out of Stock</span>
                            <span class="badge bg-danger">{{ $outOfStockProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted">Produk tidak tersedia</p>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold">Almost Expired</span>
                            <span class="badge bg-info">{{ $almostExpiredProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted">Kadaluarsa < 30 hari</p>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold">Expired</span>
                            <span class="badge bg-dark">{{ $expiredProducts }}</span>
                        </div>
                        <p class="mb-0 small text-muted">Sudah melewati tanggal expired</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. TOP SELLING PRODUCTS -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-fire text-danger me-2"></i>Top 5 Produk Terlaris</h6>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
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
                                    <td class="ps-3 fw-semibold">{{ $item->product->nama_barang ?? 'Produk Dihapus' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $item->total_quantity }} pcs</span>
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
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->product->stok_aktual <= $item->product->nilai_ss)
                                                <span class="badge bg-warning">Low Stock</span>
                                            @else
                                                <span class="badge bg-success">Available</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data penjualan.</td>
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
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Monitoring Stok Kritis ({{ $jumlahKritis }})</h6>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Stok</a>
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
                                    <td class="ps-3 fw-semibold">{{ $item->nama_barang }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $item->stok_aktual }} pcs</span>
                                    </td>
                                    <td class="text-primary fw-bold">{{ $item->nilai_ss }} pcs</td>
                                    <td>
                                        @php
                                            $diff = $item->stok_aktual - $item->nilai_ss;
                                        @endphp
                                        <span class="badge bg-danger">{{ $diff }} pcs</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/admin/reorder/{{ $item->id }}" class="btn btn-sm btn-warning rounded-3 shadow-sm">
                                            <i class="fas fa-truck me-1"></i> Reorder
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aman! Belum ada produk di bawah Safety Stock.</td>
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
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Menu Cepat</h6>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <a href="/admin/safety-stock" class="btn btn-primary w-100 py-2 text-start">
                                <i class="fas fa-calculator me-2"></i> Hitung Safety Stock
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/products" class="btn btn-outline-dark w-100 py-2 text-start">
                                <i class="fas fa-boxes me-2"></i> Kelola Inventaris
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/reports" class="btn btn-outline-dark w-100 py-2 text-start">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Laporan Penjualan
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/settings" class="btn btn-outline-dark w-100 py-2 text-start">
                                <i class="fas fa-cog me-2"></i> Pengaturan Admin
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
