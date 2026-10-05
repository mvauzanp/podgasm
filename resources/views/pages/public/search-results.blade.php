@extends('layouts.frontend')

@section('content')
<div class="container-fluid px-3 px-md-4 px-xl-5 py-2 py-md-3">

    {{-- Breadcrumb Minimalis --}}
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb bg-transparent p-0 mb-0 align-items-center small flex-nowrap overflow-x-auto text-nowrap py-1 no-scrollbar">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-secondary text-decoration-none hover-primary">Home</a>
            </li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Hasil Pencarian</li>
        </ol>
    </nav>

    {{-- HEADER & FORM PENCARIAN --}}
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-light flex-wrap gap-3">
        <div>
            @if($query)
                <h2 class="fw-black text-dark mb-1" style="font-size: 1.8rem; letter-spacing: -0.5px;">
                    Hasil Pencarian "{{ $query }}"
                </h2>
                <p class="text-secondary small mb-0">Menampilkan produk yang cocok dengan kata kunci pencarian Anda</p>
            @else
                <h2 class="fw-black text-dark mb-1" style="font-size: 1.8rem; letter-spacing: -0.5px;">
                    Pencarian Produk
                </h2>
            @endif
        </div>

        @if(isset($products) && $products->count() > 0)
            <div class="text-secondary small fw-normal" style="color: #777777 !important; font-size: 0.92rem;">
                {{ $products->total() }} Items Ditemukan
            </div>
        @endif
    </div>

    {{-- Pesan Peringatan (Jika Query Kurang Dari 2 Karakter) --}}
    @if(isset($message))
        <div class="alert alert-info rounded-3 border-0 mb-4 p-3 d-flex align-items-center gap-2">
            <i class="fas fa-info-circle fs-5"></i>
            <div>{{ $message }}</div>
        </div>
    @endif

    {{-- PRODUCT GRID SURFEIT (4 KOLOM) --}}
    @if(isset($products) && $products->count() > 0)
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4 g-xl-5 mb-5">
            @foreach($products as $pro)
            <div class="col">
                @include('components._product-card', ['pro' => $pro])
            </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-center mt-4 mb-5">
            {{ $products->links() }}
        </div>
    @else
        {{-- EMPTY STATE HASIL TIDAK DITEMUKAN --}}
        <div class="text-center py-5 my-4">
            <div class="mb-3">
                <i class="fas fa-search fa-4x text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Produk Tidak Ditemukan</h4>
            <p class="text-secondary max-w-md mx-auto mb-4" style="max-width: 480px;">
                Maaf, tidak ada produk yang cocok dengan kata kunci "<strong>{{ $query }}</strong>". Coba gunakan kata kunci yang lebih umum atau jelajahi kategori terpopuler di bawah ini:
            </p>

            {{-- Rekomendasi Kategori Terpopuler --}}
            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-4 max-w-md mx-auto" style="max-width: 500px;">
                <a href="{{ route('public.search') }}?q=liquid" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5">
                    <i class="fas fa-wine-bottle me-1 text-primary"></i> Liquid
                </a>
                <a href="{{ route('public.search') }}?q=saltnic" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5">
                    <i class="fas fa-tint me-1 text-info"></i> Saltnic
                </a>
                <a href="{{ route('public.search') }}?q=pod" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5">
                    <i class="fas fa-bolt me-1 text-warning"></i> Pod System
                </a>
                <a href="{{ route('public.search') }}?q=cartridge" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1.5">
                    <i class="fas fa-cube me-1 text-success"></i> Cartridge
                </a>
            </div>

            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 py-2.5 fw-semibold small shadow-sm">
                <i class="fas fa-store me-1"></i> Jelajahi Seluruh Katalog
            </a>
        </div>
    @endif

</div>

<style>
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
</style>
@endsection
