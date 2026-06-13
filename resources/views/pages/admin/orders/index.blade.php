@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')

@section('content_admin')
<div class="row mb-4">
    <div class="col">
        <h1 class="h3 mb-0">
            <i class="fas fa-shopping-cart me-2"></i> Manajemen Pesanan
        </h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.orders.exportCSV') }}" class="btn btn-success btn-sm">
            <i class="fas fa-download me-2"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filter B2C vs B2B Tabs -->
<div class="d-flex gap-3 mb-4">
    <a href="{{ route('admin.orders.index', ['type' => 'b2c', 'status' => request('status'), 'search' => request('search')]) }}" 
       class="btn {{ $activeType === 'b2c' ? 'btn-primary' : 'btn-outline-primary' }} position-relative px-4 py-2 rounded-pill fw-bold transition-all shadow-sm">
        <i class="fas fa-user me-2"></i> Pesanan B2C (Retail)
        @if($unprocessedB2CCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                {{ $unprocessedB2CCount }}
            </span>
        @endif
    </a>
    <a href="{{ route('admin.orders.index', ['type' => 'b2b', 'status' => request('status'), 'search' => request('search')]) }}" 
       class="btn {{ $activeType === 'b2b' ? 'btn-primary' : 'btn-outline-primary' }} position-relative px-4 py-2 rounded-pill fw-bold transition-all shadow-sm">
        <i class="fas fa-briefcase me-2"></i> Pesanan B2B (Reseller)
        @if($unprocessedB2BCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                {{ $unprocessedB2BCount }}
            </span>
        @endif
    </a>
</div>

<!-- Filter & Search -->
<div class="card shadow-sm mb-4 border-0 rounded-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
            <input type="hidden" name="type" value="{{ $activeType }}">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Cari Invoice, Email, atau Nama Pelanggan..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Notifikasi -->
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong>
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabel Orders -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            @if($order->metode_pembayaran === 'branch_request')
                                <a href="{{ route('admin.stock-requests.show-order', $order->id) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $order->invoice_number }}
                                </a>
                            @else
                                <span class="fw-bold text-primary">{{ $order->invoice_number }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $order->nama_penerima ?? $order->user->name }}</div>
                            <small class="text-muted">{{ $order->user->name }}</small>
                        </td>
                        <td>
                            <a href="mailto:{{ $order->email }}" class="text-decoration-none">
                                {{ $order->email }}
                            </a>
                        </td>
                        <td class="fw-bold text-success">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge 
                                @if($order->status == 'pending') bg-warning
                                @elseif($order->status == 'paid') bg-info
                                @elseif($order->status == 'shipped') bg-secondary
                                @elseif($order->status == 'completed') bg-success
                                @elseif($order->status == 'cancelled') bg-danger
                                @else bg-warning
                                @endif">
                                @if($order->metode_pembayaran === 'branch_request' && $order->status === 'pending_payment')
                                    Menunggu Persetujuan
                                @elseif($order->metode_pembayaran === 'branch_request' && $order->status === 'paid')
                                    Disetujui & Diproses
                                @else
                                    {{ ucfirst($order->status) }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</small>
                        </td>
                        <td>
                            @if($order->metode_pembayaran === 'branch_request')
                                <a href="{{ route('admin.stock-requests.show-order', $order->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                    <i class="fas fa-truck-loading me-1"></i> Proses
                                </a>
                            @else
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info rounded-pill px-3 text-white shadow-sm">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3"></i>
                            <p>Belum ada pesanan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $orders->links() }}
</div>

@endsection
