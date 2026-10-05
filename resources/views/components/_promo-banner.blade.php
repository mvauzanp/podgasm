<style>
    .promo-swiper {
        width: 100%;
        aspect-ratio: 21 / 8;
        border-radius: 1.25rem;
        overflow: hidden;
    }
    @media (max-width: 991px) {
        .promo-swiper {
            aspect-ratio: 16 / 7;
            border-radius: 1rem;
        }
    }
    @media (max-width: 576px) {
        .promo-swiper {
            aspect-ratio: 16 / 9;
            border-radius: 0.85rem;
        }
    }
    .promo-banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        opacity: 1;
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .card:hover .promo-banner-img {
        transform: scale(1.02);
    }
    .promo-title {
        font-size: clamp(1.25rem, 2.5vw, 2.2rem);
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
        letter-spacing: -0.5px;
        line-height: 1.15;
    }
    .promo-subtitle {
        font-size: clamp(0.8rem, 1.2vw, 0.98rem);
        line-height: 1.45;
        letter-spacing: 0.2px;
    }
</style>

<div class="w-100" data-aos="fade-down">
    <div class="swiper promo-swiper rounded-4 shadow-sm w-100">
        <div class="swiper-wrapper">
            @forelse($promoBanners as $banner)
                <div class="swiper-slide">
                    @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" class="text-decoration-none text-white d-block h-100">
                    @endif
                    
                    <div class="card bg-dark text-white border-0 overflow-hidden position-relative h-100 rounded-4">
                        <img src="{{ $banner->gambar_url }}" class="card-img w-100 h-100 promo-banner-img" alt="{{ $banner->judul ?? 'Promo Banner Podgasm' }}">
                        
                        @if($banner->judul || $banner->deskripsi)
                            <div class="card-img-overlay d-flex flex-column justify-content-center p-4 p-md-5" style="background: linear-gradient(90deg, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.25) 55%, transparent 100%);">  
                                @if($banner->judul)
                                    <h2 class="fw-bold promo-title text-white mb-2">{{ $banner->judul }}</h2>
                                @endif
                                @if($banner->deskripsi)
                                    <p class="promo-subtitle text-light opacity-90 mb-3" style="max-width: 480px;">{{ $banner->deskripsi }}</p>
                                @endif
                                @if($banner->link_url)
                                    <div>
                                        <span class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm fw-semibold" style="font-size: 0.85rem;">
                                            Cek Detail <i class="fas fa-arrow-right ms-1.5 fa-xs"></i>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Clickable full-image overlay --}}
                            <div class="card-img-overlay d-flex flex-column justify-content-end p-3 p-md-4">
                                <span class="badge bg-primary rounded-pill px-3.5 py-1.5 align-self-end shadow-sm" style="font-size: 0.78rem;">Lihat Selengkapnya <i class="fas fa-arrow-right ms-1"></i></span>
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
                        <div class="card-img-overlay d-flex flex-column justify-content-center p-4 p-md-5" style="background: linear-gradient(90deg, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.25) 55%, transparent 100%);">
                            <div>
                                <span class="badge bg-danger mb-2.5 shadow-sm px-3 py-1" style="font-size: 0.75rem;">LIMITED OFFER</span>
                            </div>
                            <h2 class="fw-bold promo-title text-uppercase mb-2">Diskon Spesial Hari Ini</h2>
                            <p class="promo-subtitle opacity-90 text-light mb-3" style="max-width: 480px;">Nikmati harga hemat belanja vape &amp; liquid favorit Anda dengan diskon grosir otomatis!</p>
                            <div>
                                <a href="#katalog" class="btn btn-primary btn-sm rounded-pill px-4 py-2 shadow-sm fw-semibold" style="font-size: 0.85rem;">Cek Produk <i class="fas fa-arrow-right ms-1.5 fa-xs"></i></a>
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