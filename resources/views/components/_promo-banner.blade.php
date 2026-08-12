<style>
    .promo-swiper {
        height: 280px;
    }
    .promo-banner-img {
        height: 280px;
        object-fit: cover;
        opacity: 1;
        transition: opacity 0.35s ease;
    }
    /* Gelapkan gambar hanya saat hover */
    .card:hover .promo-banner-img {
        opacity: 0.85;
    }
    .promo-title {
        font-size: 1.85rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
        letter-spacing: -0.5px;
    }
    .promo-subtitle {
        font-size: 0.95rem;
        line-height: 1.4;
    }
    @media (max-width: 992px) {
        .promo-swiper, .promo-banner-img {
            height: 240px !important;
        }
        .promo-title {
            font-size: 1.4rem;
        }
    }
    @media (max-width: 576px) {
        .promo-swiper, .promo-banner-img {
            height: 200px !important;
        }
        .promo-title {
            font-size: 1.2rem;
        }
        .promo-subtitle {
            font-size: 0.82rem;
        }
    }
</style>

<div class="row g-3" data-aos="fade-down">
    <div class="col-lg-8 col-md-7">
        <div class="swiper promo-swiper rounded-4 shadow-sm h-100">
            <div class="swiper-wrapper">
                @forelse($promoBanners as $banner)
                    <div class="swiper-slide">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" class="text-decoration-none text-white">
                        @endif
                        
                        <div class="card bg-dark text-white border-0 overflow-hidden position-relative h-100 rounded-4">
                            <img src="{{ $banner->gambar_url }}" class="card-img w-100 h-100 promo-banner-img">
                            
                            @if($banner->judul || $banner->deskripsi)
                                <div class="card-img-overlay d-flex flex-column justify-content-center p-4 p-lg-5" style="background: linear-gradient(90deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.3) 60%, transparent 100%);">  
                                    @if($banner->judul)
                                        <h2 class="fw-bold promo-title text-white mb-2">{{ $banner->judul }}</h2>
                                    @endif
                                    @if($banner->deskripsi)
                                        <p class="promo-subtitle opacity-90 text-light mb-3" style="max-width: 480px;">{{ $banner->deskripsi }}</p>
                                    @endif
                                    @if($banner->link_url)
                                        <div>
                                            <span class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm fw-semibold">Cek Detail <i class="fas fa-arrow-right ms-1"></i></span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Clickable full-image overlay --}}
                                <div class="card-img-overlay d-flex flex-column justify-content-end p-3 p-md-4">
                                    <span class="badge bg-primary rounded-pill px-3 py-2 align-self-end shadow-sm fs-7">Lihat Selengkapnya <i class="fas fa-arrow-right ms-1"></i></span>
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
                            <img src="{{ asset('assets/img/banner-promo-utama.jpg') }}" class="card-img w-100 h-100 promo-banner-img">
                            <div class="card-img-overlay d-flex flex-column justify-content-center p-4 p-lg-5" style="background: linear-gradient(90deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.3) 60%, transparent 100%);">
                                <div>
                                    <span class="badge bg-danger mb-2 shadow-sm px-3 py-1" style="font-size: 0.75rem;">LIMITED OFFER</span>
                                </div>
                                <h2 class="fw-bold promo-title text-uppercase mb-2">Diskon 50%</h2>
                                <p class="promo-subtitle opacity-90 text-light mb-3">Khusus untuk pembelian ritel (B2C) hari ini!</p>
                                <div>
                                    <a href="#katalog" class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm fw-semibold">Cek Produk <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Slide 2 --}}
                    <div class="swiper-slide">
                        <div class="card bg-info text-white border-0 overflow-hidden position-relative h-100 rounded-4" style="background: linear-gradient(135deg, #09afb9, #00d4ff);">
                            <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center p-4 text-center">
                                <i class="fas fa-box-open fa-3x mb-2 opacity-75"></i>
                                <h2 class="fw-bold promo-title mb-2">Koleksi Baru Podgasm!</h2>
                                <p class="promo-subtitle opacity-90 text-light mb-3">Varian rasa terbaru telah tiba.</p>
                                <div>
                                    <a href="#katalog" class="btn btn-light btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm" style="color: #09afb9 !important;">Lihat Sekarang</a>
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

    <div class="col-lg-4 col-md-5">
        <div class="card text-white border-0 shadow-sm rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #09afb9 0%, #078991 100%);">
            <div class="card-body d-flex flex-column justify-content-center p-4">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="bg-white bg-opacity-20 rounded-3 p-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-truck-moving fa-2x text-white"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Free Ongkir Partner</h5>
                        <span class="badge bg-warning text-dark mt-1" style="font-size: 0.7rem;">KHUSUS B2B</span>
                    </div>
                </div>
                <p class="small opacity-90 mb-3" style="line-height: 1.4;">Minimal order 20 unit khusus area Surabaya & sekitarnya.</p>
                <a href="#" class="text-white fw-bold small text-decoration-none d-inline-flex align-items-center gap-1">
                    <span>Syarat & Ketentuan</span>
                    <i class="fas fa-arrow-right small"></i>
                </a>
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