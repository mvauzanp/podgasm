@php
    $totalStock = $pro->total_stok;
    $isOutOfStock = $totalStock <= 0;
    $isLimitedStock = !$isOutOfStock && ($totalStock <= ($pro->nilai_ss ?? 5));
@endphp

<div class="surfeit-product-wrapper position-relative h-100 d-flex flex-column">
    {{-- KOTAK GAMBAR PRODUK --}}
    <div class="surfeit-image-box position-relative d-flex align-items-center justify-content-center overflow-hidden w-100" 
         style="background-color: #f4f4f4; aspect-ratio: 1 / 1; padding: 14px; border-radius: 12px; transition: all 0.3s ease;">
        
        {{-- Status Badges --}}
        <div class="position-absolute top-0 start-0 m-2 z-2 d-flex flex-column gap-1">
            @if($isOutOfStock)
                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 shadow-sm fw-bold" style="font-size: 0.68rem;">
                    <i class="fas fa-times-circle me-1"></i> Stok Habis
                </span>
            @elseif($isLimitedStock)
                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 shadow-sm fw-bold" style="font-size: 0.68rem;">
                    <i class="fas fa-fire me-1 text-danger"></i> Sisa {{ $totalStock }} unit
                </span>
            @endif

            @if($pro->is_promo && $pro->diskon_persen > 0)
                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 shadow-sm fw-bold" style="font-size: 0.68rem;">
                    -{{ $pro->diskon_persen }}%
                </span>
            @endif
        </div>

        {{-- Product Image --}}
        @if($pro->gambar)
            <img src="{{ $pro->gambar_url }}" 
                 alt="{{ $pro->nama_barang }}"
                 class="surfeit-floating-img img-fluid"
                 style="max-height: 85%; max-width: 85%; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.08)); transition: transform 0.4s ease;"
                 onerror="this.onerror=null; this.src='https://placehold.co/400x400/f8fafc/0f172a?text=Podgasm+Product';">
        @else
            <div class="text-center text-muted">
                <i class="fas fa-box fa-2x opacity-25"></i>
                <span class="d-block small text-muted mt-1" style="font-size: 0.75rem;">No Image</span>
            </div>
        @endif

        {{-- Wishlist Button (Z-index high to prevent stretched link interception) --}}
        <button type="button" 
                class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 shadow-sm border-0 wishlist-btn z-2"
                data-product-id="{{ $pro->id }}"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
            <i class="far fa-heart text-danger small"></i>
        </button>
    </div>

    {{-- INFORMASI PRODUK --}}
    <div class="surfeit-details-box mt-2.5 d-flex flex-column flex-grow-1">
        {{-- Nama Produk (Dengan Stretched Link Semantic) --}}
        <h6 class="fw-bold text-dark mb-1 text-truncate product-card-title" style="font-size: 0.95rem; line-height: 1.3;" title="{{ $pro->nama_barang }}">
            <a href="{{ route('public.product.show', $pro->slug) }}" class="text-dark text-decoration-none stretched-link">
                {{ $pro->nama_barang }}
            </a>
        </h6>

        {{-- Sub-judul / Kategori --}}
        <div class="text-secondary small mb-1 product-card-cat" style="font-size: 0.82rem;">
            {{ $pro->category ? $pro->category->nama_kategori : 'Podgasm Store' }}
        </div>

        {{-- Format Harga --}}
        <div class="mt-auto pt-1 fw-bold price-text" style="font-size: 0.95rem;">
            <span class="text-dark product-card-price">{{ $pro->formatted_price }}</span>
            @if($pro->is_promo && $pro->diskon_persen > 0)
                <span class="text-decoration-line-through text-muted small ms-1">Rp {{ number_format($pro->harga_jual, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>
</div>
