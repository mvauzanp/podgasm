@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">
                <i class="fas fa-shopping-cart text-primary me-2"></i> Manajemen Pesanan
            </h4>
            <p class="text-muted small mb-0">Kelola dan proses transaksi pesanan masuk pelanggan</p>
        </div>
        <div>
            <a href="{{ route('admin.orders.exportCSV') }}" class="btn btn-success btn-sm rounded-3 shadow-sm" style="font-size: 0.8rem;">
                <i class="fas fa-download me-1.5"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filter B2C vs B2B Tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('admin.orders.index', ['type' => 'b2c', 'status' => request('status'), 'search' => request('search')]) }}" 
           class="btn btn-sm {{ $activeType === 'b2c' ? 'btn-primary' : 'btn-outline-primary' }} position-relative px-3 py-1.5 rounded-pill fw-semibold shadow-sm" style="font-size: 0.8rem;">
            <i class="fas fa-user me-1.5"></i> Pesanan B2C (Retail)
            @if($unprocessedB2CCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    {{ $unprocessedB2CCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.orders.index', ['type' => 'b2b', 'status' => request('status'), 'search' => request('search')]) }}" 
           class="btn btn-sm {{ $activeType === 'b2b' ? 'btn-primary' : 'btn-outline-primary' }} position-relative px-3 py-1.5 rounded-pill fw-semibold shadow-sm" style="font-size: 0.8rem;">
            <i class="fas fa-briefcase me-1.5"></i> Pesanan B2B (Reseller)
            @if($unprocessedB2BCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                    {{ $unprocessedB2BCount }}
                </span>
            @endif
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="card shadow-sm mb-3 border-0 rounded-3">
        <div class="card-body p-2.5">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2">
                <input type="hidden" name="type" value="{{ $activeType }}">
                <div class="col-md-6 col-12">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Invoice, Email, atau Nama..." 
                           value="{{ request('search') }}" style="font-size: 0.8rem;">
                </div>
                <div class="col-md-4 col-sm-8 col-12">
                    <select name="status" class="form-select form-select-sm" style="font-size: 0.8rem;">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4 col-12">
                    <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3" style="font-size: 0.8rem;">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Orders -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2.5">Invoice</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="border-top">
                                <td class="ps-3 py-2.5">
                                    <span class="fw-bold text-primary" style="font-size: 0.84rem;">{{ $order->invoice_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size: 0.84rem;">{{ $order->nama_penerima ?? $order->user->name }}</div>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $order->user->name }}</small>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 0.8rem;">{{ $order->email }}</span>
                                </td>
                                <td class="fw-bold text-success" style="font-size: 0.84rem;">
                                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2 py-0.5 
                                        @if($order->status == 'pending') bg-warning text-dark
                                        @elseif($order->status == 'paid') bg-info text-white
                                        @elseif($order->status == 'shipped') bg-secondary text-white
                                        @elseif($order->status == 'completed') bg-success text-white
                                        @elseif($order->status == 'cancelled') bg-danger text-white
                                        @else bg-warning text-dark
                                        @endif" style="font-size: 0.7rem;">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                    <p class="mb-0">Belum ada pesanan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection
