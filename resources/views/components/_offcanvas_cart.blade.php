<div class="d-flex flex-column h-100">
    @if($items->isEmpty())
        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 p-4 text-center">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3 opacity-25"></i>
            <h5 class="fw-bold text-dark mb-1">Keranjang Kosong</h5>
            <p class="text-muted small mb-4">Yuk, temukan produk favoritmu sekarang!</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-dismiss="offcanvas">Belanja Sekarang</a>
        </div>
    @else
        <div class="flex-grow-1 overflow-auto p-3">
            @foreach($items as $item)
                <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: #f8f9fa;">
                    <div class="card-body p-3 d-flex gap-3">
                        <img src="{{ asset('storage/' . $item->product->gambar) }}" class="rounded-3 object-fit-cover" style="width: 70px; height: 70px;">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1 text-truncate" style="max-width: 200px;" title="{{ $item->product->nama_barang }}{{ $item->variant ? ' (' . $item->variant->nama_varian . ')' : '' }}">
                                {{ $item->product->nama_barang }}
                                @if($item->variant)
                                    <span class="d-block small text-muted fw-normal mt-1">Varian: {{ $item->variant->nama_varian }}</span>
                                @endif
                            </h6>
                            <p class="text-primary fw-bold mb-2 small">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <form action="{{ route('cart.update') }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product_variant_id ? $item->variant->stok_aktual : $item->product->stok_aktual }}" class="form-control form-control-sm text-center rounded-pill border-0 shadow-sm" style="width: 60px; height: 30px;" onchange="this.form.submit()">
                                </form>
                                
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-link text-danger p-0" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="border-top p-4 bg-white mt-auto shadow-sm" style="border-radius: 20px 20px 0 0;">
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted fw-bold">Total Belanja</span>
                <span class="text-primary fw-bold fs-5">Rp {{ number_format($cart->total_price ?? 0, 0, ',', '.') }}</span>
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('cart.index') }}" class="btn btn-light w-100 rounded-pill fw-bold text-primary">Lihat Penuh</a>
                </div>
                <div class="col-6">
                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">Checkout</a>
                </div>
            </div>
        </div>
    @endif
</div>
