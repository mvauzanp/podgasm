<div class="d-flex flex-column h-100 w-100 overflow-hidden offcanvas-content-wrapper">
    @if(isset($isGuest) && $isGuest)
        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 p-4 text-center">
            <div class="icon-circle bg-light text-muted rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 76px; height: 76px;">
                <i class="fas fa-user-lock fa-2x opacity-40 text-primary"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1 offcanvas-text-title">Masuk ke Akun Anda</h6>
            <p class="text-muted small mb-4">Silakan login untuk melihat keranjang belanja dan melanjutkan checkout.</p>
            <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 py-2.5 small fw-semibold shadow-sm">
                <i class="fas fa-sign-in-alt me-1.5"></i> Masuk Sekarang
            </a>
        </div>
    @elseif($items->isEmpty())
        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 p-4 text-center">
            <div class="icon-circle bg-light text-muted rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 76px; height: 76px;">
                <i class="fas fa-shopping-bag fa-2x opacity-30"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1 offcanvas-text-title">Keranjang Masih Kosong</h6>
            <p class="text-muted small mb-4">Temukan perangkat vape & liquid favorit Anda sekarang.</p>
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2 small fw-semibold shadow-sm" data-bs-dismiss="offcanvas">
                Mulai Belanja
            </a>
        </div>
    @else
        {{-- 📜 SCROLLABLE ITEM LIST --}}
        <div class="flex-grow-1 overflow-y-auto p-2.5 p-sm-3 custom-cart-scroll" style="min-height: 0;">
            @foreach($items as $item)
                <div class="card border-0 rounded-4 mb-2.5 p-3 bg-white shadow-sm offcanvas-item-card" id="offcanvas-item-{{ $item->id }}">
                    <div class="d-flex gap-2.5 align-items-start">
                        {{-- Product Thumbnail --}}
                        <div class="product-img-wrapper rounded-3 overflow-hidden bg-light flex-shrink-0 border" style="width: 58px; height: 58px;">
                            <img src="{{ $item->product->gambar_url }}" 
                                 class="w-100 h-100 object-fit-cover" 
                                 alt="{{ $item->product->nama_barang }}"
                                 onerror="this.src='https://placehold.co/100x100?text=Podgasm'">
                        </div>

                        {{-- Details --}}
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-1">
                                <h6 class="fw-semibold text-dark mb-0 small text-truncate-2 offcanvas-text-title" style="font-size: 0.84rem; line-height: 1.3;" title="{{ $item->product->nama_barang }}">
                                    {{ $item->product->nama_barang }}
                                </h6>
                                <button type="button" class="btn btn-link text-danger p-0 ms-1 flex-shrink-0" onclick="deleteOffcanvasItem('{{ $item->id }}')" title="Hapus dari keranjang">
                                    <i class="far fa-trash-alt" style="font-size: 0.85rem;"></i>
                                </button>
                            </div>

                            @if($item->variant)
                                <div class="mt-0.5">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.2" style="font-size: 0.65rem;">
                                        {{ $item->variant->nama_varian }}
                                    </span>
                                </div>
                            @endif

                            {{-- Unit Price & Discount --}}
                            <div class="d-flex align-items-baseline gap-1 mt-1 flex-wrap">
                                <span class="fw-bold text-primary small" style="font-size: 0.82rem;">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </span>
                                @if($item->hasDiscount())
                                    <del class="text-muted small" style="font-size: 0.68rem;">
                                        Rp {{ number_format($item->getOriginalPrice(), 0, ',', '.') }}
                                    </del>
                                    <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 0.6rem;">
                                        -{{ $item->getDiscountPercentage() }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Row: Quantity Controls & Subtotal --}}
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top border-light-subtle">
                        {{-- Quantity Control Pill --}}
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border bg-light-subtle" style="width: 88px;">
                            <button class="btn btn-sm btn-light border-0 px-2 text-dark d-flex align-items-center justify-content-center" type="button" 
                                    onclick="changeOffcanvasQty('{{ $item->id }}', {{ $item->quantity - 1 }})" 
                                    {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                <i class="fas fa-minus" style="font-size: 0.6rem;"></i>
                            </button>
                            <span class="form-control form-control-sm text-center border-0 bg-transparent fw-bold p-0 small d-flex align-items-center justify-content-center" style="font-size: 0.82rem;">
                                {{ $item->quantity }}
                            </span>
                            @php
                                $maxStock = $item->product_variant_id ? ($item->variant->stok_aktual ?? 1000) : ($item->product->stok_aktual ?? 1000);
                            @endphp
                            <button class="btn btn-sm btn-light border-0 px-2 text-dark d-flex align-items-center justify-content-center" type="button" 
                                    onclick="changeOffcanvasQty('{{ $item->id }}', {{ $item->quantity + 1 }})" 
                                    {{ $item->quantity >= $maxStock ? 'disabled' : '' }}>
                                <i class="fas fa-plus" style="font-size: 0.6rem;"></i>
                            </button>
                        </div>

                        {{-- Item Subtotal --}}
                        <div class="text-end">
                            <span class="fw-bold text-dark small offcanvas-text-title" style="font-size: 0.86rem;">
                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- 🔒 PINNED / FIXED BOTTOM FOOTER --}}
        <div class="p-3 p-sm-3.5 mt-auto flex-shrink-0 offcanvas-bottom-footer bg-white shadow-lg" style="position: sticky; bottom: 0; z-index: 1050;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-medium">Subtotal Belanja</span>
                <span class="text-dark fw-bold fs-5 offcanvas-text-title">Rp {{ number_format($cart->total_price ?? 0, 0, ',', '.') }}</span>
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 rounded-pill py-2.5 small fw-semibold text-truncate">
                        Lihat Keranjang
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary w-100 rounded-pill py-2.5 small fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-1 text-truncate">
                        <span>Checkout</span>
                        <i class="fas fa-arrow-right fa-2xs"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Dark Mode Offcanvas Overrides */
    body.dark-mode .offcanvas-content-wrapper,
    html[data-bs-theme="dark"] .offcanvas-content-wrapper {
        background-color: #0b1324 !important;
    }

    body.dark-mode .offcanvas-bottom-footer,
    html[data-bs-theme="dark"] .offcanvas-bottom-footer {
        background-color: #0f172a !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .offcanvas-item-card,
    html[data-bs-theme="dark"] .offcanvas-item-card {
        background-color: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .offcanvas-item-card .border-light-subtle,
    html[data-bs-theme="dark"] .offcanvas-item-card .border-light-subtle {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    body.dark-mode .offcanvas-text-title,
    html[data-bs-theme="dark"] .offcanvas-text-title {
        color: #f8fafc !important;
    }

    body.dark-mode .offcanvas-item-card .btn-light,
    html[data-bs-theme="dark"] .offcanvas-item-card .btn-light {
        background-color: #0f172a !important;
        color: #f8fafc !important;
    }

    body.dark-mode .offcanvas-item-card .input-group,
    html[data-bs-theme="dark"] .offcanvas-item-card .input-group {
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
</style>

<script>
var isOffcanvasCartBusy = false;

function changeOffcanvasQty(itemId, newQty) {
    if (newQty < 1 || isOffcanvasCartBusy) return;
    isOffcanvasCartBusy = true;

    const itemCard = document.getElementById('offcanvas-item-' + itemId);
    if (itemCard) itemCard.style.opacity = '0.5';
    
    fetch("{{ route('cart.update') }}", {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            id: itemId,
            quantity: newQty
        })
    })
    .then(res => res.json())
    .then(data => {
        isOffcanvasCartBusy = false;
        if (data.success) {
            loadOffcanvasCart();
            if (typeof updateCartCount === 'function' && data.cartCount !== undefined) {
                updateCartCount(data.cartCount);
            }
        } else {
            if (itemCard) itemCard.style.opacity = '1';
            if (typeof showToast === 'function') {
                showToast(data.message || 'Gagal mengubah jumlah', 'error');
            } else {
                alert(data.message || 'Gagal mengubah jumlah');
            }
        }
    })
    .catch(err => {
        isOffcanvasCartBusy = false;
        if (itemCard) itemCard.style.opacity = '1';
        console.error('Offcanvas cart update error:', err);
    });
}

function deleteOffcanvasItem(itemId) {
    if (isOffcanvasCartBusy) return;
    isOffcanvasCartBusy = true;

    const itemCard = document.getElementById('offcanvas-item-' + itemId);
    if (itemCard) itemCard.style.opacity = '0.4';

    fetch("{{ route('cart.remove') }}", {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: itemId })
    })
    .then(res => res.json())
    .then(data => {
        isOffcanvasCartBusy = false;
        if (data.success) {
            loadOffcanvasCart();
            if (typeof updateCartCount === 'function' && data.cartCount !== undefined) {
                updateCartCount(data.cartCount);
            }
        } else {
            loadOffcanvasCart();
        }
    })
    .catch(err => {
        isOffcanvasCartBusy = false;
        loadOffcanvasCart();
    });
}
</script>
