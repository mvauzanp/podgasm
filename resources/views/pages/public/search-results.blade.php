@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hasil Pencarian</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar Kategori --}}
        <div class="col-lg-3 d-none d-lg-block">
            @include('components._category-sidebar')
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9 col-12">
            {{-- Search Bar --}}
            <div class="mb-4">
                <form action="{{ route('public.search') }}" method="GET" class="input-group">
                    <input type="text" 
                        class="form-control form-control-lg rounded-start-3" 
                        name="q" 
                        placeholder="Cari produk..."
                        value="{{ $query }}"
                        required>
                    <button class="btn btn-primary btn-lg rounded-end-3" type="submit">
                        <i class="fas fa-search me-2"></i> Cari
                    </button>
                </form>
            </div>

            {{-- Results Header --}}
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    @if($query)
                        <h3 class="fw-bold m-0">Hasil pencarian untuk "<span class="text-primary">{{ $query }}</span>"</h3>
                    @else
                        <h3 class="fw-bold m-0">Cari Produk</h3>
                    @endif
                </div>
                @if(isset($products) && $products->count() > 0)
                    <span class="badge bg-secondary py-2 px-3">{{ $products->total() }} produk ditemukan</span>
                @endif
            </div>

            {{-- Error Message --}}
            @if(isset($message))
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i> {{ $message }}
                </div>
            @endif

            {{-- Products Grid --}}
            @if(isset($products) && $products->count() > 0)
                <div class="row g-3 mb-4">
                    @foreach($products as $product)
                        <div class="col-6 col-md-4 col-lg-3" style="cursor: pointer;" onclick="window.location.href='{{ route('public.product.show', $product->slug) }}'">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden" style="transition: all 0.3s ease;">
                                <div class="bg-light position-relative" style="height: 180px; overflow: hidden;">
                                    @if($product->gambar)
                                        <img src="{{ asset('storage/' . $product->gambar) }}" 
                                            class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                        </div>
                                    @endif
                                    
                                    {{-- Promo Badge --}}
                                    @if($product->is_promo)
                                        <span class="badge bg-danger position-absolute top-2 end-2">
                                            <i class="fas fa-tag me-1"></i> PROMO
                                        </span>
                                    @endif
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title fw-bold text-dark">{{ $product->nama_barang }}</h6>
                                    @php
                                        $isB2B = auth()->check() && auth()->user()->role === 'branch';
                                        $b2bPrice = $isB2B ? $product->getB2bPrice(1) : null;
                                    @endphp
                                    @if($isB2B && $b2bPrice < $product->harga_jual)
                                        <p class="text-primary fw-bold mb-2">
                                            <span class="text-muted text-decoration-line-through small me-1">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span>
                                            Rp {{ number_format($b2bPrice, 0, ',', '.') }} <span class="badge bg-info text-white" style="font-size: 0.65rem;">B2B Price</span>
                                        </p>
                                    @else
                                        <p class="text-primary fw-bold mb-2">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</p>
                                    @endif
                                    
                                    <div class="mt-auto">
                                        {{-- Stock Status --}}
                                        @if($product->stok_aktual <= $product->nilai_ss)
                                            <span class="badge bg-danger w-100 py-2">Stok Habis / Kritis</span>
                                        @else
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Tersedia: {{ $product->stok_aktual }}</small>
                                                <div class="d-flex gap-1">
                                                    {{-- Wishlist Button --}}
                                                    <button type="button" 
                                                       class="btn btn-light btn-sm rounded-circle shadow-sm text-danger wishlist-btn"
                                                       data-product-id="{{ $product->id }}"
                                                       onclick="event.stopPropagation();"
                                                       style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                     {{-- Add to Cart Button --}}
                                                     @if($product->hasVariants())
                                                         <a href="{{ route('public.product.show', $product->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="event.stopPropagation();">
                                                             Pilih Varian
                                                         </a>
                                                     @else
                                                         <button class="btn btn-sm btn-primary rounded-pill px-3 add-to-cart-btn" data-product-id="{{ $product->id }}" onclick="event.stopPropagation();">
                                                             <i class="fas fa-shopping-cart me-1"></i> Beli
                                                         </button>
                                                     @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($products->hasPages())
                    <nav aria-label="Page navigation" class="mt-4">
                        {{ $products->appends(request()->query())->links() }}
                    </nav>
                @endif
            @else
                {{-- No Results --}}
                <div class="text-center py-5">
                    <div class="py-5">
                        <i class="fas fa-search fa-5x text-muted mb-3"></i>
                        <h5 class="text-muted">Produk tidak ditemukan</h5>
                        <p class="text-muted mb-3">
                            @if($query)
                                Kami tidak menemukan produk yang cocok dengan "<strong>{{ $query }}</strong>"
                            @else
                                Silakan gunakan kolom pencarian di atas untuk mencari produk
                            @endif
                        </p>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const originalHTML = this.innerHTML;
            
            // Add loading state
            this.disabled = true;
            this.innerHTML = '<span class="spinner-animation"></span>Menambahkan...';
            
            fetch("{{ url('/cart/add') }}/" + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast('✓ Produk berhasil ditambahkan ke keranjang!', 'success', 4000);
                    
                    // Update cart count if available
                    if (data.cartCount) {
                        updateCartCount(data.cartCount);
                    }
                    
                    // Restore button
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 1000);
                } else {
                    showToast(data.message || 'Gagal menambahkan ke keranjang', 'error');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
