@extends('layouts.frontend')

@section('content')
<div class="container py-3">

    {{-- Breadcrumb Minimalis --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0 align-items-center small">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-secondary text-decoration-none hover-primary d-inline-flex align-items-center">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm" style="height: 24px; width: auto;" class="me-1.5 object-fit-contain"> Home
                </a>
            </li>
            <li class="breadcrumb-item text-muted">
                <i class="fas fa-chevron-right mx-1 opacity-50" style="font-size: 0.65rem;"></i>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('public.category', $product->category->slug) }}" class="text-secondary text-decoration-none hover-primary">
                    {{ $product->category->nama_kategori }}
                </a>
            </li>
            <li class="breadcrumb-item text-muted">
                <i class="fas fa-chevron-right mx-1 opacity-50" style="font-size: 0.65rem;"></i>
            </li>
            <li class="breadcrumb-item active text-dark fw-semibold text-truncate" style="max-width: 250px;" aria-current="page">{{ $product->nama_barang }}</li>
        </ol>
    </nav>

    <div class="row g-4 g-lg-5">
        {{-- Product Image Showcase (Square 1:1 Aspect Ratio) --}}
        <div class="col-lg-6 col-md-6">
            <div class="card border-0 rounded-4 overflow-hidden mb-3 bg-white shadow-sm" style="border: 1px solid #f1f5f9 !important;">
                <div class="product-img-wrapper position-relative overflow-hidden" style="aspect-ratio: 1/1;">
                    @if($product->gambar)
                        <img src="{{ asset('storage/' . $product->gambar) }}" 
                             class="w-100 h-100 object-fit-cover transition-all"
                             alt="{{ $product->nama_barang }}"
                             id="productImage">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 bg-slate-50">
                            <div class="text-center">
                                <i class="fas fa-box fa-4x text-secondary opacity-30"></i>
                                <p class="text-muted small mt-2">Gambar tidak tersedia</p>
                            </div>
                        </div>
                    @endif

                    {{-- Badges Overlays --}}
                    <div class="position-absolute top-0 start-0 m-3">
                        @if($product->stok_aktual <= $product->nilai_ss)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 shadow-sm fw-bold">
                                <i class="fas fa-exclamation-circle me-1"></i> Stok Habis / Kritis
                            </span>
                        @else
                            <span class="badge bg-success text-white rounded-pill px-3 py-1.5 shadow-sm fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Ready Stock
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Gallery Thumbnails --}}
            @if($product->images->isNotEmpty())
                <div class="d-flex gap-2 overflow-x-auto pb-2 mb-3 no-scrollbar">
                    @foreach($product->images as $index => $img)
                        <div class="thumbnail-wrapper rounded-3 overflow-hidden cursor-pointer {{ $index === 0 ? 'border-primary border-2 shadow-sm' : 'border-light border' }}"
                             style="width: 72px; height: 72px; flex-shrink: 0; cursor: pointer; transition: all 0.2s;"
                             onclick="switchMainImage('{{ asset('storage/' . $img->path) }}', this)">
                            <img src="{{ asset('storage/' . $img->path) }}" class="w-100 h-100 object-fit-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Details Info --}}
        <div class="col-lg-6 col-md-6">
            {{-- Category Tag & Rating --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <a href="{{ route('public.category', $product->category->slug) }}" class="badge bg-dark text-white rounded-pill px-3 py-1.5 fw-semibold small text-uppercase tracking-wider text-decoration-none">
                    <i class="fas fa-tag me-1 text-white-50"></i> {{ $product->category->nama_kategori }}
                </a>

                @php
                    $avgRating = $product->reviews->avg('rating') ?? 0;
                    $reviewCount = $product->reviews->count();
                @endphp
                <div class="d-flex align-items-center gap-1.5 bg-light px-2.5 py-1 rounded-pill small">
                    <div class="text-warning" style="font-size: 0.82rem;">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $avgRating)
                                <i class="fas fa-star"></i>
                            @elseif($i - 0.5 <= $avgRating)
                                <i class="fas fa-star-half-alt"></i>
                            @else
                                <i class="far fa-star text-muted"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="fw-bold text-dark ms-1" style="font-size: 0.8rem;">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted" style="font-size: 0.78rem;">({{ $reviewCount }} Ulasan)</span>
                </div>
            </div>

            {{-- Title --}}
            <h2 class="fw-bold text-dark mb-3 tracking-tight" style="font-size: 1.75rem; line-height: 1.35;">
                {{ $product->nama_barang }}
            </h2>

            @php
                $isB2B = auth()->check() && auth()->user()->role === 'branch';
                $tiers = $isB2B ? $product->b2bPriceSettings()->orderBy('min_qty')->get() : collect();
                $b2bPrice = $isB2B ? $product->getB2bPrice(1) : null;
                $actualPrice = $product->harga_jual_actual;
            @endphp

            {{-- Minimalist Price Container --}}
            <div class="card border-0 rounded-4 p-3.5 mb-4 shadow-sm bg-slate-50">
                <div class="d-flex align-items-baseline gap-2 flex-wrap">
                    @if($isB2B && $b2bPrice < $actualPrice)
                        <h2 class="text-primary fw-bold mb-0 display-6 fs-2">
                            Rp {{ number_format($b2bPrice, 0, ',', '.') }}
                        </h2>
                        <span class="text-muted text-decoration-line-through me-2">
                            Rp {{ number_format($actualPrice, 0, ',', '.') }}
                        </span>
                        <span class="badge bg-info text-white rounded-pill px-2.5 py-1 small">Harga B2B</span>
                    @else
                        <h2 class="text-primary fw-bold mb-0 display-6 fs-2">
                            {{ $product->formatted_price }}
                        </h2>
                        @if($product->is_promo && $product->diskon_persen > 0)
                            <span class="text-muted text-decoration-line-through me-2">
                                Rp {{ number_format($actualPrice, 0, ',', '.') }}
                            </span>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 small">
                                -{{ $product->diskon_persen }}%
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- PRICING TIER CARDS (Tingkat Harga Grosir / Multi-Unit Savings) --}}
            @php
                $normalPrice = $product->harga_jual_actual;
                $tiersList = collect();
                $dbTiers = $product->b2bPriceSettings()->whereNull('product_variant_id')->orderBy('min_qty')->get();

                if ($dbTiers->isNotEmpty()) {
                    foreach ($dbTiers as $t) {
                        $unitPrice = $t->discount_type === 'percentage' 
                            ? $normalPrice - ($normalPrice * ($t->discount_value / 100))
                            : $normalPrice - $t->discount_value;
                        
                        $savePercent = $normalPrice > 0 ? round((($normalPrice - $unitPrice) / $normalPrice) * 100) : 0;
                        
                        $tiersList->push([
                            'min_qty' => $t->min_qty,
                            'unit_price' => max($unitPrice, 0),
                            'save_percent' => max($savePercent, 0),
                            'label' => 'Beli ' . $t->min_qty . '+ pcs',
                        ]);
                    }
                } else {
                    // Fallback Tier Rules jika belum di-set di DB (Tier 1: Normal, Tier 2: Beli 3+ Hemat 5%, Tier 3: Beli 6+ Hemat 10%)
                    $tiersList->push([
                        'min_qty' => 1,
                        'unit_price' => $normalPrice,
                        'save_percent' => 0,
                        'label' => 'Beli 1 - 2 pcs',
                    ]);
                    $tiersList->push([
                        'min_qty' => 3,
                        'unit_price' => $normalPrice * 0.95,
                        'save_percent' => 5,
                        'label' => 'Beli 3 - 5 pcs',
                    ]);
                    $tiersList->push([
                        'min_qty' => 6,
                        'unit_price' => $normalPrice * 0.90,
                        'save_percent' => 10,
                        'label' => 'Beli 6+ pcs',
                    ]);
                }
            @endphp

            <div class="card border-0 rounded-4 p-3.5 mb-4 shadow-sm card-pricing-tier">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold card-pricing-tier-title mb-0 d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                        <i class="fas fa-tags text-teal"></i>
                        <span>Harga Grosir &amp; Tier Discount</span>
                    </h6>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                        <i class="fas fa-fire me-1"></i> Beli Banyak Lebih Hemat
                    </span>
                </div>

                <div class="row row-cols-1 row-cols-sm-3 g-2" id="pricingTierContainer">
                    @foreach($tiersList as $index => $tier)
                        <div class="col">
                            <div class="tier-card-option {{ $index === 0 ? 'active-tier' : '' }}" 
                                 data-min-qty="{{ $tier['min_qty'] }}"
                                 data-unit-price="{{ $tier['unit_price'] }}"
                                 onclick="selectPricingTier(this, {{ $tier['min_qty'] }}, {{ $tier['unit_price'] }})">
                                <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                                    <span class="fw-bold small tier-label" style="font-size: 0.82rem;">{{ $tier['label'] }}</span>
                                    @if($tier['save_percent'] > 0)
                                        <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                            Hemat {{ $tier['save_percent'] }}%
                                        </span>
                                    @else
                                        <span class="badge bg-secondary opacity-75 text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                            Normal
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-baseline gap-1">
                                    <span class="fw-bold text-teal fs-6">Rp {{ number_format($tier['unit_price'], 0, ',', '.') }}</span>
                                    <span class="small text-muted" style="font-size: 0.7rem;">/ unit</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Deskripsi Produk</h6>
                    <p class="text-muted small leading-relaxed mb-0">{!! nl2br(e($product->description)) !!}</p>
                </div>
            @endif

            {{-- SKU & Info --}}
            <div class="d-flex align-items-center gap-4 py-2 border-top border-bottom border-light mb-4 text-muted small">
                <div>
                    <span>SKU: </span>
                    <strong class="text-dark" id="productSku">{{ $product->kode_barang ?? 'N/A' }}</strong>
                </div>
                <div>
                    <span>Safety Stock: </span>
                    <strong class="text-dark">{{ $product->nilai_ss }} unit</strong>
                </div>
            </div>

            {{-- Variant Selector --}}
            @if($product->variants->isNotEmpty())
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">Pilih Varian <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2" id="variantSelector">
                        @foreach($product->variants as $variant)
                            @php
                                $varPrice = $isB2B ? $variant->getB2bPrice(1) : $variant->harga_jual_actual;
                            @endphp
                            <button type="button" 
                                class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 variant-btn small fw-semibold" 
                                data-variant-id="{{ $variant->id }}" 
                                data-variant-stock="{{ $variant->stok_aktual }}"
                                data-variant-price="Rp {{ number_format($varPrice, 0, ',', '.') }}"
                                data-variant-sku="{{ $variant->kode_barang ?? $product->kode_barang }}"
                                data-variant-image="{{ $variant->gambar ? asset('storage/' . $variant->gambar) : '' }}"
                                {{ $variant->stok_aktual <= 0 ? 'disabled' : '' }}>
                                {{ $variant->nama_varian }}
                                @if($variant->stok_aktual <= 0)
                                    <span class="small opacity-50 ms-1">(Habis)</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedVariantId" value="">
                </div>
            @endif

            {{-- Quantity Counter & Purchase Buttons --}}
            @if($product->stok_aktual > $product->nilai_ss)
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">Jumlah Pembelian</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-white" style="width: 140px;">
                            <button class="btn btn-white border-0 px-3" type="button" id="decreaseQty">-</button>
                            <input type="number" class="form-control text-center border-0 fw-bold" id="quantity" value="1" min="1" max="{{ $product->stok_aktual }}">
                            <button class="btn btn-white border-0 px-3" type="button" id="increaseQty">+</button>
                        </div>
                        <span class="text-muted small" id="stockLimitText">Tersedia {{ $product->stok_aktual }} unit</span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-8 col-sm-9">
                        <button class="btn btn-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-sm" id="addToCartBtn">
                            <i class="fas fa-shopping-bag me-2"></i> Tambah ke Keranjang
                        </button>
                    </div>
                    <div class="col-4 col-sm-3">
                        <button class="btn btn-outline-danger btn-lg rounded-pill w-100 py-3" id="addToWishlistBtn" title="Wishlist">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-danger rounded-4 border-0 p-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Stok produk ini sedang habis atau dalam batas kritis.
                </div>
            @endif


        </div>
    </div>

    {{-- Reviews Section --}}
    <hr class="my-5 border-light">
    <div class="mb-5">
        <h4 class="fw-bold text-dark mb-4">Ulasan Pembeli ({{ $reviewCount }})</h4>
        
        <div class="row g-4">
            <div class="col-lg-8">
                @forelse($product->reviews as $review)
                    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-3 bg-white" style="border: 1px solid #f1f5f9 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark small">{{ $review->user->name }}</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $review->created_at->format('d M Y') }}</small>
                        </div>
                        <div class="text-warning mb-2" style="font-size: 0.8rem;">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star text-muted"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="mb-0 text-secondary small leading-relaxed">{{ $review->comment }}</p>
                    </div>
                @empty
                    <div class="card border-0 rounded-4 p-4 text-center bg-slate-50">
                        <p class="text-muted small mb-0">Belum ada ulasan untuk produk ini.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="col-lg-4">
                @auth
                    @php
                        $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                            ->where('status', 'completed')
                            ->whereHas('items', function($q) use ($product) {
                                $q->where('product_id', $product->id);
                            })->first();
                            
                        $hasReviewed = false;
                        if ($hasPurchased) {
                            $hasReviewed = \App\Models\Review::where('user_id', auth()->id())
                                ->where('product_id', $product->id)
                                ->where('order_id', $hasPurchased->id)
                                ->exists();
                        }
                    @endphp

                    @if($hasPurchased && !$hasReviewed)
                        <div class="card border-0 rounded-4 p-4 shadow-sm bg-white" style="border: 1px solid #f1f5f9 !important;">
                            <h6 class="fw-bold text-dark mb-3">Tulis Ulasan Anda</h6>
                            <form action="{{ route('review.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="order_id" value="{{ $hasPurchased->id }}">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Rating</label>
                                    <select name="rating" class="form-select form-select-sm rounded-3" required>
                                        <option value="5">5 Bintang - Sangat Bagus</option>
                                        <option value="4">4 Bintang - Bagus</option>
                                        <option value="3">3 Bintang - Cukup</option>
                                        <option value="2">2 Bintang - Kurang</option>
                                        <option value="1">1 Bintang - Sangat Kurang</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Komentar</label>
                                    <textarea name="comment" class="form-control form-control-sm rounded-3" rows="3" required placeholder="Bagaimana pengalaman Anda dengan produk ini?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm fw-semibold shadow-sm">Kirim Ulasan</button>
                            </form>
                        </div>
                    @elseif($hasPurchased && $hasReviewed)
                        <div class="alert alert-success rounded-4 small mb-0">
                            Terima kasih, Anda telah memberikan ulasan untuk produk ini.
                        </div>
                    @endif
                @else
                    <div class="alert alert-light border rounded-4 small text-center mb-0">
                        Silakan <a href="{{ route('login') }}" class="fw-bold text-primary">login</a> untuk menulis ulasan.
                    </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->count() > 0)
        <hr class="my-5 border-light">
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-4">Produk Terkait</h4>
            <div class="row g-3 g-md-4">
                @foreach($relatedProducts as $related)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="minimalist-product-card card h-100 border-0 rounded-4 overflow-hidden bg-white shadow-sm transition-all" 
                             style="border: 1px solid #f1f5f9 !important;"
                             onclick="window.location.href='{{ route('public.product.show', $related->slug) }}'">
                            
                            <div class="product-img-wrapper position-relative overflow-hidden bg-light" style="aspect-ratio: 1/1;">
                                @if($related->gambar)
                                    <img src="{{ asset('storage/' . $related->gambar) }}" class="w-100 h-100 object-fit-cover product-img">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-slate-50">
                                        <i class="fas fa-box fa-2x text-secondary opacity-30"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="card-title fw-semibold text-dark text-truncate mb-1" style="font-size: 0.88rem;">{{ $related->nama_barang }}</h6>
                                <p class="text-primary fw-bold mb-3 small">{{ $related->formatted_price }}</p>
                                <div class="mt-auto">
                                    <a href="{{ route('public.product.show', $related->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                                        Lihat Detail
                                    </a>
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
function selectPricingTier(element, minQty, unitPrice) {
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        let maxQty = parseInt(qtyInput.getAttribute('max')) || 9999;
        let targetQty = Math.min(minQty, maxQty);
        qtyInput.value = targetQty;
        qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
        qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.querySelectorAll('.tier-card-option').forEach(el => {
        el.classList.remove('active-tier');
    });
    if (element) {
        element.classList.add('active-tier');
    }
}

