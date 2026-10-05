@extends('layouts.frontend')

@section('content')
<div class="container-fluid px-3 px-md-4 px-xl-5 py-2 py-md-3">

    {{-- Breadcrumb Minimalis --}}
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0 align-items-center small flex-nowrap overflow-x-auto text-nowrap py-1 no-scrollbar">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-secondary text-decoration-none hover-primary">Home</a>
            </li>
            @if($category->parent)
                <li class="breadcrumb-item">
                    <a href="{{ url('/category/' . $category->parent->slug) }}" class="text-secondary text-decoration-none hover-primary">
                        {{ $category->parent->nama_kategori }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $category->nama_kategori }}</li>
        </ol>
    </nav>

    {{-- 1. HERO CATEGORY SECTION (Slider Kategori Lengkap & Sinkron) --}}
    @include('components._category-hero')

    @php
        $displaySubCats = $category->children->count() > 0 
            ? $category->children 
            : ($category->parent ? $category->parent->children : collect());
    @endphp

    {{-- Sub-Kategori Quick Pills (Jika kategori memiliki sub-kategori) --}}
    @if($displaySubCats->isNotEmpty())
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-3 no-scrollbar flex-nowrap">
            <span class="small text-muted fw-semibold me-1 text-nowrap">
                <i class="fas fa-tags text-primary me-1"></i> Sub:
            </span>
            @if($category->parent)
                <a href="{{ url('/category/' . $category->parent->slug) }}" class="btn btn-sm rounded-pill btn-outline-secondary px-3 py-1 text-nowrap" style="font-size: 0.8rem;">
                    Semua {{ $category->parent->nama_kategori }}
                </a>
            @endif
            @foreach($displaySubCats as $sc)
                @php $isScActive = $category->id === $sc->id; @endphp
                <a href="{{ url('/category/' . $sc->slug) }}" class="btn btn-sm rounded-pill {{ $isScActive ? 'btn-dark' : 'btn-outline-secondary bg-white' }} px-3 py-1 text-nowrap" style="font-size: 0.8rem;">
                    {{ $sc->nama_kategori }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- 2. FILTER & SORT TOOLBAR --}}
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-light flex-wrap gap-2">
        {{-- Kiri: Filter & Sort Button Trigger --}}
        <div class="d-flex align-items-center gap-2.5">
            {{-- Desktop Trigger (Collapse) --}}
            <button class="btn btn-link p-0 filter-sort-btn fw-bold text-decoration-none d-none d-lg-inline-flex align-items-center gap-2 border-0 shadow-none" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#filterDrawer">
                <span class="filter-sort-label border-bottom border-2 pb-0.5 d-inline-flex align-items-center gap-1.5">
                    <i class="fas fa-sliders-h small"></i> Filter &amp; Sort
                </span>
            </button>

            {{-- Mobile & Tablet Trigger (Offcanvas Bottom Sheet) --}}
            <button class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex d-lg-none align-items-center gap-2 shadow-sm mobile-filter-trigger" 
                    type="button" 
                    data-bs-toggle="offcanvas" 
                    data-bs-target="#mobileFilterOffcanvas" 
                    aria-controls="mobileFilterOffcanvas">
                <i class="fas fa-sliders-h small"></i>
                <span>Filter &amp; Sort</span>
                @if(request()->filled('sort') && request('sort') !== 'default')
                    <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">1</span>
                @endif
            </button>

            @if(request()->filled('sort') && request('sort') !== 'default')
                <a href="{{ request()->url() }}" class="badge bg-light text-dark border text-decoration-none px-2.5 py-1.5 rounded-pill small d-inline-flex align-items-center gap-1 filter-reset-badge">
                    <span>Reset Filter</span>
                    <i class="fas fa-times-circle text-danger"></i>
                </a>
            @endif
        </div>

        {{-- Kanan: Items Counter Display (e.g. 8420 Items) --}}
        <div class="text-secondary small fw-normal items-counter-text" style="font-size: 0.92rem;">
            {{ $products->total() }} Items
        </div>
    </div>

    {{-- COLLAPSIBLE FILTER PANEL (DESKTOP ONLY) --}}
    <div class="collapse mb-4 d-none d-lg-block" id="filterDrawer">
        <div class="card border-0 rounded-3 p-4 shadow-sm" style="background-color: #f8f9fa;">
            <div class="row g-4">
                @if($displaySubCats->isNotEmpty())
                    <div class="col-md-6 col-12">
                        <h6 class="fw-bold text-uppercase small text-dark mb-2.5">
                            <i class="fas fa-th-large me-1 text-primary"></i> Pilihan Sub-Kategori
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($displaySubCats as $sc)
                                <a href="{{ url('/category/' . $sc->slug) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1 {{ $category->id === $sc->id ? 'active' : '' }}">
                                    {{ $sc->nama_kategori }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="{{ $displaySubCats->isNotEmpty() ? 'col-md-6' : 'col-12' }} col-12">
                    <h6 class="fw-bold text-uppercase small text-dark mb-2.5">
                        <i class="fas fa-sort-amount-down me-1 text-primary"></i> Urutkan Berdasarkan
                    </h6>
                    <select id="sortSelectSurfeit" class="form-select form-select-sm rounded-3 border-secondary-subtle py-2" onchange="applyDesktopSort(this.value)">
                        <option value="default" {{ request('sort', 'default') === 'default' ? 'selected' : '' }}>⭐ Urutan Default</option>
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>🔥 Produk Terbaru</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>💰 Harga Terendah &rarr; Tertinggi</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>💎 Harga Tertinggi &rarr; Terendah</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>🔤 Nama: A - Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>🔤 Nama: Z - A</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- MOBILE OFFCANVAS FILTER (BOTTOM SHEET DRAWER FOR TABLET & MOBILE) --}}
    <div class="offcanvas offcanvas-bottom d-lg-none rounded-top-4 border-0 shadow-lg mobile-filter-bottom-sheet" 
         tabindex="-1" 
         id="mobileFilterOffcanvas" 
         aria-labelledby="mobileFilterOffcanvasLabel" 
         style="height: auto; max-height: 85vh;">
        
        {{-- Handle drag bar --}}
        <div class="d-flex justify-content-center pt-2.5 pb-1">
            <div class="rounded-pill bg-secondary-subtle" style="width: 42px; height: 4px;"></div>
        </div>

        {{-- Header --}}
        <div class="offcanvas-header px-4 py-2 border-bottom border-light-subtle">
            <h5 class="offcanvas-title fw-bold text-dark fs-6 d-flex align-items-center gap-2 mb-0" id="mobileFilterOffcanvasLabel">
                <i class="fas fa-sliders-h text-primary"></i>
                <span>Filter &amp; Pengurutan</span>
            </h5>
            <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        {{-- Body --}}
        <div class="offcanvas-body px-4 py-3 overflow-y-auto">
            {{-- Sub-kategori pilihan --}}
            @if($displaySubCats->isNotEmpty())
                <div class="mb-4">
                    <label class="form-label fw-bold text-uppercase small text-muted mb-2.5 d-flex align-items-center gap-1.5" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <i class="fas fa-th-large text-primary"></i> Sub-Kategori
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        @if($category->parent)
                            <a href="{{ url('/category/' . $category->parent->slug . (request()->has('sort') ? '?sort=' . request('sort') : '')) }}" 
                               class="btn btn-sm rounded-pill btn-outline-secondary px-3 py-1.5" style="font-size: 0.82rem;">
                                Semua {{ $category->parent->nama_kategori }}
                            </a>
                        @endif
                        @foreach($displaySubCats as $sc)
                            @php $isScActive = $category->id === $sc->id; @endphp
                            <a href="{{ url('/category/' . $sc->slug . (request()->has('sort') ? '?sort=' . request('sort') : '')) }}" 
                               class="btn btn-sm rounded-pill px-3 py-1.5 {{ $isScActive ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }}" 
                               style="font-size: 0.82rem;">
                                {{ $sc->nama_kategori }}
                                @if($isScActive)
                                    <i class="fas fa-check ms-1 small"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Opsi Sort Card --}}
            <div class="mb-2">
                <label class="form-label fw-bold text-uppercase small text-muted mb-2.5 d-flex align-items-center gap-1.5" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                    <i class="fas fa-sort-amount-down text-primary"></i> Urutkan Berdasarkan
                </label>
                @php
                    $currentSort = request('sort', 'default');
                    $sortOptions = [
                        'default' => ['label' => 'Urutan Default (Rekomendasi)', 'icon' => 'fas fa-star text-warning'],
                        'newest' => ['label' => 'Produk Terbaru', 'icon' => 'fas fa-fire text-danger'],
                        'price_low' => ['label' => 'Harga Terendah → Tertinggi', 'icon' => 'fas fa-arrow-down-short-wide text-success'],
                        'price_high' => ['label' => 'Harga Tertinggi → Terendah', 'icon' => 'fas fa-arrow-up-wide-short text-primary'],
                        'name_asc' => ['label' => 'Nama: A - Z', 'icon' => 'fas fa-arrow-down-a-z text-secondary'],
                        'name_desc' => ['label' => 'Nama: Z - A', 'icon' => 'fas fa-arrow-up-z-a text-secondary'],
                    ];
                @endphp
                <div class="d-flex flex-column gap-2" id="mobileSortOptions">
                    @foreach($sortOptions as $key => $opt)
                        @php $isSelected = ($currentSort === $key); @endphp
                        <div class="mobile-sort-card p-2.5 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer {{ $isSelected ? 'border-primary bg-primary-subtle active-sort' : 'border-light-subtle bg-white' }}"
                             data-sort-key="{{ $key }}"
                             onclick="selectMobileSort('{{ $key }}')" 
                             style="cursor: pointer; transition: all 0.2s;">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="{{ $opt['icon'] }} fa-fw"></i>
                                <span class="fw-semibold small sort-title {{ $isSelected ? 'text-primary' : 'text-dark' }}">{{ $opt['label'] }}</span>
                            </div>
                            <input type="radio" name="mobile_sort_radio" value="{{ $key }}" class="form-check-input mt-0" {{ $isSelected ? 'checked' : '' }}>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="offcanvas-footer p-3 border-top border-light-subtle d-flex gap-2 bg-white" style="padding-bottom: max(12px, env(safe-area-inset-bottom, 12px)) !important;">
            @if(request()->filled('sort') && request('sort') !== 'default')
                <a href="{{ request()->url() }}" class="btn btn-outline-secondary rounded-pill py-2.5 px-3.5 small fw-semibold flex-shrink-0">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            @endif
            <button type="button" class="btn btn-primary rounded-pill py-2.5 px-4 w-100 fw-bold shadow-sm" onclick="applyMobileSort()">
                Terapkan Filter
            </button>
        </div>
    </div>

    {{-- SKELETON LOADING PLACEHOLDER GRID --}}
    @include('components._product-skeleton-grid')

    {{-- 3. PRODUCT GRID --}}
    <div id="actualProductGrid" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4 g-xl-5 mb-5" style="display: none;">
        @forelse($products as $pro)
        <div class="col">
            @include('components._product-card', ['pro' => $pro])
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="fas fa-boxes fa-3x mb-3 opacity-30"></i>
                <p class="mb-0">Tidak ada produk dalam kategori ini saat ini.</p>
            </div>
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
    font-size: clamp(1.75rem, 2.4vw, 2.3rem);
    line-height: 1.1;
    letter-spacing: -0.8px;
    color: #0f172a;
    white-space: nowrap;
}

.filter-sort-btn {
    font-size: 1rem;
    color: #0f172a !important;
    transition: color 0.2s ease;
}
.filter-sort-label {
    border-bottom-color: #0f172a !important;
}
.filter-sort-btn:hover {
    color: #09afb9 !important;
}
.filter-sort-btn:hover .filter-sort-label {
    border-bottom-color: #09afb9 !important;
}

@media (max-width: 991px) {
    .surfeit-category-title {
        font-size: 1.9rem;
    }
}

.surfeit-product-wrapper:hover .surfeit-floating-img {
    transform: translateY(-6px) scale(1.03);
}

.surfeit-product-wrapper:hover .surfeit-image-box {
    background-color: #ededed;
}

/* Dark mode product card & image box styling */
body.dark-mode .surfeit-image-box,
html[data-bs-theme="dark"] .surfeit-image-box,
body.dark-mode .surfeit-product-wrapper,
html[data-bs-theme="dark"] .surfeit-product-wrapper {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

body.dark-mode .surfeit-product-wrapper:hover .surfeit-image-box,
html[data-bs-theme="dark"] .surfeit-product-wrapper:hover .surfeit-image-box {
    background-color: rgba(255, 255, 255, 0.04) !important;
}

body.dark-mode .wishlist-btn,
html[data-bs-theme="dark"] .wishlist-btn {
    background-color: rgba(30, 41, 59, 0.8) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
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

/* Dark mode styling for Filter & Sort & Drawer */
body.dark-mode .filter-sort-btn,
html[data-bs-theme="dark"] .filter-sort-btn {
    color: #f8fafc !important;
}
body.dark-mode .filter-sort-label,
html[data-bs-theme="dark"] .filter-sort-label {
    border-bottom-color: #f8fafc !important;
}
body.dark-mode .filter-sort-btn:hover,
html[data-bs-theme="dark"] .filter-sort-btn:hover {
    color: #38bdf8 !important;
}
body.dark-mode .filter-sort-btn:hover .filter-sort-label,
html[data-bs-theme="dark"] .filter-sort-btn:hover .filter-sort-label {
    border-bottom-color: #38bdf8 !important;
}
body.dark-mode .items-counter-text,
html[data-bs-theme="dark"] .items-counter-text {
    color: #94a3b8 !important;
}
body.dark-mode .surfeit-category-title,
html[data-bs-theme="dark"] .surfeit-category-title {
    color: #f8fafc !important;
}
body.dark-mode #filterDrawer .card,
html[data-bs-theme="dark"] #filterDrawer .card {
    background-color: #1a2234 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
body.dark-mode #filterDrawer .text-dark,
html[data-bs-theme="dark"] #filterDrawer .text-dark {
    color: #ffffff !important;
}
body.dark-mode #sortSelectSurfeit,
html[data-bs-theme="dark"] #sortSelectSurfeit {
    background-color: #0f172a;
    color: #ffffff;
    border-color: #334155;
}
body.dark-mode #filterDrawer .btn-outline-secondary,
html[data-bs-theme="dark"] #filterDrawer .btn-outline-secondary {
    background-color: #0f172a !important;
    color: #cbd5e1 !important;
    border-color: #334155 !important;
}
body.dark-mode #filterDrawer .btn-outline-secondary:hover,
html[data-bs-theme="dark"] #filterDrawer .btn-outline-secondary:hover {
    background-color: #38bdf8 !important;
    color: #0f172a !important;
}

/* Mobile Offcanvas Bottom Sheet Styling */
.mobile-filter-bottom-sheet {
    border-top-left-radius: 20px !important;
    border-top-right-radius: 20px !important;
    box-shadow: 0 -10px 30px rgba(15, 23, 42, 0.15) !important;
}
.mobile-sort-card {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.mobile-sort-card:hover {
    border-color: #0d6efd !important;
}
.mobile-sort-card.active-sort {
    background-color: rgba(13, 110, 253, 0.08) !important;
    border-color: #0d6efd !important;
}

/* Mobile Offcanvas Bottom Sheet Dark Mode */
body.dark-mode .mobile-filter-bottom-sheet,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}
body.dark-mode .mobile-filter-bottom-sheet .offcanvas-header,
body.dark-mode .mobile-filter-bottom-sheet .offcanvas-footer,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .offcanvas-header,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .offcanvas-footer {
    background-color: #0f172a !important;
    border-color: #1e293b !important;
}
body.dark-mode .mobile-filter-bottom-sheet .mobile-sort-card,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .mobile-sort-card {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
body.dark-mode .mobile-filter-bottom-sheet .mobile-sort-card.active-sort,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .mobile-sort-card.active-sort {
    background-color: rgba(13, 110, 253, 0.2) !important;
    border-color: #38bdf8 !important;
}
body.dark-mode .mobile-filter-bottom-sheet .sort-title,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .sort-title {
    color: #f1f5f9 !important;
}
body.dark-mode .mobile-filter-bottom-sheet .btn-close,
html[data-bs-theme="dark"] .mobile-filter-bottom-sheet .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
body.dark-mode .mobile-filter-trigger,
html[data-bs-theme="dark"] .mobile-filter-trigger {
    border-color: #475569 !important;
    color: #f8fafc !important;
    background-color: #1e293b !important;
}
</style>

@push('scripts')
<script>
let selectedMobileSort = "{{ request('sort', 'default') }}";

function selectMobileSort(sortKey) {
    selectedMobileSort = sortKey;
    document.querySelectorAll('#mobileSortOptions .mobile-sort-card').forEach(card => {
        const isMatch = card.getAttribute('data-sort-key') === sortKey;
        card.classList.toggle('border-primary', isMatch);
        card.classList.toggle('bg-primary-subtle', isMatch);
        card.classList.toggle('active-sort', isMatch);
        card.classList.toggle('border-light-subtle', !isMatch);
        card.classList.toggle('bg-white', !isMatch);
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = isMatch;
        const titleSpan = card.querySelector('.sort-title');
        if (titleSpan) {
            titleSpan.classList.toggle('text-primary', isMatch);
            titleSpan.classList.toggle('text-dark', !isMatch);
        }
    });
}

function applyMobileSort() {
    const url = new URL(window.location.href);
    if (selectedMobileSort && selectedMobileSort !== 'default') {
        url.searchParams.set('sort', selectedMobileSort);
    } else {
        url.searchParams.delete('sort');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function applyDesktopSort(val) {
    const url = new URL(window.location.href);
    if (val && val !== 'default') {
        url.searchParams.set('sort', val);
    } else {
        url.searchParams.delete('sort');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

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