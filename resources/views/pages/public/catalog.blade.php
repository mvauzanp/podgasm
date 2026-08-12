@extends('layouts.frontend')

@section('content')
<div class="container-fluid px-lg-5 py-3">

    {{-- Breadcrumb Minimalis dengan Logo Podgasm --}}
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
            @if($category->parent)
                <li class="breadcrumb-item">
                    <a href="{{ url('/category/' . $category->parent->slug) }}" class="text-secondary text-decoration-none hover-primary">
                        {{ $category->parent->nama_kategori }}
                    </a>
                </li>
                <li class="breadcrumb-item text-muted">
                    <i class="fas fa-chevron-right mx-1 opacity-50" style="font-size: 0.65rem;"></i>
                </li>
            @endif
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $category->nama_kategori }}</li>
        </ol>
    </nav>

    {{-- 1. HERO CATEGORY SECTION (Kiri: Judul Raksasa, Kanan: Card Sub-Kategori Visual) --}}
    <div class="row align-items-end mb-5 g-4">
        {{-- Sisi Kiri: Judul Kategori Raksasa (Sesuai "Fiction" pada Gambar SURFEIT) --}}
        <div class="col-lg-4 col-md-5">
            <h1 class="surfeit-category-title text-dark fw-black mb-0">
                {{ $category->nama_kategori }}
            </h1>
        </div>

        {{-- Sisi Kanan: Kartu Visual Sub-Kategori (Classic, Erotic, Romance, Thriller, Fantasy) --}}
        <div class="col-lg-8 col-md-7">
            <div class="d-flex gap-3 overflow-x-auto pb-2 no-scrollbar justify-content-start category-drag-scroll">
                @php
                    $subCats = $category->children->count() > 0 
                        ? $category->children 
                        : ($category->parent ? $category->parent->children : collect());
                @endphp

                @forelse($subCats as $sub)
                    @php
                        $isSubActive = (isset($category) && ($category->id === $sub->id || $category->slug === $sub->slug));
                    @endphp
                    <a href="{{ url('/category/' . $sub->slug) }}" class="text-decoration-none text-dark flex-shrink-0 group">
                        <div class="surfeit-subcategory-card border-0 rounded-1 overflow-hidden {{ $isSubActive ? 'active-category-card' : '' }}" style="width: 140px; background-color: #f4f4f4; transition: all 0.3s ease;">
                            {{-- Visual Image Stacks --}}
                            <div class="position-relative overflow-hidden p-2 d-flex align-items-center justify-content-center category-icon-box" style="height: 100px; background: linear-gradient(180deg, #eaeaea 0%, #f4f4f4 100%);">
                                @php
                                    $subIconUrl = null;
                                    $subPossibleNames = array_unique([
                                        $sub->slug,
                                        str_replace('-', ' ', $sub->slug),
                                        str_replace(' ', '-', $sub->slug),
                                        \Illuminate\Support\Str::slug($sub->nama_kategori),
                                        strtolower($sub->nama_kategori),
                                    ]);
                                    $subExtensions = ['png', 'svg', 'webp', 'jpg', 'jpeg'];
                                    foreach ($subPossibleNames as $name) {
                                        foreach ($subExtensions as $ext) {
                                            $relPath = "images/categories/{$name}.{$ext}";
                                            if (file_exists(public_path($relPath))) {
                                                $subIconUrl = asset($relPath);
                                                break 2;
                                            }
                                        }
                                    }
                                @endphp

                                @if($subIconUrl)
                                    <img src="{{ $subIconUrl }}" alt="{{ $sub->nama_kategori }}" class="img-fluid" style="max-height: 65px; object-fit: contain;">
                                @else
                                    <i class="fas fa-boxes-stacked opacity-30 text-dark fa-2x"></i>
                                @endif
                            </div>
                            <div class="p-3 bg-f4">
                                <span class="fw-bold small d-block text-dark text-truncate" style="font-size: 0.82rem;">
                                    {{ $sub->nama_kategori }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="surfeit-subcategory-card border-0 rounded-1 overflow-hidden" style="width: 140px; background-color: #f4f4f4;">
                        <div class="position-relative p-2 d-flex align-items-center justify-content-center" style="height: 100px;">
                            <i class="fas fa-layer-group opacity-30 text-dark fa-2x"></i>
                        </div>
                        <div class="p-3">
                            <span class="fw-bold small d-block text-dark" style="font-size: 0.82rem;">
                                {{ $category->nama_kategori }}
                            </span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 2. FILTER & SORT TOOLBAR (Sesuai Referensi "Filter & Sort" + "8420 Items") --}}
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-light">
        {{-- Kiri: Filter & Sort Button Trigger --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link p-0 text-dark fw-bold text-decoration-none d-inline-flex align-items-center gap-2 border-0 shadow-none" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#filterDrawer" 
                    style="font-size: 1rem; color: #000000 !important;">
                <span class="border-bottom border-dark border-2 pb-0.5">Filter & Sort</span>
            </button>
        </div>

        {{-- Kanan: Items Counter Display (e.g. 8420 Items) --}}
        <div class="text-secondary small fw-normal" style="color: #777777 !important; font-size: 0.92rem;">
            {{ $products->total() }} Items
        </div>
    </div>

    {{-- COLLAPSIBLE FILTER PANEL --}}
    <div class="collapse mb-4" id="filterDrawer">
        <div class="card border-0 rounded-0 p-4" style="background-color: #f8f8f8;">
            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase small text-dark mb-2">Pilihan Sub-Kategori</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($subCats as $sc)
                            <a href="{{ url('/category/' . $sc->slug) }}" class="btn btn-outline-dark btn-sm rounded-0">
                                {{ $sc->nama_kategori }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-uppercase small text-dark mb-2">Urutkan Berdasarkan</h6>
                    <select id="sortSelectSurfeit" class="form-select form-select-sm rounded-0 border-dark" onchange="window.location.href='?sort=' + this.value">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama: A - Z</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- SKELETON LOADING PLACEHOLDER GRID --}}
    @include('components._product-skeleton-grid')

    {{-- 3. PRODUCT GRID (4 KOLOM DENGAN KOTAK ABU-ABU FLOATING IMAGE PERSI SAMA SEPERTI GAMBAR SURFEIT) --}}
    <div id="actualProductGrid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-xl-5 mb-5" style="display: none;">
        @forelse($products as $pro)
        @php
            $isB2B = auth()->check() && auth()->user()->role === 'branch';
            $actualPrice = $pro->harga_jual_actual;
            $b2bPrice = $isB2B ? $pro->getB2bPrice(1) : null;
            $isOutOfStock = $pro->stok_aktual <= $pro->nilai_ss;
        @endphp
        <div class="col">
            <div class="surfeit-product-wrapper cursor-pointer" onclick="window.location.href='{{ route('public.product.show', $pro->slug) }}'">
                
                {{-- KOTAK CONTAINER ABU-ABU TEMPAT GAMBAR MENGAPUNG (Sesuai Gambar Referensi) --}}
                <div class="surfeit-image-box position-relative d-flex align-items-center justify-content-center overflow-hidden" 
                     style="background-color: #f4f4f4; height: 380px; padding: 36px 24px; border-radius: 4px; transition: all 0.3s ease;">
                    
                    @if($pro->gambar)
                        <img src="{{ asset('storage/' . $pro->gambar) }}" 
                             alt="{{ $pro->nama_barang }}"
                             class="surfeit-floating-img img-fluid"
                             style="max-height: 290px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.12)); transition: transform 0.4s ease;">
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-box fa-3x opacity-25"></i>
                        </div>
                    @endif

                    {{-- Wishlist Floating Icon --}}
                    <button type="button" 
                            class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-3 shadow-sm border-0 wishlist-btn"
                            data-product-id="{{ $pro->id }}"
                            onclick="event.stopPropagation();"
                            style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #ffffff;">
                        <i class="far fa-bookmark text-dark"></i>
                    </button>
                </div>

                {{-- INFORMASI PRODUK DI LUAR / DI BAWAH KOTAK ABU-ABU (Sesuai Gambar Referensi) --}}
                <div class="surfeit-details-box mt-3">
                    {{-- Nama Produk (Sesuai font Navbar/Topbar) --}}
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.95rem; line-height: 1.3; font-family: 'Poppins', sans-serif;" title="{{ $pro->nama_barang }}">
                        {{ $pro->nama_barang }}
                    </h6>

                    {{-- Sub-judul / Kategori (Sesuai font Navbar/Topbar) --}}
                    <div class="text-secondary small mb-1" style="font-size: 0.82rem; color: #64748b !important; font-family: 'Poppins', sans-serif;">
                        {{ $pro->category ? $pro->category->nama_kategori : 'Podgasm Store' }}
                    </div>

                    {{-- Brand / Pembuat / Varian (seperti "Aenaes Gunn") --}}
                    <div class="fw-bold text-dark small mb-1" style="font-size: 0.82rem; color: #1e293b !important;">
                        {{ $pro->kode_barang ?? 'Original Vape Product' }}
                    </div>

                    {{-- Harga Bersih (seperti "$54.00") --}}
                    <div class="fw-bold price-text" style="font-size: 0.95rem;">
                        @if($isB2B && $b2bPrice < $actualPrice)
                            <span class="text-decoration-line-through text-muted small me-1">Rp {{ number_format($actualPrice, 0, ',', '.') }}</span>
                            Rp {{ number_format($b2bPrice, 0, ',', '.') }}
                        @else
                            {{ $pro->formatted_price }}
                        @endif
                    </div>
                </div>

            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">No items available in this category.</p>
        </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($products->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $products->links() }}
        </div>
    @endif
</div>

<style>
.surfeit-category-title {
    font-size: 3.8rem;
    line-height: 1;
    letter-spacing: -1.5px;
    color: #000000;
}

@media (max-width: 768px) {
    .surfeit-category-title {
        font-size: 2.5rem;
    }
}

.surfeit-product-wrapper:hover .surfeit-floating-img {
    transform: translateY(-6px) scale(1.03);
}

.surfeit-product-wrapper:hover .surfeit-image-box {
    background-color: #ededed;
}

.bg-f4 {
    background-color: #f4f4f4;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const skeletonGrid = document.getElementById('productGridSkeleton');
    const actualGrid = document.getElementById('actualProductGrid');
    if (skeletonGrid && actualGrid) {
        setTimeout(function() {
            skeletonGrid.style.transition = 'opacity 0.25s ease';
            skeletonGrid.style.opacity = '0';
            setTimeout(function() {
                skeletonGrid.style.display = 'none';
                actualGrid.style.display = 'flex';
                actualGrid.style.opacity = '0';
                actualGrid.style.transition = 'opacity 0.35s ease';
                setTimeout(function() { actualGrid.style.opacity = '1'; }, 20);
            }, 250);
        }, 200);
    }
});
</script>
@endpush
@endsection