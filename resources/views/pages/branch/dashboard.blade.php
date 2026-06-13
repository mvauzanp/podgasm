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

    <div class="card shadow mb-4 rounded-4 border-0">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">5 Permintaan Stok Terakhir</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Produk</th>
                            <th class="border-0">Jumlah</th>
                            <th class="border-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $req)
                        <tr>
                            <td>
                                {{ $req->product->nama_barang }}
                                @if($req->variant)
                                    <span class="text-muted small d-block">Varian: {{ $req->variant->nama_varian }}</span>
                                @endif
                            </td>
                            <td>{{ $req->jumlah }}</td>
                            <td>
                                <span class="badge {{ $req->status == 'Pending' ? 'bg-warning text-dark' : 'bg-info' }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection