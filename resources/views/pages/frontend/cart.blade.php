@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <h3 class="fw-bold mb-4">Keranjang Belanja</h3>

    @if($items && count($items) > 0)
    {{-- PRICE CHANGE WARNING --}}
    @if(!empty($priceChanges))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Perhatian!</strong> Ada perubahan harga pada produk di keranjang:
        <ul class="mb-0 mt-2">
            @foreach($priceChanges as $change)
            <li>{{ $change['product'] }}: Rp {{ number_format($change['old_price'], 0, ',', '.') }} → Rp {{ number_format($change['new_price'], 0, ',', '.') }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- 🔥 LEFT: CART ITEMS --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="ps-4">Produk</th>
                                <th>Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th>Subtotal</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                {{-- Produk --}}
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('storage/' . $item->product->gambar) }}"
                                            width="60" height="60"
                                            class="rounded-3 object-fit-cover me-3 shadow-sm">
                                        <div>
                                            <span class="fw-bold d-block">{{ $item->product->nama_barang }}</span>
                                            @if($item->variant)
                                            <span class="d-block small text-muted">Varian: {{ $item->variant->nama_varian }}</span>
                                            @endif
                                            @if($item->isPriceChanged())
                                            <span class="small text-warning">
                                                <i class="fas fa-exclamation-circle"></i> Harga berubah
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Harga --}}
                                <td class="text-muted small">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>

                                {{-- Quantity --}}
                                <td>
                                    <form action="{{ route('cart.update') }}" method="POST"
                                          class="d-flex justify-content-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                        <input type="number"
                                               name="quantity"
                                               value="{{ $item->quantity }}"
                                               min="1"
                                               max="{{ $item->product_variant_id ? $item->variant->stok_aktual : $item->product->stok_aktual }}"
                                               class="form-control form-control-sm text-center fw-bold"
                                               style="width: 70px; border-radius: 8px;"
                                               oninput="let max = parseInt(this.max); if(this.value && parseInt(this.value) > max) this.value = max; if(this.value && parseInt(this.value) < 1) this.value = 1;"
                                               onchange="this.form.submit()">
                                    </form>
                                </td>

                                {{-- Subtotal --}}
                                <td class="fw-bold text-primary">
                                    Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                </td>

                                {{-- Delete --}}
                                <td class="text-center pe-4">
                                    <form action="{{ route('cart.remove') }}" method="POST"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                        <button class="btn btn-link text-danger p-0">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 🔥 RIGHT: SUMMARY --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Harga</span>
                    <span>Rp {{ number_format($cart->total_price, 0, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Ongkir</span>
                    <span class="text-success">Gratis</span>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">Total</span>
                    <h5 class="text-primary fw-bold mb-0">
                        Rp {{ number_format($cart->total_price, 0, ',', '.') }}
                    </h5>
                </div>

                @php
                    $isB2B = auth()->check() && auth()->user()->role === 'branch';
                @endphp
                @if($isB2B)
                    <a href="{{ route('cart.checkout') }}" class="btn btn-success w-100 py-3 rounded-pill fw-bold mb-3">
                        Lanjut ke Request Barang <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                @else
                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-3">
                        Lanjut ke Pembayaran <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                @endif

                <div class="text-center mt-3">
                    <a href="{{ route('home') }}"
                       class="small text-muted text-decoration-none">
                        ← Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

    </div>

    @else
    {{-- 🔥 EMPTY STATE --}}
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="fw-bold">Keranjang kosong</h5>
        <p class="text-muted">Yuk mulai belanja produk favorit kamu</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2 rounded-pill">
            Mulai Belanja
        </a>
    </div>
    @endif
</div>
@endsection