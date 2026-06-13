@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0 text-dark">Dashboard Gudang Pusat</h3>
        <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i> Hari ini: {{ date('d M Y') }}</span>
    </div>

    <!-- 1. Widget Ringkasan Operasional (Data Riil) -->
    <div class="row g-3 mb-4">
        <!-- Widget Stok Kritis (Indikator Safety Stock) -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Risiko Stockout</h6>
                    <h2 class="fw-bold text-danger mb-1">{{ $jumlahKritis }}</h2>
                    <p class="mb-0 small text-muted">Produk butuh reorder segera</p>
                </div>
            </div>
        </div>

        <!-- Widget Penjualan Riil -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Pendapatan Hari Ini</h6>
                    <h2 class="fw-bold text-primary mb-1">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h2>
                    <p class="mb-0 small text-muted">Total transaksi B2B & B2C</p>
                </div>
            </div>
        </div>

        <!-- Widget Depresiasi (Risiko Kerugian) -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Potensi Kerugian</h6>
                    <h2 class="fw-bold text-warning mb-1">Rp {{ number_format($potensiKerugian, 0, ',', '.') }}</h2>
                    <p class="mb-0 small text-muted">Stok mendekati expired/cukai lama</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 2. Tabel Monitoring Inventaris Terpusat -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Monitoring Stok Kritis</h6>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Stok</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Produk</th>
                                    <th>Stok</th>
                                    <th>Safety Stock</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stokKritis as $item)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $item->nama_barang }}</td>
                                    <td>{{ $item->stok_aktual }} <small class="text-muted">pcs</small></td>
                                    <td class="text-primary fw-bold">{{ $item->nilai_ss }} <small class="text-muted">pcs</small></td>
                                    <td><span class="badge bg-danger rounded-pill">Kritis</span></td>
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

        <!-- 3. Sidebar Navigasi Admin Cepat -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Menu Cepat</h6>
                    <div class="d-grid gap-2">
                        <a href="/admin/safety-stock" class="btn btn-primary text-start py-2">
                            <i class="fas fa-calculator me-2"></i> Hitung Safety Stock
                        </a>
                        <a href="/admin/products" class="btn btn-outline-dark text-start py-2">
                            <i class="fas fa-boxes me-2"></i> Kelola Inventaris
                        </a>
                        <a href="/admin/reports" class="btn btn-outline-dark text-start py-2">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Laporan Penjualan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection