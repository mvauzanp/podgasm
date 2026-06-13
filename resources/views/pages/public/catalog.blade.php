@extends('layouts.frontend')

@section('content')
<div class="container">
    {{-- Breadcrumb (Navigasi Kecil) --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->nama_kategori }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar Kategori --}}
        <div class="col-lg-3 d-none d-lg-block">
            @include('components._category-sidebar')
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9 col-12">
            {{-- Judul & Filter Ringkas --}}
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h3 class="fw-bold m-0">Koleksi {{ $category->nama_kategori }}</h3>
                <span class="text-muted">{{ $products->total() }} Produk ditemukan</span>
            </div>

            {{-- Grid Produk --}}
            <div class="row g-3">
                @forelse($products as $pro)
                <div class="col-6 col-md-4 col-lg-3" style="cursor: pointer;" onclick="window.location.href='{{ route('public.product.show', $pro->slug) }}'">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden" style="transition: all 0.3s ease;">
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
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold text-dark">{{ $pro->nama_barang }}</h6>
                            @php
                                $isB2B = auth()->check() && auth()->user()->role === 'branch';
                                $b2bPrice = $isB2B ? $pro->getB2bPrice(1) : null;
                            @endphp
                            @if($isB2B && $b2bPrice < $pro->harga_jual)
                                <p class="text-primary fw-bold mb-2">
                                    <span class="text-muted text-decoration-line-through small me-1">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</span>
                                    Rp {{ number_format($b2bPrice, 0, ',', '.') }} <span class="badge bg-info text-white" style="font-size: 0.65rem;">B2B Price</span>
                                </p>
                            @else
                                <p class="text-primary fw-bold mb-2">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</p>
                            @endif
                            
                            <div class="mt-auto">
                                @if($pro->stok_aktual <= $pro->nilai_ss)
                                    <span class="badge bg-danger w-100 py-2">Stok Habis / Kritis</span>
                                @else
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Tersedia: {{ $pro->stok_aktual }}</small>
                                        <div class="d-flex gap-1">
                                            {{-- Wishlist Button --}}
                                            <button type="button" 
                                               class="btn btn-light btn-sm rounded-circle shadow-sm text-danger wishlist-btn"
                                               data-product-id="{{ $pro->id }}"
                                               onclick="event.stopPropagation();"
                                               style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="far fa-heart"></i>
                                            </button>
                                             @if($pro->hasVariants())
                                                 <a href="{{ route('public.product.show', $pro->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="event.stopPropagation();">
                                                     Pilih Varian
                                                 </a>
                                             @else
                                                 <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#modalTambah{{ $pro->id }}">
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

                {{-- Modal Beli --}}
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
                                    @if($pro->gambar)
                                        <img src="{{ asset('storage/' . $pro->gambar) }}" class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    @endif
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
                    <div class="py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada produk untuk kategori {{ $category->nama_kategori }}.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Beranda</a>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Navigasi Halaman (Pagination) --}}
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const modalId = this.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            const quantityInput = modal.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value);
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
                body: JSON.stringify({ quantity: quantity })
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
                    
                    // Close modal after 1 second
                    setTimeout(() => {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }, 1000);
                    
                    // Reset button
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                        quantityInput.value = 1;
                    }, 1200);
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
@endsection