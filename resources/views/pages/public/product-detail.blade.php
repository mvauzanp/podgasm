@extends('layouts.frontend')

@push('styles')
<style>
    .mobile-sticky-action-bar {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transform: translateY(115%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
        opacity: 0;
        pointer-events: none;
        padding-bottom: max(10px, env(safe-area-inset-bottom, 10px));
        border-top: 1px solid rgba(226, 232, 240, 0.9) !important;
        box-shadow: 0 -6px 20px rgba(15, 23, 42, 0.08) !important;
    }
    .mobile-sticky-action-bar.visible {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
    }
    /* Dark mode support */
    html[data-bs-theme="dark"] .mobile-sticky-action-bar,
    body.dark-mode .mobile-sticky-action-bar {
        background: rgba(15, 23, 42, 0.96) !important;
        border-top: 1px solid rgba(51, 65, 85, 0.8) !important;
        box-shadow: 0 -6px 25px rgba(0, 0, 0, 0.5) !important;
    }
    html[data-bs-theme="dark"] .mobile-sticky-action-bar #mobileStickyVariantLabel,
    body.dark-mode .mobile-sticky-action-bar #mobileStickyVariantLabel {
        color: #94a3b8 !important;
    }
    html[data-bs-theme="dark"] .mobile-sticky-action-bar #mobileStickyVariantLabel .text-dark,
    body.dark-mode .mobile-sticky-action-bar #mobileStickyVariantLabel .text-dark {
        color: #f1f5f9 !important;
    }
    
    /* Defensive mobile bottom padding to prevent fixed bar content occlusion */
    @media (max-width: 767.98px) {
        .product-detail-container {
            padding-bottom: calc(85px + env(safe-area-inset-bottom, 0px)) !important;
        }
    }

    /* Pulse animation when scrolling to variant selector */
    @keyframes pulseVariantHighlight {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5); }
        50% { transform: scale(1.02); box-shadow: 0 0 0 8px rgba(13, 110, 253, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    .pulse-highlight {
        animation: pulseVariantHighlight 0.6s ease-in-out 2;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="container py-2 py-md-3 product-detail-container">

    {{-- Breadcrumb Minimalis --}}
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0 align-items-center small flex-nowrap overflow-x-auto text-nowrap py-1 no-scrollbar">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-secondary text-decoration-none hover-primary">Home</a>
            </li>
            @if($product->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('public.category', $product->category->slug) }}" class="text-secondary text-decoration-none hover-primary">
                        {{ $product->category->nama_kategori }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active text-dark fw-semibold text-truncate" style="max-width: 220px;" aria-current="page">{{ $product->nama_barang }}</li>
        </ol>
    </nav>

    <div class="row g-3 g-md-4 g-lg-5">
        {{-- Product Image Showcase (Square 1:1 Aspect Ratio) --}}
        <div class="col-lg-6 col-md-6">
            <div class="card border-0 rounded-4 overflow-hidden mb-3 bg-transparent shadow-none" style="border: none !important;">
                <div class="product-img-wrapper position-relative overflow-hidden d-flex align-items-center justify-content-center bg-transparent rounded-4" style="aspect-ratio: 1/1; border: none !important;">
                    @if($product->gambar)
                        <img src="{{ $product->gambar_url }}" 
                             class="w-100 h-100 object-fit-cover rounded-4 transition-all"
                             alt="{{ $product->nama_barang }}"
                             id="productImage"
                             style="border: none !important; transition: opacity 0.2s ease-in-out;">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-slate-800 rounded-4">
                            <div class="text-center">
                                <i class="fas fa-box fa-4x text-secondary opacity-30"></i>
                                <p class="text-muted small mt-2">Gambar tidak tersedia</p>
                            </div>
                        </div>
                    @endif

                    {{-- Badges Overlays --}}
                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1">
                        @if($product->total_stok <= 0)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 shadow-sm fw-bold">
                                <i class="fas fa-times-circle me-1"></i> Stok Habis
                            </span>
                        @elseif($product->total_stok <= ($product->nilai_ss ?? 5))
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 shadow-sm fw-bold">
                                <i class="fas fa-fire me-1 text-danger"></i> Stok Terbatas (Sisa {{ $product->total_stok }} unit)
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
                <div class="d-flex align-items-center gap-1.5 small py-1">
                    <div class="text-warning" style="font-size: 0.88rem;">
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
                    <span class="fw-bold text-dark ms-1" style="font-size: 0.88rem;">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-muted" style="font-size: 0.82rem;">({{ $reviewCount }} Ulasan)</span>
                </div>
            </div>

            {{-- Title --}}
            <h2 class="fw-bold text-dark mb-3 tracking-tight" style="font-size: 1.85rem; line-height: 1.3;">
                {{ $product->nama_barang }}
            </h2>

            @php
                $actualPrice = $product->harga_jual_actual;
                $isEligibleTier = $product->isEligibleForTierDiscount();
                $pricingTiers = $product->getPricingTiers();
            @endphp

            {{-- Clean & Seamless Price Typography (No Box / Pure Transparent) --}}
            <div class="d-flex align-items-baseline gap-2.5 flex-wrap mb-4 py-1">
                <h2 class="text-primary fw-bold mb-0 display-6 fs-1" style="letter-spacing: -0.02em;" id="mainPriceDisplay">
                    {{ $product->formatted_price }}
                </h2>
                @if($product->is_promo && $product->diskon_persen > 0)
                    <span class="text-muted text-decoration-line-through fs-5 me-1" id="originalPriceDisplay">
                        Rp {{ number_format($actualPrice, 0, ',', '.') }}
                    </span>
                    <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small" id="promoBadgeDisplay">
                        -{{ $product->diskon_persen }}%
                    </span>
                @endif
            </div>

            {{-- PRICING TIER CARDS (Tingkat Harga Grosir / Multi-Unit Savings) --}}
            @if($isEligibleTier || $product->b2bPriceSettings()->whereNull('product_variant_id')->exists())
            <div class="card-pricing-tier mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold card-pricing-tier-title mb-0 d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                        <i class="fas fa-tags text-primary"></i>
                        <span>Harga Grosir &amp; Potongan Kuantitas</span>
                    </h6>
                    <span class="badge rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center tier-highlight-badge shadow-sm" style="background: #0f172a; color: #ffffff !important; font-size: 0.74rem; border: none;">
                        <i class="fas fa-fire me-1.5 text-danger"></i> Beli Banyak Lebih Hemat s/d 10%
                    </span>
                </div>

                <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-5 g-2" id="pricingTierContainer">
                    @foreach($pricingTiers as $index => $tier)
                        <div class="col">
                            <div class="tier-card-option p-2.5 rounded-3 text-center h-100 {{ $index === 0 ? 'active-tier' : '' }}" 
                                 data-min-qty="{{ $tier['min_qty'] }}"
                                 data-save-percent="{{ $tier['save_percent'] }}"
                                 data-unit-price="{{ $tier['unit_price'] }}"
                                 onclick="selectPricingTier(this, {{ $tier['min_qty'] }}, {{ $tier['unit_price'] }})">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <span class="fw-bold small tier-label" style="font-size: 0.8rem;">{{ $tier['label'] }}</span>
                                </div>
                                <div class="mb-1">
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
                                <div class="d-flex align-items-baseline justify-content-center gap-0.5">
                                    <span class="fw-bold text-primary tier-price-display" style="font-size: 0.85rem;">Rp {{ number_format($tier['unit_price'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Description --}}
            @if($product->description)
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Deskripsi Produk</h6>
                    <p class="text-muted small leading-relaxed mb-0">{!! nl2br(e($product->description)) !!}</p>
                </div>
            @endif

            {{-- SKU & Info --}}
            <div class="d-flex align-items-center gap-4 py-2 border-top border-bottom border-light mb-4 text-muted small flex-wrap">
                <div>
                    <span>SKU: </span>
                    <strong class="text-dark" id="productSku">{{ $product->kode_barang ?? 'N/A' }}</strong>
                </div>
                <div>
                    <span>Kategori: </span>
                    <strong class="text-dark">{{ $product->category ? $product->category->nama_kategori : 'Umum' }}</strong>
                </div>
                <div>
                    <span>Kondisi: </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold">100% Original</span>
                </div>
            </div>

            {{-- Variant Selector --}}
            @if($product->variants->isNotEmpty())
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">Pilih Varian <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2" id="variantSelector">
                        @foreach($product->variants as $variant)
                            @php
                                $varPrice = $variant->harga_jual_actual;
                                $varImg = $variant->gambar_url ?: $product->gambar_url;
                            @endphp
                            <button type="button" 
                                class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 variant-btn small fw-semibold position-relative" 
                                data-variant-id="{{ $variant->id }}" 
                                data-variant-name="{{ $variant->nama_varian }}"
                                data-variant-stock="{{ $variant->stok_aktual }}"
                                data-variant-price="Rp {{ number_format($varPrice, 0, ',', '.') }}"
                                data-raw-price="{{ $varPrice }}"
                                data-variant-sku="{{ $variant->kode_barang ?? $product->kode_barang }}"
                                data-variant-image="{{ $varImg }}"
                                {{ $variant->stok_aktual <= 0 ? 'data-out-of-stock=1' : '' }}>
                                {{ $variant->nama_varian }}
                                @if($variant->stok_aktual <= 0)
                                    <span class="small text-danger ms-1 fw-normal">(Habis)</span>
                                @else
                                    <span class="small text-muted ms-1 fw-normal">({{ $variant->stok_aktual }})</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedVariantId" value="">
                </div>
            @endif

            {{-- Quantity Counter & Purchase Buttons --}}
            @if($product->total_stok > 0)
                @if($product->total_stok <= ($product->nilai_ss ?? 5))
                    <div class="alert alert-warning border-0 rounded-4 py-2 px-3 mb-3 d-flex align-items-center gap-2 small">
                        <i class="fas fa-fire text-danger"></i>
                        <span><strong>Stok Terbatas:</strong> Segera pesan sebelum kehabisan (tersisa {{ $product->total_stok }} unit).</span>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">Jumlah Pembelian</label>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-white" style="width: 140px;">
                            <button class="btn btn-white border-0 px-3" type="button" id="decreaseQty">-</button>
                            <input type="number" class="form-control text-center border-0 fw-bold" id="quantity" value="1" min="1" max="{{ $product->total_stok }}">
                            <button class="btn btn-white border-0 px-3" type="button" id="increaseQty">+</button>
                        </div>
                        <span class="text-muted small" id="stockLimitText">
                            @if($product->variants->isNotEmpty())
                                Tersedia {{ $product->total_stok }} unit total
                            @else
                                Tersedia {{ $product->stok_aktual }} unit
                            @endif
                        </span>
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

                {{-- Social Share & Copy Link 1-Klik --}}
                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top border-light flex-wrap">
                    <span class="small text-muted fw-medium me-1">Bagikan:</span>
                    <a href="https://wa.me/?text={{ urlencode('Cek produk ' . $product->nama_barang . ' di Podgasm: ' . url()->current()) }}" 
                       target="_blank" 
                       class="btn btn-light btn-sm rounded-pill px-3 py-1.5 small text-success fw-semibold border shadow-none" 
                       title="Bagikan ke WhatsApp">
                        <i class="fab fa-whatsapp me-1 fs-6"></i> WhatsApp
                    </a>
                    <button type="button" 
                            class="btn btn-light btn-sm rounded-pill px-3 py-1.5 small text-dark fw-semibold border shadow-none" 
                            onclick="copyProductLink()" 
                            id="btnCopyProductLink">
                        <i class="fas fa-link me-1 text-primary"></i> Salin Link
                    </button>
                </div>
            @else
                <div class="alert alert-danger rounded-4 border-0 p-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Stok produk ini saat ini sedang habis. Silakan cek kembali nanti atau hubungi CS kami.
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
                    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-3 bg-white">
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
                        <div class="card border-0 rounded-4 p-4 shadow-sm bg-white">
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
                             onclick="window.location.href='{{ route('public.product.show', $related->slug) }}'">
                            
                            <div class="product-img-wrapper position-relative overflow-hidden bg-light" style="aspect-ratio: 1/1;">
                                @if($related->gambar)
                                    <img src="{{ $related->gambar_url }}" class="w-100 h-100 object-fit-cover product-img">
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

        const activeUnitPrice = parseFloat(matchedCard.getAttribute('data-unit-price') || 0);
        const priceDisplay = document.getElementById('mainPriceDisplay');
        if (priceDisplay && activeUnitPrice > 0) {
            priceDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(activeUnitPrice);
        }
        const stickyPrice = document.getElementById('mobileStickyPrice');
        if (stickyPrice && activeUnitPrice > 0) {
            stickyPrice.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(activeUnitPrice);
        }
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
    const priceDisplay = document.getElementById('mainPriceDisplay') || document.querySelector('.display-6.fs-2');
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
                if (selectedVariantInput) selectedVariantInput.value = variantId;

                const variantStock = parseInt(this.getAttribute('data-variant-stock')) || 0;
                maxQty = variantStock;
                
                if (quantityInput) {
                    quantityInput.setAttribute('max', variantStock);
                    if (variantStock <= 0) {
                        quantityInput.value = 0;
                    } else if (parseInt(quantityInput.value) <= 0 || parseInt(quantityInput.value) > variantStock) {
                        quantityInput.value = 1;
                    }
                }

                if (stockLimitText) {
                    if (variantStock > 0) {
                        stockLimitText.innerHTML = `<span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i> Tersedia ${variantStock} unit</span>`;
                    } else {
                        stockLimitText.innerHTML = `<span class="text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i> Varian ini Habis</span>`;
                    }
                }

                const variantPrice = this.getAttribute('data-variant-price');
                if (priceDisplay && variantPrice) {
                    priceDisplay.textContent = variantPrice;
                }

                const rawPrice = parseFloat(this.getAttribute('data-raw-price') || 0);
                if (rawPrice > 0) {
                    document.querySelectorAll('.tier-card-option').forEach(card => {
                        const savePercent = parseFloat(card.getAttribute('data-save-percent') || 0);
                        const newUnitPrice = Math.round(rawPrice - (rawPrice * (savePercent / 100)));
                        card.setAttribute('data-unit-price', newUnitPrice);
                        const priceSpan = card.querySelector('.tier-price-display');
                        if (priceSpan) {
                            priceSpan.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newUnitPrice);
                        }
                    });
                }

                const currentQty = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
                updateActiveTierByQty(currentQty);

                const variantSku = this.getAttribute('data-variant-sku');
                if (skuDisplay && variantSku) {
                    skuDisplay.textContent = variantSku;
                }

                // Ganti gambar utama produk sesuai foto varian
                const variantImage = this.getAttribute('data-variant-image');
                const mainImage = document.getElementById('productImage');
                if (mainImage && variantImage && variantImage.trim() !== '') {
                    mainImage.style.opacity = '0.4';
                    setTimeout(() => {
                        mainImage.src = variantImage;
                        mainImage.style.opacity = '1';
                    }, 120);
                }

                // Update tombol Add to Cart jika varian tertentu habis
                if (addToCartBtn) {
                    if (variantStock <= 0) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.classList.remove('btn-primary');
                        addToCartBtn.classList.add('btn-secondary');
                        addToCartBtn.innerHTML = '<i class="fas fa-times-circle me-2"></i> Varian Ini Habis';
                    } else {
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('btn-secondary');
                        addToCartBtn.classList.add('btn-primary');
                        addToCartBtn.innerHTML = '<i class="fas fa-shopping-bag me-2"></i> Tambah ke Keranjang';
                    }
                }

                // Sync mobile sticky action bar
                const stickyPrice = document.getElementById('mobileStickyPrice');
                const stickyImg = document.getElementById('mobileStickyImg');
                const stickyVariantLabel = document.getElementById('mobileStickyVariantLabel');
                const stickyActionBtn = document.getElementById('mobileStickyActionBtn');
                const stickyBtnText = document.getElementById('mobileStickyBtnText');

                if (stickyPrice && variantPrice) {
                    stickyPrice.textContent = variantPrice;
                }
                if (stickyImg && variantImage && variantImage.trim() !== '') {
                    stickyImg.src = variantImage;
                }
                if (stickyVariantLabel) {
                    const varName = this.getAttribute('data-variant-name') || this.innerText.trim();
                    if (variantStock > 0) {
                        stickyVariantLabel.innerHTML = `<span class="text-dark fw-semibold">${varName}</span> <span class="text-success small">(${variantStock})</span>`;
                    } else {
                        stickyVariantLabel.innerHTML = `<span class="text-dark fw-semibold">${varName}</span> <span class="text-danger small">(Habis)</span>`;
                    }
                }
                if (stickyActionBtn) {
                    if (variantStock <= 0) {
                        stickyActionBtn.disabled = true;
                        stickyActionBtn.classList.remove('btn-primary');
                        stickyActionBtn.classList.add('btn-secondary');
                        if (stickyBtnText) stickyBtnText.textContent = 'Habis';
                    } else {
                        stickyActionBtn.disabled = false;
                        stickyActionBtn.classList.remove('btn-secondary');
                        stickyActionBtn.classList.add('btn-primary');
                        if (stickyBtnText) stickyBtnText.textContent = 'Tambah';
                    }
                }
            });
        });

        // Otomatis pilih varian pertama yang masih ready stock saat halaman dimuat
        const firstAvailableVariant = Array.from(variantButtons).find(btn => parseInt(btn.getAttribute('data-variant-stock')) > 0) || variantButtons[0];
        if (firstAvailableVariant) {
            firstAvailableVariant.click();
        }
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

            const stickyActionBtn = document.getElementById('mobileStickyActionBtn');
            if (stickyActionBtn) {
                stickyActionBtn.disabled = true;
                stickyActionBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            const restoreStickyBtn = () => {
                if (stickyActionBtn) {
                    const isOutOfStock = (maxQty <= 0);
                    stickyActionBtn.disabled = isOutOfStock;
                    stickyActionBtn.className = 'btn rounded-pill px-3.5 py-2 small fw-bold shadow-sm d-flex align-items-center gap-1.5 ' + (isOutOfStock ? 'btn-secondary' : 'btn-primary');
                    stickyActionBtn.innerHTML = isOutOfStock 
                        ? '<i class="fas fa-ban"></i> <span id="mobileStickyBtnText">Habis</span>'
                        : '<i class="fas fa-shopping-bag"></i> <span id="mobileStickyBtnText">Tambah</span>';
                }
            };

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
                    restoreStickyBtn();
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                if (data.success) {
                    if (data.cartCount) updateCartCount(data.cartCount);
                    
                    // Notifikasi non-blocking toast
                    if (typeof showToast === 'function') {
                        showToast('✓ Berhasil ditambahkan ke keranjang!', 'success', 2500);
                    }

                    // 1. Jalankan animasi terbang (Fly-to-Cart) dari foto produk ke ikon keranjang navbar
                    const pImage = document.getElementById('productImage');
                    if (typeof animateFlyToCart === 'function' && pImage) {
                        animateFlyToCart(pImage);
                    }

                    // 2. Buka laci mini cart (Offcanvas Cart) secara halus
                    setTimeout(() => {
                        if (typeof loadOffcanvasCart === 'function') {
                            loadOffcanvasCart();
                            const offcanvasElem = document.getElementById('offcanvasCart');
                            if (offcanvasElem && typeof bootstrap !== 'undefined') {
                                const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElem);
                                bsOffcanvas.show();
                            }
                        }
                    }, 400);

                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                        restoreStickyBtn();
                    }, 1000);
                } else {
                    showToast(data.message || 'Gagal menambahkan ke keranjang', 'error');
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                    restoreStickyBtn();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                this.innerHTML = originalHTML;
                this.disabled = false;
                restoreStickyBtn();
            });
        });
    }

    // Mobile Sticky Action Button Click Handler
    const mobileStickyActionBtn = document.getElementById('mobileStickyActionBtn');
    if (mobileStickyActionBtn) {
        mobileStickyActionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const hasVariants = variantButtons.length > 0;
            const selectedVariantId = selectedVariantInput ? selectedVariantInput.value : null;

            if (hasVariants && !selectedVariantId) {
                const variantSelector = document.getElementById('variantSelector');
                if (variantSelector) {
                    variantSelector.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    variantSelector.classList.add('pulse-highlight');
                    setTimeout(() => variantSelector.classList.remove('pulse-highlight'), 1600);
                }
                if (typeof showToast === 'function') {
                    showToast('Silakan pilih varian produk terlebih dahulu.', 'warning', 2500);
                }
                return;
            }

            if (addToCartBtn) {
                if (addToCartBtn.disabled) {
                    if (typeof showToast === 'function') {
                        showToast('Varian yang dipilih sedang habis stok.', 'error', 2500);
                    }
                    return;
                }
                addToCartBtn.click();
            }
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

    // High-performance IntersectionObserver for Mobile Sticky Bar
    const mobileStickyBar = document.getElementById('mobileStickyBar');
    const mainActionBtn = document.getElementById('addToCartBtn');
    if (mobileStickyBar && mainActionBtn) {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    // Show when primary CTA has scrolled out of view above viewport
                    if (!entry.isIntersecting && entry.boundingClientRect.top < 0) {
                        mobileStickyBar.classList.add('visible');
                    } else {
                        mobileStickyBar.classList.remove('visible');
                    }
                });
            }, { threshold: 0.1 });
            observer.observe(mainActionBtn);
        } else {
            // Passive scroll listener fallback
            window.addEventListener('scroll', function() {
                const rect = mainActionBtn.getBoundingClientRect();
                if (rect.bottom < 0) {
                    mobileStickyBar.classList.add('visible');
                } else {
                    mobileStickyBar.classList.remove('visible');
                }
            }, { passive: true });
        }
    }
});

function copyProductLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
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
                title: 'Link produk berhasil disalin!'
            });
        } else {
            alert('Link produk berhasil disalin!');
        }
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

{{-- MOBILE STICKY BOTTOM ACTION BAR --}}
@if($product->total_stok > 0)
<div id="mobileStickyBar" class="d-md-none position-fixed bottom-0 start-0 w-100 mobile-sticky-action-bar" 
     style="z-index: 1040;">
    <div class="container-fluid px-3 py-2 d-flex align-items-center justify-content-between gap-3">
        {{-- Product Info (Thumbnail + Dynamic Price + Variant status) --}}
        <div class="d-flex align-items-center gap-2.5 min-w-0">
            <div class="rounded-3 overflow-hidden flex-shrink-0 border" style="width: 42px; height: 42px; background: rgba(0,0,0,0.02);">
                <img id="mobileStickyImg" src="{{ $product->gambar_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->nama_barang }}">
            </div>
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="fw-bold text-primary small lh-1" id="mobileStickyPrice" style="font-size: 0.98rem; letter-spacing: -0.01em;">
                        {{ $product->formatted_price }}
                    </span>
                </div>
                <div class="small text-truncate mt-0.5" style="font-size: 0.74rem; max-width: 145px;">
                    <span id="mobileStickyVariantLabel" class="text-secondary fw-medium">
                        @if($product->variants->isNotEmpty())
                            Pilih Varian
                        @else
                            Stok: {{ $product->stok_aktual }} unit
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Action CTA Button --}}
        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
            <button type="button" id="mobileStickyActionBtn" class="btn btn-primary rounded-pill px-3.5 py-2 small fw-bold shadow-sm d-flex align-items-center gap-1.5">
                <i class="fas fa-shopping-bag"></i>
                <span id="mobileStickyBtnText">Tambah</span>
            </button>
        </div>
    </div>
</div>
@endif
@endpush
@endsection
