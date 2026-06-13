@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Pesanan</h4>
                <a href="{{ route('order.history') }}" class="btn btn-light rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-primary text-white p-4 border-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="opacity-75 d-block">Nomor Invoice</small>
                            <h4 class="fw-bold mb-0">{{ $order->invoice_number }}</h4>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-white text-primary px-3 py-2 rounded-pill">
                                @if($order->metode_pembayaran === 'branch_request' && $order->status === 'pending_payment')
                                    MENUNGGU PERSETUJUAN
                                @elseif($order->metode_pembayaran === 'branch_request' && $order->status === 'paid')
                                    DISETUJUI / DIPROSES
                                @else
                                    {{ strtoupper($order->status) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted small text-uppercase">Informasi Penerima</h6>
                            <p class="mb-1"><strong>{{ $order->nama_penerima }}</strong></p>
                            <p class="mb-1 text-muted small">{{ $order->no_telp }} | {{ $order->email }}</p>
                            <p class="mb-0 text-muted small">{{ $order->alamat_pengiriman }}</p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <h6 class="fw-bold text-muted small text-uppercase">Metode Pembayaran</h6>
                            <p class="mb-0"><strong>{{ $order->metode_pembayaran }}</strong></p>
                            <small class="text-muted">Dibayar pada: {{ $order->created_at->format('d M Y H:i') }}</small>
                        </div>
                    </div>

                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Item Pesanan</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/' . $item->product->gambar) }}" width="40" class="rounded me-2">
                                            <div>
                                                <span class="small fw-bold d-block">{{ $item->product->nama_barang }}</span>
                                                @if($item->variant)
                                                    <span class="small text-muted d-block" style="font-size: 0.75rem;">Varian: {{ $item->variant->nama_varian }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end text-muted small">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold py-3">Total Pembayaran</td>
                                    <td class="text-end fw-bold text-primary py-3 h5">
                                        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($order->metode_pembayaran === 'branch_request')
                @if($order->status === 'pending_payment')
                    <div class="alert alert-success border-0 rounded-4 p-3 shadow-sm d-flex align-items-center" style="background-color: #e6f7ef; color: #1b8a5a;">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Permintaan Sedang Ditinjau</h6>
                            <p class="small mb-0">Permintaan barang Anda telah dikirim dan sedang menunggu verifikasi/persetujuan dari Admin Gudang Pusat.</p>
                        </div>
                    </div>
                @endif
            @else
                @if($order->status == 'pending' || $order->status == 'pending_payment')
                    <div class="alert alert-info border-0 rounded-4 p-3 shadow-sm d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Instruksi Pembayaran</h6>
                            <p class="small mb-0">Silakan lakukan pembayaran sesuai metode <strong>{{ $order->metode_pembayaran }}</strong>. Kirim bukti bayar ke WhatsApp Admin jika status tidak berubah.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection