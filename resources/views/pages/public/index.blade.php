@extends('layouts.frontend')

@section('content')
<div class="container-fluid px-lg-5 py-4">

    {{-- Promo Banner Slider --}}
    <div class="row mb-5">
        <div class="col-12">
            @include('components._promo-banner')
        </div>
    </div>

    {{-- HERO CATEGORY SECTION (Kiri: Judul, Kanan: Sub-Kategori Cards dengan Icon) --}}
    {{-- HERO CATEGORY SECTION --}}
    <div class="row align-items-center mb-5 g-3">
        <div class="col-xl-3 col-lg-4 col-12 d-flex align-items-center justify-content-between">
            <h1 class="surfeit-category-title text-dark fw-black mb-0">
                Category
            </h1>
            {{-- Tombol Navigasi (Otomatis tampil HANYA jika kartu meluap/overflow) --}}
            <div id="categoryScrollNav" class="d-none gap-2 align-items-center">
                <button type="button" class="btn btn-sm rounded-circle category-scroll-btn" id="btnScrollCatLeft" style="width: 36px; height: 36px; padding: 0;" title="Geser Kiri">
                    <i class="fas fa-chevron-left small"></i>
                </button>
                <button type="button" class="btn btn-sm rounded-circle category-scroll-btn" id="btnScrollCatRight" style="width: 36px; height: 36px; padding: 0;" title="Geser Kanan">
                    <i class="fas fa-chevron-right small"></i>
                </button>
            </div>
        </div>

        <div class="col-xl-9 col-lg-8 col-12 overflow-hidden">
            <div id="categoryScrollContainer" class="d-flex gap-3 overflow-x-auto pb-2 no-scrollbar justify-content-start justify-content-xl-end category-drag-scroll" style="cursor: grab; scroll-behavior: smooth;">
                @foreach($categories->where('parent_id', null) as $cat)
                    @php
                        $isCatActive = (isset($category) && ($category->id === $cat->id || $category->parent_id === $cat->id)) || request()->is('category/' . $cat->slug);
                    @endphp
                    <a href="{{ route('public.category', $cat->slug) }}" class="text-decoration-none text-dark flex-shrink-0">
                        <div class="surfeit-subcategory-card border-0 rounded-1 overflow-hidden {{ $isCatActive ? 'active-category-card' : '' }}" style="width: 135px; background-color: #f4f4f4; transition: all 0.3s ease;">
                            <div class="position-relative overflow-hidden p-2 d-flex align-items-center justify-content-center category-icon-box" style="height: 95px; background: linear-gradient(180deg, #eaeaea 0%, #f4f4f4 100%);">
                                @php
                                    $iconUrl = null;
                                    $possibleNames = array_unique([
                                        $cat->slug,
                                        str_replace('-', ' ', $cat->slug),
                                        str_replace(' ', '-', $cat->slug),
                                        \Illuminate\Support\Str::slug($cat->nama_kategori),
                                        strtolower($cat->nama_kategori),
                                    ]);
                                    $extensions = ['png', 'svg', 'webp', 'jpg', 'jpeg'];
                                    foreach ($possibleNames as $name) {
                                        foreach ($extensions as $ext) {
                                            $relPath = "images/categories/{$name}.{$ext}";
                                            if (file_exists(public_path($relPath))) {
                                                $iconUrl = asset($relPath);
                                                break 2;
                                            }
                                        }
                                    }
                                @endphp

                                @if($iconUrl)
                                    <img src="{{ $iconUrl }}" alt="{{ $cat->nama_kategori }}" class="img-fluid" style="max-height: 60px; object-fit: contain;">
                                @else
                                    <i class="fas fa-box opacity-30 text-dark fa-2x"></i>
                                @endif
                            </div>
                            <div class="p-2 text-center">
                                <span class="fw-bold small d-block text-dark text-truncate" style="font-size: 0.82rem;">
                                    {{ $cat->nama_kategori }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- FILTER & SORT TOOLBAR (Sesuai Referensi "Filter & Sort" + "Items") --}}
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-light">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold text-dark border-bottom border-dark border-2 pb-0.5" style="font-size: 1rem;">Filter & Sort</span>
        </div>

        <div class="text-secondary small fw-normal" style="color: #777777 !important; font-size: 0.92rem;">
            {{ $products->count() }} Items
        </div>
    </div>

    {{-- SKELETON LOADING PLACEHOLDER GRID --}}
    @include('components._product-skeleton-grid')

    {{-- PRODUCT GRID 4 KOLOM --}}
    <div id="actualProductGrid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-xl-5 mb-5" style="display: none;">
        @forelse($products as $pro)
        @php
            $isB2B = auth()->check() && auth()->user()->role === 'branch';
            $actualPrice = $pro->harga_jual_actual;
            $b2bPrice = $isB2B ? $pro->getB2bPrice(1) : null;
        @endphp
        <div class="col">
            <div class="surfeit-product-wrapper cursor-pointer" onclick="window.location.href='{{ route('public.product.show', $pro->slug) }}'">
                
                {{-- KOTAK CONTAINER ABU-ABU TEMPAT GAMBAR MENGAPUNG --}}
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

                    <button type="button" 
                            class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-3 shadow-sm border-0 wishlist-btn"
                            data-product-id="{{ $pro->id }}"
                            onclick="event.stopPropagation();"
                            style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #ffffff;">
                        <i class="far fa-bookmark text-dark"></i>
                    </button>
                </div>

                {{-- INFORMASI PRODUK DI LUAR / DI BAWAH KOTAK ABU-ABU --}}
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
            <p class="text-muted">No products available at the moment.</p>
        </div>
        @endforelse
    </div>

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
    const container = document.getElementById('categoryScrollContainer');
    const scrollNav = document.getElementById('categoryScrollNav');
    const btnLeft = document.getElementById('btnScrollCatLeft');
    const btnRight = document.getElementById('btnScrollCatRight');

    if (!container) return;

    // Cek apakah kartu meluap (overflow), tunjukkan panah hanya jika meluap & atur posisi kanan/kiri
    function checkOverflow() {
        if (!scrollNav) return;
        const isOverflowing = container.scrollWidth - container.clientWidth > 8;
        if (isOverflowing) {
            scrollNav.classList.remove('d-none');
            scrollNav.classList.add('d-flex');
            container.classList.remove('justify-content-xl-end', 'justify-content-lg-end');
            container.classList.add('justify-content-start');
        } else {
            scrollNav.classList.remove('d-flex');
            scrollNav.classList.add('d-none');
            container.classList.remove('justify-content-start');
            container.classList.add('justify-content-xl-end');
        }
    }

    // Jalankan saat load dan saat resize layar
    checkOverflow();
    window.addEventListener('resize', checkOverflow);

    // 1. Tombol Navigasi Kanan/Kiri
    if (btnLeft) {
        btnLeft.addEventListener('click', function() {
            container.scrollBy({ left: -260, behavior: 'smooth' });
        });
    }
    if (btnRight) {
        btnRight.addEventListener('click', function() {
            container.scrollBy({ left: 260, behavior: 'smooth' });
        });
    }

    // 2. Drag & Scroll dengan Mouse
    let isDown = false;
    let startX;
    let scrollLeft;

    container.addEventListener('mousedown', (e) => {
        isDown = true;
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
    });

    container.addEventListener('mouseleave', () => {
        isDown = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mouseup', () => {
        isDown = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.5;
        container.scrollLeft = scrollLeft - walk;
    });

    // 3. Scroll dengan Mouse Wheel (Roda Mouse)
    container.addEventListener('wheel', (e) => {
        if (e.deltaY !== 0) {
            e.preventDefault();
            container.scrollLeft += e.deltaY;
        }
    }, { passive: false });

    // 4. Skeleton Loader Smooth Fade Transition
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