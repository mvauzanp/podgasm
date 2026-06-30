@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- MAIN CONTENT FULL --}}
        <div class="col-lg-12">

            {{-- Banner --}}
            <div class="row mb-4">
                <div class="col-12">
                    @include('components._promo-banner')
                </div>
            </div>

            {{-- Categories Circular Grid (Inspired by Vapehan) --}}
            <div class="row mb-4 mt-2">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-4 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-th-large" style="color: #09afb9;"></i> Jelajahi Kategori
                        </h5>
                        <div class="d-flex gap-4 overflow-x-auto pb-2 scrollbar-hidden justify-content-start justify-content-md-center">
                            @foreach($categories->where('parent_id', null) as $cat)
                                @php
                                    // Icon mapping based on categories
                                    $icon = 'fas fa-box';
                                    $bgGradient = 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)';
                                    $iconColor = '#0284c7';
                                    
                                    switch($cat->slug) {
                                        case 'liquid':
                                            $icon = 'fas fa-flask';
                                            $bgGradient = 'linear-gradient(135deg, rgba(9, 175, 185, 0.08) 0%, rgba(0, 228, 255, 0.08) 100%)';
                                            $iconColor = '#09afb9';
                                            break;
                                        case 'pod':
                                            $icon = 'fas fa-plug';
                                            $bgGradient = 'linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%)';
                                            $iconColor = '#ea580c';
                                            break;
                                        case 'mod':
                                            $icon = 'fas fa-cube';
                                            $bgGradient = 'linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%)';
                                            $iconColor = '#7c3aed';
                                            break;
                                        case 'atomizer':
                                            $icon = 'fas fa-atom';
                                            $bgGradient = 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)';
                                            $iconColor = '#e11d48';
                                            break;
                                        case 'accessories':
                                            $icon = 'fas fa-tools';
                                            $bgGradient = 'linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%)';
                                            $iconColor = '#0d9488';
                                            break;
                                        default:
                                            $icon = 'fas fa-box-open';
                                            $bgGradient = 'linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)';
                                            $iconColor = '#64748b';
                                            break;
                                    }
                                @endphp
                                <a href="{{ route('public.category', $cat->slug) }}" class="text-decoration-none text-center d-flex flex-column align-items-center group shrink-0" style="width: 100px; transition: all 0.3s ease;">
                                    <div class="category-circle d-flex align-items-center justify-content-center mb-2 shadow-sm" 
                                         style="width: 70px; height: 70px; border-radius: 50%; background: {{ $bgGradient }}; color: {{ $iconColor }}; transition: all 0.3s ease; font-size: 1.6rem;">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <span class="fw-bold text-dark text-uppercase tracking-wider" style="font-size: 0.72rem; transition: all 0.2s ease;">
                                        {{ $cat->nama_kategori }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .category-circle {
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                }
                .category-circle:hover {
                    transform: translateY(-5px) scale(1.05);
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
                }
                .scrollbar-hidden::-webkit-scrollbar {
                    display: none;
                }
                .scrollbar-hidden {
                    -ms-overflow-style: none;  /* IE and Edge */
                    scrollbar-width: none;  /* Firefox */
                }
                .group:hover span {
                    color: #09afb9 !important;
                }
            </style>

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0 text-dark">Katalog Podgasm</h4>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        Urutkan: {{ request('sort') === 'price_low' ? 'Harga Terendah' : (request('sort') === 'price_high' ? 'Harga Tertinggi' : 'Terbaru') }}
                    </button>
                    <ul class="dropdown-menu border-0 shadow">
                        <li><a class="dropdown-item {{ request('sort', 'newest') === 'newest' ? 'active' : '' }}" href="?sort=newest">Terbaru</a></li>
                        <li><a class="dropdown-item {{ request('sort') === 'price_low' ? 'active' : '' }}" href="?sort=price_low">Harga Terendah</a></li>
                        <li><a class="dropdown-item {{ request('sort') === 'price_high' ? 'active' : '' }}" href="?sort=price_high">Harga Tertinggi</a></li>
                    </ul>
                </div>
            </div>

            {{-- Produk --}}
            <div class="row g-3">
                @forelse($products as $pro)
                <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 50 }}" style="cursor: pointer;" onclick="window.location.href='{{ route('public.product.show', $pro->slug) }}'">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-product @if($pro->stok_aktual <= $pro->nilai_ss) border-danger-subtle @endif" style="transition: all 0.3s ease;">
                        
                        <div class="bg-light" style="height: 180px; overflow: hidden;">
                            @if($pro->gambar)
                                <img src="{{ asset('storage/' . $pro->gambar) }}" 
                                    class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-box fa-3x text-muted opacity-25"></i>
                                </div>
                            @endif
                        </div>

                        <div class="card-body p-3 d-flex flex-column">
                            <small class="text-uppercase text-muted fw-bold mb-1" style="font-size: 10px;">
                                {{ $pro->category->nama_kategori ?? 'Vape Item' }}
                            </small>

                            <h6 class="card-title fw-bold text-dark mb-1 text-truncate">
                                {{ $pro->nama_barang }}
                            </h6>

                            @php
                                $isB2B = auth()->check() && auth()->user()->role === 'branch';
                                $b2bPrice = $isB2B ? $pro->getB2bPrice(1) : null;
                            @endphp
                            @if($isB2B && $b2bPrice < $pro->harga_jual)
                                <p class="text-primary fw-bold mb-3">
                                    <span class="text-muted text-decoration-line-through small me-1">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</span>
                                    Rp {{ number_format($b2bPrice, 0, ',', '.') }} <span class="badge bg-info text-white" style="font-size: 0.65rem;">B2B Price</span>
                                </p>
                            @else
                                <p class="text-primary fw-bold mb-3">
                                    Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}
                                </p>
                            @endif

                            <div class="mt-auto">
                                @if($pro->stok_aktual <= $pro->nilai_ss)
                                    <div class="alert alert-danger py-1 px-2 mb-0 border-0 text-center rounded-pill">
                                        <small class="fw-bold" style="font-size: 11px;">Stok Kritis / Habis</small>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-check-circle text-success me-1"></i>Tersedia
                                        </small>

                                        <div class="d-flex gap-1">
                                            {{-- Wishlist Button --}}
                                            <button type="button" 
                                               class="btn btn-light btn-sm rounded-circle shadow-sm text-danger wishlist-btn"
                                               data-product-id="{{ $pro->id }}"
                                               onclick="event.stopPropagation();"
                                               style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="far fa-heart"></i>
                                            </button>

                                            {{-- Tombol Beli --}}
                                            @if($pro->hasVariants())
                                                <a href="{{ route('public.product.show', $pro->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="event.stopPropagation();">
                                                    Pilih Varian
                                                </a>
                                            @else
                                                <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#modalTambah{{ $pro->id }}">
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

                {{-- MODAL POPUP FORM --}}
                @if($pro->stok_aktual > $pro->nilai_ss)
                <div class="modal fade" id="modalTambah{{ $pro->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content border-0 shadow rounded-4">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="fw-bold mb-0">Atur Jumlah</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $pro->gambar) }}" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    <p class="mt-2 mb-0 fw-bold small text-truncate">{{ $pro->nama_barang }}</p>
                                    @if($isB2B && $b2bPrice < $pro->harga_jual)
                                        <p class="text-primary fw-bold small">
                                            <span class="text-muted text-decoration-line-through small me-1">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</span>
                                            Rp {{ number_format($b2bPrice, 0, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="text-primary fw-bold small">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                                
                                <div class="form-group">
                                    <label class="small text-muted mb-1">Berapa unit yang ingin dibeli?</label>
                                    <input type="number" class="form-control text-center fw-bold quantity-input" data-product-id="{{ $pro->id }}" value="1" min="1" max="{{ $pro->stok_aktual }}" required>
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Stok tersisa: {{ $pro->stok_aktual }}</small>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold btn-add-to-cart" data-product-id="{{ $pro->id }}" data-modal-id="modalTambah{{ $pro->id }}">
                                    <i class="fas fa-shopping-cart me-1"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Maaf, produk belum tersedia.</p>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// ✅ PERBAIKAN: Setup event listeners langsung tanpa delay
(function() {
    function setupAddToCartListeners() {
        const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
        
        if (addToCartButtons.length === 0) {
            console.warn('No add-to-cart buttons found');
            return;
        }
        
        addToCartButtons.forEach(button => {
            // Remove existing listeners if any
            const clone = button.cloneNode(true);
            button.parentNode.replaceChild(clone, button);
            
            // Attach new listener to the cloned button
            clone.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // ✅ PERBAIKAN #6.3: Check if user is authenticated
                const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
                
                if (!isAuthenticated) {
                    // User belum login, redirect ke login
                    showToast('Silakan login terlebih dahulu untuk menambahkan ke keranjang', 'info', 3000);
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 1500);
                    return false;
                }

                const btn = this;  // Use 'this' from regular function
                const productId = btn.getAttribute('data-product-id');
                const modalId = btn.getAttribute('data-modal-id');
                const modal = document.getElementById(modalId);
                const quantityInput = modal.querySelector('.quantity-input');
                const quantity = parseInt(quantityInput.value);
                const originalHTML = btn.innerHTML;
                
                // Add loading state
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-animation"></span>Menambahkan...';
                
                fetch("{{ url('/cart/add') }}/" + productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ quantity: quantity })
                })
                .then(response => {
                    // Handle 401 Unauthorized response (user session expired)
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
                    if (!data) return; // Handle 401 response
                    
                    if (data.success) {
                        // Show success toast
                        showToast('✓ Produk berhasil ditambahkan ke keranjang!', 'success', 4000);
                        
                        // Update cart count if available
                        if (data.cartCount) {
                            updateCartCount(data.cartCount);
                        }

                        // Buka Offcanvas Cart
                        if(typeof openOffcanvasCart === 'function') {
                            openOffcanvasCart();
                        }
                        
                        // Close modal after 1 second
                        setTimeout(() => {
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) {
                                bsModal.hide();
                            }
                        }, 1000);
                        
                        // Reset button
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                            quantityInput.value = 1;
                        }, 1200);
                    } else {
                        showToast(data.message || 'Gagal menambahkan ke keranjang', 'error');
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                });
                
                return false;
            });
        });
    }

    // Try setup immediately
    if (document.readyState !== 'loading') {
        setupAddToCartListeners();
    } else {
        document.addEventListener('DOMContentLoaded', setupAddToCartListeners);
    }
    
    // Also setup on page load to be sure
    window.addEventListener('load', setupAddToCartListeners);
})();
</script>
@endpush
@endsection