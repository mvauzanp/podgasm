@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->invoice_number)

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">
                <i class="fas fa-receipt text-primary me-2"></i> Detail Pesanan
                <span class="text-primary font-monospace">{{ $order->invoice_number }}</span>
            </h4>
            <p class="text-muted small mb-0">Informasi lengkap status pesanan, penerima, dan item produk</p>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm" style="font-size: 0.8rem;">
                <i class="fas fa-arrow-left me-1.5"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Notifikasi -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <strong class="small">Error!</strong>
            <ul class="mb-0 small" style="font-size: 0.78rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-1.5"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Informasi Pesanan & Items -->
        <div class="col-lg-7 col-12">
            <!-- Status Pesanan -->
            <div class="card shadow-sm mb-3 border-0 rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small">Status Pesanan</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-flex gap-2 align-items-center flex-wrap">
                        @csrf
                        <select name="status" class="form-select form-select-sm" style="font-size: 0.8rem; max-width: 200px;" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm fw-semibold" style="font-size: 0.8rem;">Update Status</button>
                    </form>
                    <div class="mt-2.5 d-flex align-items-center gap-2">
                        <span class="text-muted small" style="font-size: 0.75rem;">Status Saat Ini:</span>
                        <span class="badge rounded-pill px-2.5 py-1
                            @if($order->status == 'pending') bg-warning text-dark
                            @elseif($order->status == 'paid') bg-info text-white
                            @elseif($order->status == 'shipped') bg-secondary text-white
                            @elseif($order->status == 'completed') bg-success text-white
                            @elseif($order->status == 'cancelled') bg-danger text-white
                            @else bg-warning text-dark
                            @endif" style="font-size: 0.72rem;">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Informasi Pengiriman -->
            <div class="card shadow-sm mb-3 border-0 rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-truck text-primary me-2"></i> Informasi Pengiriman &amp; Penerima</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                            <tr>
                                <td class="fw-bold text-muted" style="width: 35%;">Nama Penerima</td>
                                <td class="fw-semibold text-dark">{{ $order->nama_penerima }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Email</td>
                                <td><a href="mailto:{{ $order->email }}" class="text-decoration-none">{{ $order->email }}</a></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">No. Telepon</td>
                                <td><a href="tel:{{ $order->no_telp }}" class="text-decoration-none text-dark">{{ $order->no_telp }}</a></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Alamat Pengiriman</td>
                                <td class="text-dark">{{ $order->alamat_pengiriman }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Metode Pembayaran</td>
                                <td><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">{{ strtoupper($order->metode_pembayaran) }}</span></td>
                            </tr>
                            @if($order->kurir)
                            <tr>
                                <td class="fw-bold text-muted">Kurir / Layanan</td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2.5 py-0.5 text-uppercase" style="font-size: 0.72rem;">{{ $order->kurir }} - {{ $order->layanan }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Ongkos Kirim</td>
                                <td class="fw-semibold text-dark">Rp {{ number_format($order->ongkir, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">No. Resi (Waybill)</td>
                                <td class="fw-bold text-primary font-monospace">{{ $order->resi ?? 'Belum ada resi' }}</td>
                            </tr>
                            @if($order->biteship_order_id)
                            <tr>
                                <td class="fw-bold text-muted">Biteship Order ID</td>
                                <td class="small text-muted font-monospace">{{ $order->biteship_order_id }}</td>
                            </tr>
                            @endif
                            @endif
                        </table>
                    </div>

                    @if($order->status == 'paid' && !$order->biteship_order_id && $order->metode_pembayaran !== 'branch_request')
                        <div class="mt-3 border-top pt-2.5">
                            <form action="{{ route('admin.orders.ship', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold py-1.5 rounded-3 shadow-sm" style="font-size: 0.8rem;">
                                    <i class="fas fa-paper-plane me-1.5"></i> Kirim ke Biteship (Request Pickup)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Daftar Barang -->
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-box text-primary me-2"></i> Daftar Barang yang Dipesan</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-2">Produk</th>
                                <th class="text-center py-2" style="width: 100px;">Jumlah</th>
                                <th class="text-end py-2" style="width: 130px;">Harga Satuan</th>
                                <th class="text-end pe-3 py-2" style="width: 130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr class="border-top">
                                    <td class="ps-3 py-2">
                                        <div class="d-flex align-items-center">
                                            @if($item->product && $item->product->gambar)
                                                <img src="{{ $item->product->gambar_url }}" 
                                                     width="34" height="34" class="rounded-2 me-2 object-fit-cover shadow-sm">
                                            @else
                                                <div class="bg-light rounded-2 me-2 border d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                    <i class="fas fa-box text-muted small"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $item->product->nama_barang ?? 'Produk Dihapus' }}</div>
                                                @if($item->variant)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">Varian: {{ $item->variant->nama_varian }}</span>
                                                @endif
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">SKU: {{ $item->product_variant_id ? ($item->variant->kode_barang ?? $item->product->kode_barang ?? $item->product->id ?? '-') : ($item->product->kode_barang ?? $item->product->id ?? '-') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">{{ $item->quantity }} pcs</span>
                                    </td>
                                    <td class="text-end text-muted py-2" style="font-size: 0.8rem;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-dark pe-3 py-2" style="font-size: 0.82rem;">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted small">Tidak ada item</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ringkasan Pesanan & Customer Info -->
        <div class="col-lg-5 col-12">
            <!-- Info Customer -->
            <div class="card shadow-sm mb-3 border-0 rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-user text-primary me-2"></i> Informasi Customer</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                        <tr>
                            <td class="fw-bold text-muted" style="width: 40%;">Nama</td>
                            <td class="fw-semibold text-dark">{{ $order->user->name ?? $order->nama_penerima }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Email</td>
                            <td><a href="mailto:{{ $order->user->email ?? $order->email }}" class="text-decoration-none">{{ $order->user->email ?? $order->email }}</a></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Role</td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">{{ ucfirst($order->user->role ?? 'Customer') }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Bergabung Sejak</td>
                            <td class="text-muted">{{ $order->user ? $order->user->created_at->format('d M Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ringkasan Pembayaran -->
            <div class="card shadow-sm mb-3 border-0 rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-receipt text-primary me-2"></i> Ringkasan Pembayaran</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                        <tr>
                            <td class="fw-bold text-muted">Subtotal Produk</td>
                            <td class="text-end fw-semibold text-dark">
                                Rp {{ number_format(
                                    $order->items->sum(fn($item) => $item->price * $item->quantity), 
                                    0, ',', '.'
                                ) }}
                            </td>
                        </tr>
                        @if($order->ongkir > 0)
                        <tr>
                            <td class="fw-bold text-muted">Ongkos Kirim</td>
                            <td class="text-end text-dark">Rp {{ number_format($order->ongkir, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($order->voucher_code)
                        @php
                            $voucherInfo = \App\Models\Voucher::where('code', $order->voucher_code)->first();
                            $voucherLabel = ($voucherInfo && $voucherInfo->type === 'shipping_subsidy') ? 'Subsidi Ongkir' : 'Voucher';
                        @endphp
                        <tr>
                            <td class="fw-bold text-success">
                                <i class="fas fa-ticket-alt me-1"></i> {{ $voucherLabel }} ({{ $order->voucher_code }})
                            </td>
                            <td class="text-end text-success fw-semibold">
                                -Rp {{ number_format($order->voucher_discount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        <tr class="border-top pt-2">
                            <td class="fw-bold text-dark" style="font-size: 0.95rem;">Total Pembayaran</td>
                            <td class="text-end fw-bold text-success" style="font-size: 1.05rem;">
                                Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-0">
                    <h6 class="mb-0 fw-bold small"><i class="fas fa-clock text-primary me-2"></i> Timeline Pesanan</h6>
                </div>
                <div class="card-body p-3 pt-0">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"><i class="fas fa-plus"></i></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-semibold text-dark" style="font-size: 0.82rem;">Pesanan Dibuat</p>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $order->created_at->format('d M Y H:i') }}</small>
                            </div>
                        </div>
                        @if($order->updated_at != $order->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"><i class="fas fa-rotate"></i></div>
                                <div class="timeline-content">
                                    <p class="mb-0 fw-semibold text-dark" style="font-size: 0.82rem;">Terakhir Diupdate</p>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $order->updated_at->format('d M Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
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
    margin-bottom: 1rem;
    position: relative;
    padding-left: 36px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 28px;
    width: 2px;
    height: calc(100% - 20px);
    background: #e2e8f0;
}
.timeline-marker {
    position: absolute;
    left: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
}
.timeline-content {
    flex: 1;
}
</style>

@endsection
