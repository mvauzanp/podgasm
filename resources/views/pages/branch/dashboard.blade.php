@extends('layouts.frontend')

@section('title', 'Dashboard Cabang')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Overview Cabang</h1>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Stok Lokal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStok }} Unit</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Permintaan Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingRequests }} Permintaan</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-primary text-white shadow h-100 py-2 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-white-50 small text-uppercase">Butuh Stok?</div>
                        <div class="h6 mb-0 font-weight-bold">Ajukan ke Pusat</div>
                    </div>
                    <a href="{{ route('branch.request') }}" class="btn btn-light btn-sm rounded-pill px-3">Kirim REQ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Tabel Stok Barang Cabang --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-boxes me-2"></i> Inventori Stok Fisik di Cabang</h6>
                    <span class="badge bg-primary rounded-pill">{{ count($branchStocks ?? []) }} SKU</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0">Produk</th>
                                    <th class="border-0">Varian</th>
                                    <th class="border-0 text-end pe-4">Stok Toko</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchStocks ?? [] as $bs)
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark">{{ $bs->product->nama_barang ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $bs->variant->nama_varian ?? 'Standar (No Variant)' }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="fw-bold text-success">{{ $bs->stok_lokal }} Unit</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">Belum ada stok fisik tercatat di cabang ini. Silakan ajukan permintaan stok ke pusat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5 Permintaan Stok Terakhir --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i> Permintaan Stok Terakhir</h6>
                    <a href="{{ route('branch.tracking') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 border-0">Produk</th>
                                    <th class="border-0">Jumlah</th>
                                    <th class="border-0 text-end pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRequests as $req)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark small text-truncate" style="max-width: 140px;">{{ $req->product->nama_barang }}</div>
                                        @if($req->variant)
                                            <span class="text-muted" style="font-size: 0.72rem;">{{ $req->variant->nama_varian }}</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $req->jumlah }}</td>
                                    <td class="text-end pe-3">
                                        <span class="badge {{ $req->status == 'Pending' ? 'bg-warning text-dark' : ($req->status == 'Dikirim' ? 'bg-info text-white' : ($req->status == 'Selesai' ? 'bg-success text-white' : 'bg-danger text-white')) }}" style="font-size: 0.7rem;">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small">Belum ada riwayat permintaan stok.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection