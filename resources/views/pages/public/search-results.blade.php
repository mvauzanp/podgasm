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
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Hasil Pencarian</li>
        </ol>
    </nav>

    {{-- HEADER & FORM PENCARIAN --}}
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom border-light flex-wrap gap-3">
        <div>
            @if($query)
                <h2 class="fw-black text-dark mb-1" style="font-size: 2.2rem; letter-spacing: -0.5px;">
                    Hasil Pencarian "{{ $query }}"
                </h2>
                <p class="text-secondary small mb-0">Menampilkan produk yang cocok dengan kata kunci pencarian Anda</p>
            @else
                <h2 class="fw-black text-dark mb-1" style="font-size: 2.2rem; letter-spacing: -0.5px;">
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

    {{-- PRODUCT GRID SURFEIT (4 KOLOM DENGAN KOTAK ABU-ABU FLOATING IMAGE) --}}
    @if(isset($products) && $products->count() > 0)
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-xl-5 mb-5">
            @foreach($products as $pro)
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
                        <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.95rem; line-height: 1.3; font-family: 'Poppins', sans-serif;" title="{{ $pro->nama_barang }}">
                            {{ $pro->nama_barang }}
                        </h6>

                        <div class="text-secondary small mb-1" style="font-size: 0.82rem; color: #64748b !important; font-family: 'Poppins', sans-serif;">
                            {{ $pro->category ? $pro->category->nama_kategori : 'Podgasm Store' }}
                        </div>

                        <div class="fw-bold text-dark small mb-1" style="font-size: 0.82rem; color: #1e293b !important;">
                            {{ $pro->kode_barang ?? 'Original Vape Product' }}
                        </div>

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
            <p class="text-secondary max-w-md mx-auto mb-4" style="max-width: 450px;">
                Maaf, tidak ada produk yang cocok dengan kata kunci "<strong>{{ $query }}</strong>". Coba gunakan kata kunci lain seperti <em>liquid, saltnic, pod, atau cartridge</em>.
            </p>
            <a href="{{ route('home') }}" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold small">
                Lihat Semua Produk
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
</style>
@endsection
