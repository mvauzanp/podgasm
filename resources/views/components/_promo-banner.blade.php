<style>
    .promo-swiper {
        min-height: 400px;
    }
    .promo-banner-img {
        min-height: 400px;
        opacity: 1;
        transition: opacity 0.35s ease;
    }
    /* Gelapkan gambar hanya saat hover */
    .card:hover .promo-banner-img {
        opacity: 0.72;
    }
    .promo-title {
        font-size: 2.5rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    @media (max-width: 768px) {
        .promo-swiper {
            min-height: 250px !important;
        }
        .promo-banner-img {
            min-height: 250px !important;
        }
        .promo-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="row g-3" data-aos="fade-down">
    <div class="col-md-8">
        <div class="swiper promo-swiper rounded-4 shadow-sm h-100 promo-swiper">
            <div class="swiper-wrapper">
                @forelse($promoBanners as $banner)
                    <div class="swiper-slide">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" class="text-decoration-none text-white">
                        @endif
                        
                        <div class="card bg-dark text-white border-0 overflow-hidden position-relative h-100 rounded-4">
                            <img src="{{ $banner->gambar_url }}" class="card-img w-100 h-100 promo-banner-img" style="object-fit: cover;">
                            
                            @if($banner->judul || $banner->deskripsi)
                                <div class="card-img-overlay d-flex flex-column justify-content-center p-3 p-md-5" style="background: linear-gradient(to right, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.15) 60%, transparent 100%);">  
                                    @if($banner->judul)
                                        <h1 class="fw-bold promo-title mb-2">{{ $banner->judul }}</h1>
                                    @endif
                                    @if($banner->deskripsi)
                                        <p class="fs-6 fs-md-4 opacity-75 mb-3">{{ $banner->deskripsi }}</p>
                                    @endif
                                    @if($banner->link_url)
                                        <div class="mt-2">
                                            <span class="btn btn-primary rounded-pill px-4 px-md-5 shadow">Cek Detail</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Clickable full-image overlay --}}
                                <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                                    <span class="badge bg-primary rounded-pill px-3 py-2 align-self-end shadow-sm">Lihat Selengkapnya <i class="fas fa-arrow-right ms-1"></i></span>
                                </div>
                            @endif
                        </div>
                        
                        @if($banner->link_url)
                            </a>
                        @endif
                    </div>
                @empty
                    {{-- Slide 1 --}}
                    <div class="swiper-slide">
                        <div class="card bg-dark text-white border-0 overflow-hidden position-relative h-100 rounded-4">
                            <img src="{{ asset('assets/img/banner-promo-utama.jpg') }}" class="card-img w-100 h-100 promo-banner-img" style="object-fit: cover;">
                            <div class="card-img-overlay d-flex flex-column justify-content-center p-3 p-md-5">
                                <span class="badge bg-danger mb-3 shadow-sm" style="width: fit-content;">LIMITED OFFER</span>
                                <h1 class="fw-bold promo-title text-uppercase">Diskon 50%</h1>
                                <p class="fs-6 fs-md-4 opacity-75">Khusus untuk pembelian ritel (B2C) hari ini!</p>
                                <div class="mt-3">
                                    <a href="#katalog" class="btn btn-primary rounded-pill px-4 px-md-5 shadow">Cek Produk</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Slide 2 --}}
                    <div class="swiper-slide">
                        <div class="card bg-info text-white border-0 overflow-hidden position-relative h-100 rounded-4" style="background: linear-gradient(135deg, #09afb9, #00d4ff);">
                            <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center p-3 p-md-5 text-center">
                                <i class="fas fa-box-open fa-4x mb-3 opacity-50"></i>
                                <h1 class="fw-bold promo-title">Koleksi Baru Podgasm!</h1>
                                <p class="fs-6 fs-md-4 opacity-75">Varian rasa terbaru telah tiba.</p>
                                <div class="mt-3">
                                    <a href="#katalog" class="btn btn-light rounded-pill px-4 px-md-5 fw-bold shadow" style="color: #09afb9 !important;">Lihat Sekarang</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            {{-- Navigation --}}
            <div class="swiper-button-next text-white"></div>
            <div class="swiper-button-prev text-white"></div>
            {{-- Pagination --}}
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white border-0 shadow-sm rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #09afb9 0%, #078991 100%);">
            <div class="card-body d-flex flex-column justify-content-center p-4">
                <i class="fas fa-truck-moving fa-3x mb-3 opacity-50"></i>
                <h4 class="fw-bold">Free Ongkir Partner</h4>
                <p class="small">Khusus order B2B minimal 20 unit untuk area Surabaya & sekitarnya.</p>
                <a href="#" class="text-white fw-bold text-decoration-none">Syarat & Ketentuan →</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const promoSwiper = new Swiper('.promo-swiper', {
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });
    });
</script>
@endpush