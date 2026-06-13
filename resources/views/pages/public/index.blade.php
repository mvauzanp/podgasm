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

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0 text-dark">Katalog Podgasm</h4>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        Urutkan: Terbaru
                    </button>
                    <ul class="dropdown-menu border-0 shadow">
                        <li><a class="dropdown-item" href="?sort=newest">Terbaru</a></li>
                        <li><a class="dropdown-item" href="?sort=price_low">Harga Terendah</a></li>
                        <li><a class="dropdown-item" href="?sort=price_high">Harga Tertinggi</a></li>
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