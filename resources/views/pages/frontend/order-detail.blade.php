@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 print-hide">
                <h4 class="fw-bold mb-0">Detail Pesanan</h4>
                <div class="d-flex gap-2">
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin Podgasm, saya ingin menanyakan pesanan saya dengan No. Invoice #' . $order->invoice_number . ' (Total: Rp ' . number_format($order->total_harga, 0, ',', '.') . ')') }}" 
                       target="_blank" 
                       class="btn btn-success rounded-pill px-3 py-2 small fw-semibold shadow-sm" 
                       title="Hubungi Admin CS">
                        <i class="fab fa-whatsapp me-1 fs-6"></i> Tanya Admin
                    </a>
                    <button type="button" class="btn btn-outline-dark rounded-pill px-3 py-2 small fw-semibold shadow-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Cetak Invoice
                    </button>
                    <a href="{{ route('order.history') }}" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 invoice-print-container">
                <div class="card-header bg-primary text-white p-4 border-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="opacity-75 d-block">Nomor Invoice</small>
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="fw-bold mb-0 font-monospace">{{ $order->invoice_number }}</h4>
                                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2 py-0.5 small border-0 print-hide" onclick="copyInvoiceNo('{{ $order->invoice_number }}')" title="Salin Nomor Invoice">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
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
                            <p class="mb-0"><strong>{{ str_contains($order->metode_pembayaran, 'midtrans') ? 'Pembayaran Otomatis (VA, QRIS, E-Wallet, CC)' : strtoupper($order->metode_pembayaran) }}</strong></p>
                            <small class="text-muted">Dibahas / Dibuat: {{ $order->created_at->format('d M Y H:i') }}</small>
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
                                            <img src="{{ $item->product->gambar_url }}" width="40" class="rounded me-2">
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
                                @if($order->voucher_code)
                                <tr>
                                    <td colspan="3" class="text-end text-muted small py-2">Subtotal Belanja</td>
                                    <td class="text-end text-muted small py-2">
                                        Rp {{ number_format($order->total_harga + $order->voucher_discount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @php
                                    $voucherInfo = \App\Models\Voucher::where('code', $order->voucher_code)->first();
                                    $voucherLabel = ($voucherInfo && $voucherInfo->type === 'shipping_subsidy') ? 'Subsidi Ongkir' : 'Voucher';
                                @endphp
                                <tr>
                                    <td colspan="3" class="text-end text-success small py-2">
                                        {{ $voucherLabel }} ({{ $order->voucher_code }})
                                    </td>
                                    <td class="text-end text-success small py-2">
                                        -Rp {{ number_format($order->voucher_discount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endif
                                <tr class="border-top">
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
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                        <div class="card-body p-4 p-md-5">
                            <div class="row align-items-center">
                                <div class="col-lg-7 mb-4 mb-lg-0">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); font-size: 0.8rem;">
                                        <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                                        <span class="text-warning fw-semibold">Menunggu Pembayaran</span>
                                    </div>
                                    <h4 class="fw-bold mb-2 text-white">Selesaikan Pembayaran Pesanan</h4>
                                    <p class="text-white-50 small mb-4">
                                        Pilih metode pembayaran aman (BCA, Mandiri, BRI, BNI Virtual Account, QRIS, GoPay, ShopeePay, atau Kartu Kredit).
                                    </p>
                                    
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                                            <i class="fas fa-university me-1 text-info"></i> Virtual Account
                                        </span>
                                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                                            <i class="fas fa-qrcode me-1 text-danger"></i> QRIS Instant
                                        </span>
                                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                                            <i class="fas fa-wallet me-1 text-warning"></i> E-Wallet
                                        </span>
                                        <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                                            <i class="fas fa-credit-card me-1 text-primary"></i> Kartu Kredit
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-5 text-lg-end">
                                    <div class="p-4 rounded-4" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                        <small class="text-white-50 d-block mb-1">Total Tagihan Pembayaran</small>
                                        <h3 class="fw-bold text-success mb-3">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</h3>
                                        
                                        @if(isset($snapToken) && $snapToken)
                                            <button type="button" id="pay-button" class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold shadow-lg mb-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                                <i class="fas fa-lock me-2"></i> Lanjutkan Pembayaran
                                            </button>
                                            <button type="button" id="change-payment-button" class="btn btn-outline-light btn-sm w-100 rounded-pill py-2 opacity-90">
                                                <i class="fas fa-sync-alt me-1"></i> Ganti Bank / Cara Bayar Lain
                                            </button>
                                        @else
                                            <div class="alert alert-warning mb-0 small text-start text-dark">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Token pembayaran tidak tersedia. Muat ulang halaman.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@if(isset($snapToken) && $snapToken)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', function () {
                window.snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        alert("Pembayaran Berhasil!");
                        location.reload();
                    },
                    onPending: function(result) {
                        alert("Menunggu Pembayaran...");
                        location.reload();
                    },
                    onError: function(result) {
                        alert("Pembayaran Gagal!");
                        location.reload();
                    },
                    onClose: function() {
                        console.log('User closed the snap popup');
                    }
                });
            });
        }

        var changeButton = document.getElementById('change-payment-button');
        if (changeButton) {
            changeButton.addEventListener('click', function () {
                if (confirm("Apakah Anda ingin mereset pilihan pembayaran untuk memilih bank / metode lain?")) {
                    changeButton.disabled = true;
                    changeButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mereset pilihan...';
                    
                    fetch("{{ route('order.resetPayment', $order->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.snap_token) {
                            window.snap.pay(data.snap_token, {
                                onSuccess: function(result) {
                                    alert("Pembayaran Berhasil!");
                                    location.reload();
                                },
                                onPending: function(result) {
                                    alert("Menunggu Pembayaran...");
                                    location.reload();
                                },
                                onError: function(result) {
                                    alert("Pembayaran Gagal!");
                                    location.reload();
                                }
                            });
                        } else {
                            alert(data.message || "Gagal mereset pembayaran.");
                        }
                    })
                    .catch(err => {
                        console.error('Reset payment error:', err);
                        alert("Terjadi kesalahan sistem saat mereset pembayaran.");
                    })
                    .finally(() => {
                        changeButton.disabled = false;
                        changeButton.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Ganti Bank / Cara Bayar Lain';
                    });
                }
            });
        }
    });
</script>
@endif

<script>
function copyInvoiceNo(inv) {
    navigator.clipboard.writeText(inv).then(() => {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'No. Invoice (' + inv + ') tersalin!'
            });
        } else {
            alert('No. Invoice tersalin!');
        }
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

<style>
@media print {
    /* Sembunyikan elemen navigasi, tombol, footer, dan chat saat mencetak */
    nav, footer, .navbar, .btn, .print-hide, #csChatFloatingBtn, #csChatModal, #themeToggleBtn {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .card {
        border: 1px solid #000000 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .card-header {
        background-color: #f8fafc !important;
        color: #000000 !important;
        border-bottom: 2px solid #000000 !important;
    }
    .card-header h4, .card-header span {
        color: #000000 !important;
    }
    .table {
        color: #000000 !important;
    }
}
</style>
@endsection