<div class="d-flex flex-column h-100 bg-white">
    @if($items->isEmpty())
        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 p-4 text-center">
            <div class="icon-circle bg-slate-50 text-muted rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 76px; height: 76px;">
                <i class="fas fa-shopping-bag fa-2x opacity-30"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Keranjang Masih Kosong</h6>
            <p class="text-muted small mb-4">Temukan perangkat vape & liquid favorit Anda sekarang.</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 small fw-semibold shadow-sm" data-bs-dismiss="offcanvas">
                Mulai Belanja
            </a>
        </div>
    @else
        <div class="flex-grow-1 overflow-y-auto p-3.5">
            @foreach($items as $item)
                <div class="card border-0 rounded-4 mb-3 p-3 bg-white" style="border: 1px solid #f1f5f9 !important;">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="product-img-wrapper rounded-3 overflow-hidden bg-slate-50 flex-shrink-0" style="width: 64px; height: 64px; aspect-ratio: 1/1;">
                            <img src="{{ asset('storage/' . $item->product->gambar) }}" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold text-dark mb-0 small text-truncate" title="{{ $item->product->nama_barang }}">
                                {{ $item->product->nama_barang }}
                            </h6>
                            @if($item->variant)
                                <span class="d-block text-muted small" style="font-size: 0.72rem;">Varian: {{ $item->variant->nama_varian }}</span>
                            @endif
                            <div class="fw-bold text-primary small mt-1">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <form action="{{ route('cart.update') }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product_variant_id ? $item->variant->stok_aktual : $item->product->stok_aktual }}" class="form-control form-control-sm text-center rounded-pill border-light bg-slate-50 fw-bold small" style="width: 64px;" onchange="this.form.submit()">
                                </form>
                                
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-link text-muted hover-danger p-0" title="Hapus">
                                        <i class="far fa-trash-alt small"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="border-top border-light p-4 bg-white mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-medium">Subtotal Belanja</span>
                <span class="text-dark fw-bold fs-5">Rp {{ number_format($cart->total_price ?? 0, 0, ',', '.') }}</span>
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2.5 small fw-semibold">Lihat Keranjang</a>
                </div>
                <div class="col-6">
                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 rounded-pill py-2.5 small fw-semibold shadow-sm">Checkout</a>
                </div>
            </div>
        </div>
    @endif
</div>
