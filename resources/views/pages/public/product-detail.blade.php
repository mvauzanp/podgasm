@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('public.category', $product->category->slug) }}" class="text-decoration-none">{{ $product->category->nama_kategori }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->nama_barang }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Product Image --}}
        <div class="col-lg-5 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                <div class="bg-light position-relative" style="height: 450px; overflow: hidden;">
                    @if($product->gambar)
                        <img src="{{ asset('storage/' . $product->gambar) }}" 
                            class="w-100 h-100 object-fit-cover"
                            alt="{{ $product->nama_barang }}"
                            id="productImage"
                            style="transition: all 0.3s ease-in-out;">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-box fa-5x text-muted opacity-25"></i>
                                <p class="text-muted mt-3">Gambar tidak tersedia</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($product->images->isNotEmpty())
                <div class="d-flex gap-2 overflow-x-auto pb-2 mb-3" style="scrollbar-width: thin; scrollbar-color: #0d6efd transparent;">
                    @foreach($product->images as $index => $img)
                        <div class="thumbnail-wrapper border rounded-3 overflow-hidden cursor-pointer {{ $index === 0 ? 'border-primary border-2' : 'border-light' }}"
                             style="width: 72px; height: 72px; flex-shrink: 0; cursor: pointer; transition: all 0.2s;"
                             onclick="switchMainImage('{{ asset('storage/' . $img->path) }}', this)">
                            <img src="{{ asset('storage/' . $img->path) }}" class="w-100 h-100 object-fit-cover">
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Status Badge --}}
            <div class="mt-3">
                @if($product->stok_aktual <= $product->nilai_ss)
                    <span class="badge bg-danger py-2 px-3">
                        <i class="fas fa-exclamation-circle me-1"></i> Stok Habis / Kritis
                    </span>
                @else
                    <span class="badge bg-success py-2 px-3">
                        <i class="fas fa-check-circle me-1"></i> Tersedia ({{ $product->stok_aktual }} unit)
                    </span>
                @endif
            </div>
        </div>

        {{-- Product Details --}}
        <div class="col-lg-7 col-md-6">
            {{-- Kategori --}}
            <div class="mb-2">
                <a href="{{ route('public.category', $product->category->slug) }}" class="text-muted text-decoration-none small">
                    <i class="fas fa-tag me-1"></i> {{ $product->category->nama_kategori }}
                </a>
            </div>

            {{-- Nama Produk --}}
            <h1 class="fw-bold mb-3" style="font-size: 2rem;">{{ $product->nama_barang }}</h1>

            {{-- Rating --}}
            <div class="mb-3 d-flex align-items-center gap-2">
                @php
                    $avgRating = $product->reviews->avg('rating') ?? 0;
                    $reviewCount = $product->reviews->count();
                @endphp
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $avgRating)
                            <i class="fas fa-star"></i>
                        @elseif($i - 0.5 <= $avgRating)
                            <i class="fas fa-star-half-alt"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-muted small">({{ number_format($avgRating, 1) }} / 5) dari {{ $reviewCount }} ulasan</span>
            </div>

            @php
                $isB2B = auth()->check() && auth()->user()->role === 'branch';
                $tiers = $isB2B ? $product->b2bPriceSettings()->orderBy('min_qty')->get() : collect();
                $b2bPrice = $isB2B ? $product->getB2bPrice(1) : null;
            @endphp

            {{-- Harga --}}
            <div class="mb-4 p-3 bg-light rounded-3">
                @if($isB2B && $b2bPrice < $product->harga_jual)
                    <h2 class="text-primary fw-bold mb-1">
                        Rp {{ number_format($b2bPrice, 0, ',', '.') }}
                    </h2>
                    <small class="text-muted d-block mt-1">
                        <s>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</s>
                        <span class="badge bg-info ms-2">Harga B2B</span>
                    </small>
                @else
                    <h2 class="text-primary fw-bold mb-1">
                        Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                    </h2>
                    @if($product->harga_pokok < $product->harga_jual)
                        <small class="text-muted d-block mt-1">
                            <s>Rp {{ number_format($product->harga_pokok, 0, ',', '.') }}</s>
                            <span class="badge bg-danger ms-2">
                                -{{ round((1 - $product->harga_pokok / $product->harga_jual) * 100) }}%
                            </span>
                        </small>
                    @endif
                @endif
            </div>

            @if($isB2B && $tiers->isNotEmpty())
                <div class="mb-4 p-3 border border-info rounded-3 bg-light-info" style="background-color: #eaf6fc;">
                    <h6 class="fw-bold text-info-emphasis mb-2" style="color: #1b7396;"><i class="fas fa-tags me-1"></i> Skema Harga Grosir (B2B)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 bg-white small">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Min. Qty</th>
                                    <th>Potongan</th>
                                    <th>Harga / Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tiers as $tier)
                                    @php
                                        $baseVal = $tier->product_variant_id && $product->variants->find($tier->product_variant_id)
                                            ? $product->variants->find($tier->product_variant_id)->harga_jual_actual
                                            : $product->harga_jual;
                                        
                                        if ($tier->discount_type === 'percentage') {
                                            $discText = $tier->discount_value . '%';
                                            $finalVal = $baseVal - ($baseVal * ($tier->discount_value / 100));
                                        } else {
                                            $discText = 'Rp ' . number_format($tier->discount_value, 0, ',', '.');
                                            $finalVal = $baseVal - $tier->discount_value;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-bold">{{ $tier->min_qty }}+</td>
                                        <td class="text-center text-success fw-bold">{{ $discText }}</td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp {{ number_format($finalVal, 0, ',', '.') }}
                                            @if($tier->product_variant_id)
                                                <span class="d-block text-muted" style="font-size: 0.65rem;">Varian: {{ $tier->variant->nama_varian ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Deskripsi Singkat --}}
            @if($product->description)
                <div class="mb-4">
                    <h5 class="fw-bold mb-2">Deskripsi Produk</h5>
                    <p class="text-muted">{!! nl2br(e($product->description)) !!}</p>
                </div>
            @endif

            {{-- Info Produk --}}
            <div class="mb-4 border-top border-bottom py-3">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">SKU</small>
                        <span class="fw-bold" id="productSku">{{ $product->kode_barang ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Stok Aman</small>
                        <span class="fw-bold">{{ $product->nilai_ss }} unit</span>
                    </div>
                </div>
            </div>

            {{-- PILIHAN VARIAN (Kadar Nikotin / Rasa / dsb) --}}
            @if($product->variants->isNotEmpty())
                <div class="mb-4">
                    <label class="form-label fw-bold mb-2">Pilih Varian <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2" id="variantSelector">
                        @foreach($product->variants as $variant)
                            @php
                                $varPrice = $isB2B ? $variant->getB2bPrice(1) : $variant->harga_jual_actual;
                            @endphp
                            <button type="button" 
                                class="btn btn-outline-primary rounded-pill px-4 py-2 variant-btn" 
                                data-variant-id="{{ $variant->id }}" 
                                data-variant-stock="{{ $variant->stok_aktual }}"
                                data-variant-price="Rp {{ number_format($varPrice, 0, ',', '.') }}"
                                data-variant-sku="{{ $variant->kode_barang ?? $product->kode_barang }}"
                                data-variant-image="{{ $variant->gambar ? asset('storage/' . $variant->gambar) : '' }}"
                                {{ $variant->stok_aktual <= 0 ? 'disabled' : '' }}>
                                {{ $variant->nama_varian }}
                                @if($variant->stok_aktual <= 0)
                                    <span class="small text-danger-emphasis ms-1">(Habis)</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedVariantId" value="">
                </div>
            @endif

            {{-- Quantity Selector & Add to Cart --}}
            @if($product->stok_aktual > $product->nilai_ss)
                <div class="mb-4">
                    <label class="form-label fw-bold mb-2">Jumlah</label>
                    <div class="input-group mb-3" style="width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="{{ $product->stok_aktual }}">
                        <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                    </div>
                    <small class="text-muted" id="stockLimitText">Max: {{ $product->stok_aktual }} unit</small>
                </div>

                {{-- Action Buttons --}}
                <div class="d-grid gap-2 gap-md-3">
                    <button class="btn btn-primary btn-lg rounded-pill" id="addToCartBtn">
                        <i class="fas fa-shopping-cart me-2"></i> Tambah ke Keranjang
                    </button>
                    <button class="btn btn-outline-danger btn-lg rounded-pill" id="addToWishlistBtn">
                        <i class="far fa-heart me-2"></i> Tambah ke Wishlist
                    </button>
                </div>
            @else
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Produk saat ini tidak tersedia
                </div>
            @endif

            {{-- Share Options --}}
            <div class="mt-4 pt-3 border-top">
                <small class="text-muted d-block mb-2">Bagikan ke:</small>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" title="Share ke Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" title="Share ke Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" title="Share ke WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" title="Copy Link">
                        <i class="fas fa-link"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews Section --}}
    <hr class="my-5">
    <div class="mb-4">
        <h3 class="fw-bold mb-4">Ulasan Produk ({{ $reviewCount }})</h3>
        
        <div class="row">
            <div class="col-md-8">
                @forelse($product->reviews as $review)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0">{{ $review->user->name }}</h6>
                                <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
                            </div>
                            <div class="text-warning mb-2" style="font-size: 0.9rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="mb-0 text-muted">{{ $review->comment }}</p>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light text-center border">
                        Belum ada ulasan untuk produk ini.
                    </div>
                @endforelse
            </div>
            
            <div class="col-md-4">
                @auth
                    @php
                        // Cek apakah user sudah membeli dan pesanan selesai
                        $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                            ->where('status', 'completed')
                            ->whereHas('items', function($q) use ($product) {
                                $q->where('product_id', $product->id);
                            })->first();
                            
                        // Cek apakah sudah me-review
                        $hasReviewed = false;
                        if ($hasPurchased) {
                            $hasReviewed = \App\Models\Review::where('user_id', auth()->id())
                                ->where('product_id', $product->id)
                                ->where('order_id', $hasPurchased->id)
                                ->exists();
                        }
                    @endphp

                    @if($hasPurchased && !$hasReviewed)
                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">Tulis Ulasan Anda</h5>
                                <form action="{{ route('review.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="order_id" value="{{ $hasPurchased->id }}">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Rating</label>
                                        <select name="rating" class="form-select" required>
                                            <option value="5">5 Bintang - Sangat Bagus</option>
                                            <option value="4">4 Bintang - Bagus</option>
                                            <option value="3">3 Bintang - Cukup</option>
                                            <option value="2">2 Bintang - Kurang</option>
                                            <option value="1">1 Bintang - Sangat Kurang</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Komentar</label>
                                        <textarea name="comment" class="form-control" rows="3" required placeholder="Bagaimana pengalaman Anda dengan produk ini?"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Kirim Ulasan</button>
                                </form>
                            </div>
                        </div>
                    @elseif($hasPurchased && $hasReviewed)
                        <div class="alert alert-success text-center">
                            Anda sudah memberikan ulasan untuk produk ini. Terima kasih!
                        </div>
                    @endif
                @else
                    <div class="alert alert-info text-center">
                        Silakan <a href="{{ route('login') }}" class="alert-link">login</a> untuk menulis ulasan (hanya untuk pembeli).
                    </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Related Products Section --}}
    @if($relatedProducts->count() > 0)
        <hr class="my-5">
        <div class="mb-4">
            <h3 class="fw-bold mb-4">Produk Terkait</h3>
            <div class="row g-3">
                @foreach($relatedProducts as $related)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                            <div class="bg-light" style="height: 180px; overflow: hidden;">
                                @if($related->gambar)
                                    <img src="{{ asset('storage/' . $related->gambar) }}" 
                                        class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-dark">{{ $related->nama_barang }}</h6>
                                <p class="text-primary fw-bold mb-2">Rp {{ number_format($related->harga_jual, 0, ',', '.') }}</p>
                                
                                <div class="mt-auto">
                                    @if($related->stok_aktual <= $related->nilai_ss)
                                        <span class="badge bg-danger w-100 py-2">Stok Habis</span>
                                    @else
                                        <a href="{{ route('public.product.show', $related->slug) }}" class="btn btn-sm btn-primary rounded-pill w-100">
                                            <i class="fas fa-eye me-1"></i> Lihat
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function switchMainImage(src, element) {
    const mainImage = document.getElementById('productImage');
    if (mainImage) {
        mainImage.src = src;
    }
    document.querySelectorAll('.thumbnail-wrapper').forEach(wrapper => {
        wrapper.classList.remove('border-primary', 'border-2');
        wrapper.classList.add('border-light');
    });
    if (element) {
        element.classList.remove('border-light');
        element.classList.add('border-primary', 'border-2');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantityInput = document.getElementById('quantity');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');
    const addToCartBtn = document.getElementById('addToCartBtn');
    let maxQty = quantityInput ? parseInt(quantityInput.getAttribute('max')) : 0;

    // Variant elements
    const variantButtons = document.querySelectorAll('.variant-btn');
    const selectedVariantInput = document.getElementById('selectedVariantId');
    const stockLimitText = document.getElementById('stockLimitText');
    const priceDisplay = document.querySelector('.text-primary.fw-bold.mb-1');
    const skuDisplay = document.getElementById('productSku');

    // Handle variant button click
    if (variantButtons.length > 0) {
        variantButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all variant buttons
                variantButtons.forEach(b => {
                    b.classList.remove('btn-primary', 'text-white');
                    b.classList.add('btn-outline-primary');
                });

                // Add active class to clicked button
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'text-white');

                // Update selected variant ID
                const variantId = this.getAttribute('data-variant-id');
                selectedVariantInput.value = variantId;

                // Update stock and limit text
                const variantStock = parseInt(this.getAttribute('data-variant-stock'));
                maxQty = variantStock;
                if (quantityInput) {
                    quantityInput.setAttribute('max', variantStock);
                    if (parseInt(quantityInput.value) > variantStock) {
                        quantityInput.value = variantStock > 0 ? 1 : 0;
                    }
                }
                if (stockLimitText) {
                    stockLimitText.textContent = `Max: ${variantStock} unit`;
                }

                // Update price display
                const variantPrice = this.getAttribute('data-variant-price');
                if (priceDisplay && variantPrice) {
                    priceDisplay.textContent = variantPrice;
                }

                // Update SKU display
                const variantSku = this.getAttribute('data-variant-sku');
                if (skuDisplay && variantSku) {
                    skuDisplay.textContent = variantSku;
                }

                // Update Image if variant has specific image, otherwise use default product image
                const variantImage = this.getAttribute('data-variant-image');
                const mainImage = document.getElementById('productImage');
                const originalProductImage = "{{ $product->gambar ? asset('storage/' . $product->gambar) : '' }}";
                
                if (mainImage) {
                    if (variantImage) {
                        mainImage.src = variantImage;
                    } else {
                        mainImage.src = originalProductImage;
                    }
                }

                // Unhighlight gallery thumbnails
                document.querySelectorAll('.thumbnail-wrapper').forEach(wrapper => {
                    wrapper.classList.remove('border-primary', 'border-2');
                    wrapper.classList.add('border-light');
                });
            });
        });
    }

    if (increaseBtn && quantityInput) {
        increaseBtn.addEventListener('click', function() {
            let currentQty = parseInt(quantityInput.value);
            if (currentQty < maxQty) {
                quantityInput.value = currentQty + 1;
            }
        });
    }

    if (decreaseBtn && quantityInput) {
        decreaseBtn.addEventListener('click', function() {
            let currentQty = parseInt(quantityInput.value);
            if (currentQty > 1) {
                quantityInput.value = currentQty - 1;
            }
        });
    }

    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            let val = parseInt(this.value);
            if (!isNaN(val)) {
                if (val > maxQty) {
                    this.value = maxQty;
                } else if (val < 1) {
                    this.value = 1;
                }
            }
        });
    }

    // Add to cart with animation
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            // Check if user is authenticated
            const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
            
            if (!isAuthenticated) {
                showToast('Silakan login terlebih dahulu untuk menambahkan ke keranjang', 'info', 3000);
                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
                }, 1500);
                return;
            }

            // Check if product has variants and one is selected
            const hasVariants = variantButtons.length > 0;
            const selectedVariantId = selectedVariantInput ? selectedVariantInput.value : '';

            if (hasVariants && !selectedVariantId) {
                showToast('Silakan pilih varian produk terlebih dahulu!', 'warning', 3000);
                return;
            }

            const quantity = parseInt(quantityInput.value);
            const productId = {{ $product->id }};
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
                body: JSON.stringify({ 
                    quantity: quantity,
                    product_variant_id: selectedVariantId
                })
            })
            .then(response => {
                if (response.status === 401) {
                    showToast('Sesi Anda telah berakhir, silakan login kembali', 'error', 3000);
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 1500);
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                
                if (data.success) {
                    showToast('✓ Produk berhasil ditambahkan ke keranjang!', 'success', 4000);
                    
                    if (data.cartCount) {
                        updateCartCount(data.cartCount);
                    }

                    if(typeof openOffcanvasCart === 'function') {
                        openOffcanvasCart();
                    }
                    
                    quantityInput.value = 1;
                    
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
    }

    // Add to wishlist
    const wishlistBtn = document.getElementById('addToWishlistBtn');
    if (wishlistBtn) {
        @if(auth()->check())
            wishlistBtn.addEventListener('click', function() {
                const productId = {{ $product->id }};
                const originalHTML = this.innerHTML;
                
                this.disabled = true;
                this.innerHTML = '<span class="spinner-animation"></span>Menambahkan...';
                
                fetch("{{ url('/wishlist/add') }}/" + productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('♥ Produk ditambahkan ke Wishlist!', 'success', 3000);
                    } else {
                        showToast(data.message || 'Gagal menambahkan ke Wishlist', 'info');
                    }
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan', 'error');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                });
            });
        @else
            wishlistBtn.addEventListener('click', function() {
                window.location.href = "{{ route('login') }}";
            });
        @endif
    }
});
</script>
@endsection
