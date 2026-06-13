@extends('layouts.frontend')

@section('content')
@php
    $isB2B = auth()->check() && auth()->user()->role === 'branch';
@endphp
<div class="container py-5">

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cart.processCheckout') }}" method="POST">
        @csrf

        <div class="row g-4">

            {{-- 🔥 KIRI: FORM --}}
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4">Informasi Pengiriman</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" 
                                   name="nama_penerima" 
                                   class="form-control"
                                   value="{{ auth()->user()->name }}" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control"
                                   value="{{ auth()->user()->email }}" 
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. Telepon</label>
                        <input type="text" 
                               name="no_telp" 
                               class="form-control" 
                               value="{{ auth()->user()->phone }}"
                               placeholder="08xxxx" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Alamat Lengkap</label>
                        <textarea name="alamat_pengiriman" 
                                  class="form-control" 
                                  rows="3" 
                                  required>{{ auth()->user()->address }}</textarea>
                    </div>



                    @if(!$isB2B)
                        {{-- 🔥 METODE PEMBAYARAN --}}
                        <h5 class="fw-bold mb-3">Metode Pembayaran</h5>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="cash" value="cash" checked>
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="cash">
                                    <i class="fas fa-money-bill me-2"></i> 
                                    <strong>Tunai</strong>
                                    <small class="d-block text-muted">Pembayaran di tempat</small>
                                </label>
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="transfer" value="transfer">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="transfer">
                                    <i class="fas fa-university me-2"></i> 
                                    <strong>Transfer Bank</strong>
                                    <small class="d-block text-muted">BCA / Mandiri</small>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="ewallet" value="e-wallet">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="ewallet">
                                    <i class="fas fa-wallet me-2"></i> 
                                    <strong>E-Wallet</strong>
                                    <small class="d-block text-muted">GoPay, OVO, Dana</small>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Setelah klik "Buat Pesanan", silakan lakukan pembayaran sesuai metode yang dipilih.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                            Buat Pesanan Sekarang
                        </button>
                    @else
                        <input type="hidden" name="metode_pembayaran" value="branch_request">
                        
                        <div class="alert alert-success border-0 small mt-4 p-3 rounded-3 bg-opacity-10" style="background-color: #e6f7ef; color: #1b8a5a;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Info Pengajuan B2B:</strong> Sebagai akun cabang, pengajuan barang ini tidak memerlukan pembayaran. Setelah dikirim, Admin akan segera memverifikasi dan memproses pengiriman barang ke cabang Anda.
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan Barang
                        </button>
                    @endif
                </div>

            </div>


            {{-- 🔥 KANAN: RINGKASAN --}}
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>

                    @foreach($items as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-truncate" style="max-width: 70%;" title="{{ $item->product->nama_barang }}{{ $item->variant ? ' (' . $item->variant->nama_varian . ')' : '' }}">
                                {{ $item->product->nama_barang }}
                                @if($item->variant)
                                    <small class="text-muted d-block">Varian: {{ $item->variant->nama_varian }}</small>
                                @endif
                                (x{{ $item->quantity }})
                            </span>
                            <span class="small">
                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total Tagihan</span>
                        <h5 class="text-primary fw-bold mb-0">
                            Rp {{ number_format($cart->total_price, 0, ',', '.') }}
                        </h5>
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>
@endsection