function updateActiveTierByQty(currentQty) {
    const tierCards = document.querySelectorAll('.tier-card-option');
    if (!tierCards.length) return;

    let matchedCard = null;
    let highestMinQty = 0;

    tierCards.forEach(card => {
        const minQty = parseInt(card.getAttribute('data-min-qty') || 1);
        if (currentQty >= minQty && minQty >= highestMinQty) {
            highestMinQty = minQty;
            matchedCard = card;
        }
    });

    if (matchedCard) {
        tierCards.forEach(el => el.classList.remove('active-tier'));
        matchedCard.classList.add('active-tier');
    }
}

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
    const quantityInput = document.getElementById('quantity');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const addToWishlistBtn = document.getElementById('addToWishlistBtn');
    let maxQty = quantityInput ? parseInt(quantityInput.getAttribute('max')) : 0;

    const variantButtons = document.querySelectorAll('.variant-btn');
    const selectedVariantInput = document.getElementById('selectedVariantId');
    const stockLimitText = document.getElementById('stockLimitText');
    const priceDisplay = document.querySelector('.display-6.fs-2');
    const skuDisplay = document.getElementById('productSku');

    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const current = parseInt(this.value) || 1;
            updateActiveTierByQty(current);
        });
    }

    if (increaseBtn && quantityInput) {
        increaseBtn.addEventListener('click', function() {
            let current = parseInt(quantityInput.value) || 1;
            if (current < maxQty) {
                quantityInput.value = current + 1;
                updateActiveTierByQty(current + 1);
            }
        });
    }

    if (decreaseBtn && quantityInput) {
        decreaseBtn.addEventListener('click', function() {
            let current = parseInt(quantityInput.value) || 1;
            if (current > 1) {
                quantityInput.value = current - 1;
                updateActiveTierByQty(current - 1);
            }
        });
    }

    if (variantButtons.length > 0) {
        variantButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                variantButtons.forEach(b => {
                    b.classList.remove('btn-primary', 'text-white');
                    b.classList.add('btn-outline-primary');
                });

                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'text-white');

                const variantId = this.getAttribute('data-variant-id');
                selectedVariantInput.value = variantId;

                const variantStock = parseInt(this.getAttribute('data-variant-stock'));
                maxQty = variantStock;
                if (quantityInput) {
                    quantityInput.setAttribute('max', variantStock);
                    if (parseInt(quantityInput.value) > variantStock) {
                        quantityInput.value = variantStock > 0 ? 1 : 0;
                    }
                }
                if (stockLimitText) {
                    stockLimitText.textContent = `Tersedia ${variantStock} unit`;
                }

                const variantPrice = this.getAttribute('data-variant-price');
                if (priceDisplay && variantPrice) {
                    priceDisplay.textContent = variantPrice;
                }

                const variantSku = this.getAttribute('data-variant-sku');
                if (skuDisplay && variantSku) {
                    skuDisplay.textContent = variantSku;
                }

                const variantImage = this.getAttribute('data-variant-image');
                const mainImage = document.getElementById('productImage');
                if (mainImage && variantImage) {
                    mainImage.src = variantImage;
                }
            });
        });
    }

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const hasVariants = variantButtons.length > 0;
            const selectedVariantId = selectedVariantInput ? selectedVariantInput.value : null;

            if (hasVariants && !selectedVariantId) {
                showToast('Silakan pilih varian produk terlebih dahulu.', 'error');
                return;
            }

            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            const productId = "{{ $product->id }}";
            const originalHTML = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menambahkan...';

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
                    showToast('Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.', 'error', 3000);
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 1500);
                    this.disabled = false;
                    this.innerHTML = originalHTML;
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                if (data.success) {
                    showToast('✓ Produk berhasil ditambahkan ke keranjang!', 'success');
                    if (data.cartCount) updateCartCount(data.cartCount);
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

    if (addToWishlistBtn) {
        addToWishlistBtn.addEventListener('click', function() {
            const productId = "{{ $product->id }}";
            fetch(`{{ route('wishlist.add', ':id') }}`.replace(':id', productId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    if (data.wishlistCount !== undefined) updateWishlistCount(data.wishlistCount);
                } else {
                    showToast(data.message, 'info');
                }
            })
            .catch(err => console.error(err));
        });
    }
});
</script>
@endpush
@endsection
