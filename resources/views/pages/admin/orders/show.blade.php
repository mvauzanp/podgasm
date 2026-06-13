@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->invoice_number)

@section('content_admin')
<div class="row mb-4">
    <div class="col">
        <h1 class="h3 mb-0">
            <i class="fas fa-receipt me-2"></i> Detail Pesanan
            <span class="text-primary fw-bold">{{ $order->invoice_number }}</span>
        </h1>
    </div>
    <div class="col-auto">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
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

<div class="row">
    <!-- Informasi Pesanan -->
    <div class="col-lg-7">
        <!-- Status Pesanan -->
        <div class="card shadow-sm mb-3 border-0 rounded-4">
            <div class="card-header bg-light py-3 border-0">
                <h5 class="mb-0 fw-bold">Status Pesanan</h5>
            </div>
            <div class="card-body">
                {{-- Default B2C form update --}}
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <select name="status" class="form-select" required>
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="pending_payment" {{ $order->status == 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                        <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm fw-bold">Update</button>
                </form>
                <div class="mt-3">
                    <p class="mb-1 text-muted small"><strong>Status Saat Ini:</strong></p>
                    <span class="badge fs-6
                        @if($order->status == 'pending') bg-warning
                        @elseif($order->status == 'pending_payment') bg-warning
                        @elseif($order->status == 'paid') bg-info
                        @elseif($order->status == 'shipped') bg-secondary
                        @elseif($order->status == 'completed') bg-success
                        @elseif($order->status == 'cancelled') bg-danger
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Informasi Pengiriman -->
        <div class="card shadow-sm mb-3 border-0 rounded-4">
            <div class="card-header bg-light py-3 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-truck me-2"></i> Informasi Pengiriman</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="fw-bold text-muted small" style="width: 35%">Nama Penerima</td>
                        <td class="fw-bold text-dark">{{ $order->nama_penerima }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted small">Email</td>
                        <td>
                            <a href="mailto:{{ $order->email }}" class="text-decoration-none fw-bold">{{ $order->email }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted small">No. Telepon</td>
                        <td>
                            <a href="tel:{{ $order->no_telp }}" class="text-decoration-none fw-bold text-dark">{{ $order->no_telp }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted small">Alamat Pengiriman</td>
                        <td class="text-dark">{{ $order->alamat_pengiriman }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-muted small">Metode Pembayaran</td>
                        <td>
                            <span class="badge bg-info px-3 py-2 rounded-pill fw-bold text-white">{{ strtoupper($order->metode_pembayaran) }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Daftar Barang -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-3">
            <div class="card-header bg-light py-3 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-box me-2"></i> Daftar Barang yang Dipesan</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Produk</th>
                            <th class="text-center" style="width: 130px;">Jumlah</th>
                            <th class="text-end" style="width: 150px;">Harga Satuan</th>
                            <th class="text-end pe-3" style="width: 150px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items as $item)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        @if($item->product->gambar)
                                            <img src="{{ asset('storage/' . $item->product->gambar) }}" 
                                                 width="40" height="40" class="rounded me-2 object-fit-cover">
                                        @else
                                            <div class="bg-light rounded me-2 border d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->product->nama_barang }}</div>
                                            @if($item->variant)
                                                <small class="badge bg-secondary text-white my-1">Varian: {{ $item->variant->nama_varian }}</small>
                                            @endif
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">SKU: {{ $item->product_variant_id ? ($item->variant->kode_barang ?? $item->product->kode_barang ?? $item->product->id) : ($item->product->kode_barang ?? $item->product->id) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-3 py-2 fw-bold">{{ $item->quantity }} Unit</span>
                                </td>
                                <td class="text-end text-muted small">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-dark pe-3">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Tidak ada item</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>/div>

        @if($order->metode_pembayaran === 'branch_request')
            </form>
        @endif
    </div>

    <!-- Ringkasan Pesanan -->
    <div class="col-lg-5">
        <!-- Info Customer -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Informasi Customer</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Nama</td>
                        <td>{{ $order->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email</td>
                        <td>
                            <a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Role</td>
                        <td>
                            <span class="badge bg-primary">{{ ucfirst($order->user->role) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Bergabung Sejak</td>
                        <td>{{ $order->user->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Ringkasan Pembayaran</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Subtotal</td>
                        <td class="text-end">
                            Rp {{ number_format(
                                $order->items->sum(fn($item) => $item->price * $item->quantity), 
                                0, ',', '.'
                            ) }}
                        </td>
                    </tr>
                    <tr class="border-top pt-3">
                        <td class="fw-bold fs-5">Total Pembayaran</td>
                        <td class="text-end fw-bold text-success fs-5">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <p class="mb-0"><strong>Pesanan Dibuat</strong></p>
                            <small class="text-muted">{{ $order->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                    @if($order->updated_at != $order->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Terakhir Diupdate</strong></p>
                                <small class="text-muted">{{ $order->updated_at->format('d M Y H:i') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 0;
}

.timeline-item {
    display: flex;
    margin-bottom: 1.5rem;
    position: relative;
    padding-left: 50px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 40px;
    width: 2px;
    height: 30px;
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.timeline-content {
    flex: 1;
}
</style>

@endsection
