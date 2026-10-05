@extends('layouts.frontend')

@section('content')
<div class="container pt-3 pt-md-4 pb-5 mb-5 mb-md-0">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark page-title">Keranjang Belanja</h3>
            <p class="text-muted small mb-0">{{ count($items ?? []) }} produk siap untuk diproses</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-none d-sm-inline-flex align-items-center gap-1">
            <i class="fas fa-arrow-left fa-2xs"></i>
            <span>Lanjut Belanja</span>
        </a>
    </div>

    @if($items && count($items) > 0)
    {{-- PRICE CHANGE WARNING --}}
    @if(!empty($priceChanges))
    <div class="alert alert-warning alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-exclamation-triangle mt-1"></i>
            <div>
                <strong>Perhatian!</strong> Ada perubahan harga pada produk di keranjang Anda:
                <ul class="mb-0 mt-1 ps-3 small">
                    @foreach($priceChanges as $change)
                    <li>{{ $change['product'] }}: Rp {{ number_format($change['old_price'], 0, ',', '.') }} → Rp {{ number_format($change['new_price'], 0, ',', '.') }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- 🔥 LEFT: CART ITEMS --}}
        <div class="col-lg-8">
            
            {{-- 📱 MOBILE VIEW: CARD-BASED LAYOUT (d-md-none) --}}
            <div class="d-md-none d-flex flex-column gap-3 mb-4">
                @foreach($items as $item)
                <div class="card border-0 shadow-sm rounded-4 p-3 cart-item-card" id="cart-card-mobile-{{ $item->id }}">
                    <div class="d-flex gap-3 align-items-start">
                        {{-- Product Image --}}
                        <a href="{{ route('public.product.show', $item->product->slug) }}" class="flex-shrink-0">
                            <img src="{{ $item->product->gambar_url }}"
                                 alt="{{ $item->product->nama_barang }}"
                                 class="rounded-3 object-fit-cover shadow-2xs border"
                                 style="width: 76px; height: 76px;"
                                 onerror="this.src='https://placehold.co/100x100?text=Podgasm'">
                        </a>

                        {{-- Product Details --}}
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ route('public.product.show', $item->product->slug) }}" class="text-decoration-none text-dark">
                                    <h6 class="fw-bold mb-1 text-truncate-2 cart-product-title" style="font-size: 0.92rem; line-height: 1.35;">
                                        {{ $item->product->nama_barang }}
                                    </h6>
                                </a>
                                {{-- Delete Button --}}
                                <form action="{{ route('cart.remove') }}" method="POST"
                                      onsubmit="return confirmDeleteCart(event, this, '{{ addslashes($item->product->nama_barang) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-link text-danger p-0 border-0 ms-1 flex-shrink-0" title="Hapus dari keranjang">
                                        <i class="far fa-trash-alt fs-6"></i>
                                    </button>
                                </form>
                            </div>

                            @if($item->variant)
                            <div class="mb-1">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                    Varian: {{ $item->variant->nama_varian }}
                                </span>
                            </div>
                            @endif

                            @if($item->isPriceChanged())
                            <div class="mb-1">
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    <i class="fas fa-exclamation-circle me-1"></i> Harga berubah
                                </span>
                            </div>
                            @endif

                            {{-- Price & Tier Discount --}}
                            <div class="d-flex align-items-baseline gap-1 mt-1 flex-wrap">
                                <span class="fw-bold text-dark fs-6" id="unit-price-mobile-{{ $item->id }}">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </span>
                                @if($item->hasDiscount())
                                    <del class="text-muted small" style="font-size: 0.75rem;" id="original-price-mobile-{{ $item->id }}">
                                        Rp {{ number_format($item->getOriginalPrice(), 0, ',', '.') }}
                                    </del>
                                    <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 0.65rem;" id="discount-badge-mobile-{{ $item->id }}">
                                        -{{ $item->getDiscountPercentage() }}%
                                    </span>
                                @else
                                    <div class="d-none" id="discount-badge-container-mobile-{{ $item->id }}">
                                        <del class="text-muted small" style="font-size: 0.75rem;" id="original-price-mobile-{{ $item->id }}">
                                            Rp {{ number_format($item->getOriginalPrice(), 0, ',', '.') }}
                                        </del>
                                        <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 0.65rem;" id="discount-badge-mobile-{{ $item->id }}">
                                            0%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Row: Subtotal & Quantity Controls --}}
                    <div class="d-flex justify-content-between align-items-center pt-2.5 mt-2.5 border-top border-light-subtle">
                        <div>
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Subtotal</span>
                            <span class="fw-bold text-primary" id="subtotal-item-mobile-{{ $item->id }}">
                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Quantity Pill Controls --}}
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-light-subtle" style="width: 110px;">
                            <button class="btn btn-sm btn-light border-0 px-2.5 btn-decrease text-dark" type="button" data-item-id="{{ $item->id }}">
                                <i class="fas fa-minus fa-2xs"></i>
                            </button>
                            <input type="number"
                                   id="qty-input-mobile-{{ $item->id }}"
                                   data-item-id="{{ $item->id }}"
                                   value="{{ $item->quantity }}"
                                   min="1"
                                   max="{{ $item->product_variant_id ? ($item->variant->stok_aktual ?? 1000) : ($item->product->stok_aktual ?? 1000) }}"
                                   class="form-control form-control-sm text-center fw-bold border-0 bg-transparent cart-qty-input p-0"
                                   oninput="let max = parseInt(this.max); if(this.value && parseInt(this.value) > max) this.value = max; if(this.value && parseInt(this.value) < 1) this.value = 1;">
                            <button class="btn btn-sm btn-light border-0 px-2.5 btn-increase text-dark" type="button" data-item-id="{{ $item->id }}">
                                <i class="fas fa-plus fa-2xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- 💻 DESKTOP & TABLET VIEW: TABLE LAYOUT (d-none d-md-block) --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-none d-md-block">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="ps-4 py-3">Produk</th>
                                <th class="py-3">Harga Satuan</th>
                                <th class="text-center py-3">Jumlah</th>
                                <th class="py-3">Subtotal</th>
                                <th class="text-center pe-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr id="cart-row-desktop-{{ $item->id }}">
                                {{-- Produk --}}
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('public.product.show', $item->product->slug) }}">
                                            <img src="{{ $item->product->gambar_url }}"
                                                width="64" height="64"
                                                class="rounded-3 object-fit-cover me-3 shadow-2xs border"
                                                onerror="this.src='https://placehold.co/100x100?text=Podgasm'">
                                        </a>
                                        <div>
                                            <a href="{{ route('public.product.show', $item->product->slug) }}" class="text-decoration-none text-dark">
                                                <span class="fw-bold d-block mb-1">{{ $item->product->nama_barang }}</span>
                                            </a>
                                            @if($item->variant)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 small mb-1" style="font-size: 0.72rem;">
                                                Varian: {{ $item->variant->nama_varian }}
                                            </span>
                                            @endif
                                            @if($item->isPriceChanged())
                                            <span class="d-block small text-warning">
                                                <i class="fas fa-exclamation-circle"></i> Harga berubah
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Unit Price --}}
                                <td class="small py-3" id="unit-price-cell-{{ $item->id }}">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark fs-6" id="unit-price-{{ $item->id }}">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </span>
                                        @if($item->hasDiscount())
                                            <div class="d-flex align-items-center gap-1 mt-0.5" id="discount-badge-container-{{ $item->id }}">
                                                <del class="text-muted small" style="font-size: 0.75rem;" id="original-price-{{ $item->id }}">
                                                    Rp {{ number_format($item->getOriginalPrice(), 0, ',', '.') }}
                                                </del>
                                                <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 0.65rem;" id="discount-badge-{{ $item->id }}">
                                                    -{{ $item->getDiscountPercentage() }}%
                                                </span>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-1 mt-0.5 d-none" id="discount-badge-container-{{ $item->id }}">
                                                <del class="text-muted small" style="font-size: 0.75rem;" id="original-price-{{ $item->id }}">
                                                    Rp {{ number_format($item->getOriginalPrice(), 0, ',', '.') }}
                                                </del>
                                                <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 0.65rem;" id="discount-badge-{{ $item->id }}">
                                                    0%
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Quantity --}}
                                <td class="py-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-light-subtle" style="width: 110px;">
                                            <button class="btn btn-sm btn-light border-0 px-2 btn-decrease text-dark" type="button" data-item-id="{{ $item->id }}">
                                                <i class="fas fa-minus fa-2xs"></i>
                                            </button>
                                            <input type="number"
                                                   id="qty-input-{{ $item->id }}"
                                                   data-item-id="{{ $item->id }}"
                                                   value="{{ $item->quantity }}"
                                                   min="1"
                                                   max="{{ $item->product_variant_id ? ($item->variant->stok_aktual ?? 1000) : ($item->product->stok_aktual ?? 1000) }}"
                                                   class="form-control form-control-sm text-center fw-bold border-0 bg-transparent cart-qty-input p-0"
                                                   oninput="let max = parseInt(this.max); if(this.value && parseInt(this.value) > max) this.value = max; if(this.value && parseInt(this.value) < 1) this.value = 1;">
                                            <button class="btn btn-sm btn-light border-0 px-2 btn-increase text-dark" type="button" data-item-id="{{ $item->id }}">
                                                <i class="fas fa-plus fa-2xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                {{-- Subtotal --}}
                                <td class="fw-bold text-primary py-3" id="subtotal-item-{{ $item->id }}">
                                    Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                                </td>

                                {{-- Delete --}}
                                <td class="text-center pe-4 py-3">
                                    <form action="{{ route('cart.remove') }}" method="POST"
                                          onsubmit="return confirmDeleteCart(event, this, '{{ addslashes($item->product->nama_barang) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Hapus dari keranjang">
                                            <i class="far fa-trash-alt"></i>
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

        {{-- 🔥 RIGHT: SUMMARY CARD (Desktop & Tablet) --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top cart-summary-card" style="top: 90px;">
                <h5 class="fw-bold mb-4 text-dark">Ringkasan Pesanan</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Harga ({{ count($items) }} Produk)</span>
                    <span class="fw-semibold" id="summary-subtotal">Rp {{ number_format($cart->total_price, 0, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Ongkos Kirim</span>
                    <span class="text-muted small">Dihitung saat checkout</span>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark fs-6">Total Belanja</span>
                    <h5 class="text-primary fw-bold mb-0" id="summary-total">
                        Rp {{ number_format($cart->total_price, 0, ',', '.') }}
                    </h5>
                </div>

                @php
                    $isB2B = auth()->check() && auth()->user()->role === 'branch';
                @endphp
                @if($isB2B)
                    <a href="{{ route('cart.checkout') }}" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <span>Lanjut ke Request Barang</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <span>Lanjut ke Checkout</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @endif

                <div class="text-center mt-3">
                    <a href="{{ route('home') }}" class="small text-muted text-decoration-none d-inline-flex align-items-center gap-1">
                        <i class="fas fa-arrow-left fa-2xs"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- 📱 STICKY BOTTOM CHECKOUT BAR FOR MOBILE (d-md-none) --}}
    <div class="fixed-bottom d-md-none bg-white border-top shadow-lg p-3 mobile-bottom-cart-bar" style="z-index: 1040; backdrop-filter: blur(16px);">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            <div>
                <span class="text-muted small d-block" style="font-size: 0.72rem;">Total Tagihan</span>
                <h5 class="text-primary fw-bold mb-0" id="mobile-summary-total">
                    Rp {{ number_format($cart->total_price, 0, ',', '.') }}
                </h5>
            </div>
            @if($isB2B ?? false)
                <a href="{{ route('cart.checkout') }}" class="btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <span>Checkout</span>
                    <i class="fas fa-arrow-right fa-xs"></i>
                </a>
            @else
                <a href="{{ route('cart.checkout') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <span>Checkout</span>
                    <i class="fas fa-arrow-right fa-xs"></i>
                </a>
            @endif
        </div>
    </div>

    @else
    {{-- 🔥 EMPTY STATE --}}
    <div class="text-center py-5 my-4">
        <div class="icon-circle bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 88px; height: 88px;">
            <i class="fas fa-shopping-bag fa-3x opacity-25"></i>
        </div>
        <h4 class="fw-bold mb-1">Keranjang Belanja Kosong</h4>
        <p class="text-muted small mb-4">Yuk temukan pod, liquid, dan perlengkapan vape favorit kamu sekarang.</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2.5 rounded-pill fw-semibold shadow-sm">
            <i class="fas fa-store me-1.5"></i> Mulai Belanja
        </a>
    </div>
    @endif
</div>

<style>
    /* Mobile Cart Enhancements */
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* Dark Theme Support */
    body.dark-mode .cart-item-card,
    html[data-bs-theme="dark"] .cart-item-card,
    body.dark-mode .cart-summary-card,
    html[data-bs-theme="dark"] .cart-summary-card {
        background-color: #111928 !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    
    body.dark-mode .mobile-bottom-cart-bar,
    html[data-bs-theme="dark"] .mobile-bottom-cart-bar {
        background-color: rgba(17, 25, 40, 0.95) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode .cart-item-card .border-light-subtle,
    html[data-bs-theme="dark"] .cart-item-card .border-light-subtle {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .cart-product-title,
    html[data-bs-theme="dark"] .cart-product-title,
    body.dark-mode .page-title,
    html[data-bs-theme="dark"] .page-title {
        color: #f8fafc !important;
    }

    body.dark-mode .input-group .btn-light,
    html[data-bs-theme="dark"] .input-group .btn-light {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }
    body.dark-mode .input-group,
    html[data-bs-theme="dark"] .input-group {
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function updateQuantityAjax(itemId, newQty) {
        const desktopInput = document.getElementById('qty-input-' + itemId);
        const mobileInput = document.getElementById('qty-input-mobile-' + itemId);
        
        const targetInput = desktopInput || mobileInput;
        if (!targetInput) return;

        const maxQty = parseInt(targetInput.getAttribute('max')) || 1000;
        if (newQty < 1) newQty = 1;
        if (newQty > maxQty) newQty = maxQty;

        if (desktopInput) {
            desktopInput.value = newQty;
            desktopInput.disabled = true;
        }
        if (mobileInput) {
            mobileInput.value = newQty;
            mobileInput.disabled = true;
        }

        fetch("{{ route('cart.update') }}", {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: itemId,
                quantity: newQty
            })
        })
        .then(response => response.json())
        .then(data => {
            if (desktopInput) desktopInput.disabled = false;
            if (mobileInput) mobileInput.disabled = false;

            if (data.success) {
                // Update unit price
                ['unit-price-' + itemId, 'unit-price-mobile-' + itemId].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && data.unitPriceFormatted) el.textContent = data.unitPriceFormatted;
                });

                // Update original price
                ['original-price-' + itemId, 'original-price-mobile-' + itemId].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && data.originalPriceFormatted) el.textContent = data.originalPriceFormatted;
                });

                // Update discount badge
                ['discount-badge-' + itemId, 'discount-badge-mobile-' + itemId].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && data.discountPercent > 0) el.textContent = '-' + data.discountPercent + '%';
                });

                ['discount-badge-container-' + itemId, 'discount-badge-container-mobile-' + itemId].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (data.hasDiscount && data.discountPercent > 0) {
                            el.classList.remove('d-none');
                        } else {
                            el.classList.add('d-none');
                        }
                    }
                });

                // Update subtotal item
                ['subtotal-item-' + itemId, 'subtotal-item-mobile-' + itemId].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && data.itemSubtotalFormatted) el.textContent = data.itemSubtotalFormatted;
                });

                // Update total ringkasan (Desktop + Mobile)
                const summarySubtotal = document.getElementById('summary-subtotal');
                const summaryTotal = document.getElementById('summary-total');
                const mobileSummaryTotal = document.getElementById('mobile-summary-total');

                if (summarySubtotal && data.cartTotalFormatted) summarySubtotal.textContent = data.cartTotalFormatted;
                if (summaryTotal && data.cartTotalFormatted) summaryTotal.textContent = data.cartTotalFormatted;
                if (mobileSummaryTotal && data.cartTotalFormatted) mobileSummaryTotal.textContent = data.cartTotalFormatted;

                // Update badge cart di navbar
                if (typeof updateCartCount === 'function' && data.cartCount !== undefined) {
                    updateCartCount(data.cartCount);
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Gagal memperbarui jumlah produk.', 'error');
                } else {
                    alert(data.message || 'Gagal memperbarui jumlah produk.');
                }
            }
        })
        .catch(err => {
            if (desktopInput) desktopInput.disabled = false;
            if (mobileInput) mobileInput.disabled = false;
            console.error('Cart update error:', err);
        });
    }

    // Event listener tombol minus
    document.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            const input = document.getElementById('qty-input-' + itemId) || document.getElementById('qty-input-mobile-' + itemId);
            if (input) {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal > 1) {
                    updateQuantityAjax(itemId, currentVal - 1);
                }
            }
        });
    });

    // Event listener tombol plus
    document.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            const input = document.getElementById('qty-input-' + itemId) || document.getElementById('qty-input-mobile-' + itemId);
            if (input) {
                let currentVal = parseInt(input.value) || 1;
                let maxQty = parseInt(input.getAttribute('max')) || 1000;
                if (currentVal < maxQty) {
                    updateQuantityAjax(itemId, currentVal + 1);
                }
            }
        });
    });

    // Event listener perubahan input langsung dengan debounce
    let timeoutId;
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', function () {
            const itemId = this.getAttribute('data-item-id');
            const val = parseInt(this.value) || 1;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                updateQuantityAjax(itemId, val);
            }, 300);
        });
    });
});

function confirmDeleteCart(event, form, productName) {
    if (typeof Swal !== 'undefined') {
        event.preventDefault();
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.body.classList.contains('dark-mode');
        Swal.fire({
            title: 'Hapus Produk?',
            text: 'Apakah Anda yakin ingin menghapus "' + productName + '" dari keranjang?',
            icon: 'warning',
            background: isDark ? '#182234' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }
    return confirm('Hapus produk ini?');
}
</script>
@endpush
@endsection