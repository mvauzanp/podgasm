{{-- EXACT SURFEIT HEADER & NAVIGATION BAR --}}
<div class="fixed-top shadow-none bg-white" style="z-index: 9999;">
    
    {{-- 1. TOP ANNOUNCEMENT BAR (Sesuai Referensi Gambar Top Bar) --}}
    <div class="top-announcement-bar py-1 px-3 border-bottom" style="background-color: #f4f3f0; border-color: #e8e7e3 !important; font-size: 0.8rem;">
        <div class="container-fluid px-2 px-lg-5 d-flex justify-content-between align-items-center">
            {{-- Kiri: Teks Miring Informasi --}}
            <div class="text-secondary fst-italic text-truncate text-center text-md-start w-100 w-md-auto" style="font-family: Georgia, 'Times New Roman', serif;">
                Garansi 100% Produk Original &amp; Pengiriman Cepat 21+ ke Seluruh Indonesia
            </div>

            {{-- Kanan: Link Log In & Create Account --}}
            <div class="d-none d-md-flex align-items-center gap-3 ms-auto small">
                @auth
                    <span class="text-dark fw-semibold">Halo, {{ auth()->user()->name }}</span>
                    <a href="{{ route('profile.show') }}" class="text-dark text-decoration-none hover-underline">Akun Saya</a>
                    <button type="button" class="btn btn-link p-0 text-secondary text-decoration-none small" onclick="document.getElementById('logoutForm').submit()">Logout</button>
                @else
                    <a href="{{ route('login') }}" class="text-dark text-decoration-none hover-underline fw-medium">Log In</a>
                    <a href="{{ route('register') }}" class="text-dark text-decoration-none hover-underline fw-medium">Create Account</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- 2. MAIN NAVBAR SURFEIT (Logo Kiri, Menu Tengah, Ikon Kanan) --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom border-light py-1.5 py-md-2">
        <div class="container-fluid px-2 px-lg-5">
            
            {{-- BRAND LOGO (Gambar Logo Podgasm) --}}
            <a class="navbar-brand me-1 me-md-4 me-xl-5 d-flex align-items-center py-0" href="{{ route('home') }}">
                <img src="{{ asset('PodgasmHome.png') }}" alt="Podgasm Logo" class="navbar-logo" style="height: 44px; max-height: 48px; width: auto; object-fit: contain;">
            </a>

            {{-- NAV MENU LINKS (Horizontal Sejajar Sesuai Referensi) --}}
            <div class="collapse navbar-collapse order-3 order-lg-2" id="surfeitNavMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center gap-1 gap-xl-3" style="font-size: 0.88rem;">
                    
                    {{-- 1. DESKTOP MEGA MENU (Layar Desktop >= 992px) --}}
                    <li class="nav-item desktop-category-nav-item d-none d-lg-block position-relative">
                        <a class="nav-link text-dark fw-semibold py-1 text-nowrap d-flex align-items-center gap-1" 
                           href="javascript:void(0)" 
                           id="desktopMegaMenuTrigger" 
                           role="button"
                           aria-expanded="false">
                            <span>Categories</span>
                            <i class="fas fa-chevron-down opacity-50 ms-0.5 mega-chevron-icon" style="font-size: 0.65rem; transition: transform 0.25s ease;"></i>
                        </a>

                        {{-- FLOATING MEGA MENU PANEL (Luxury Category Grid) --}}
                        <div class="desktop-mega-menu-panel shadow-2xl rounded-4 overflow-hidden border" id="desktopMegaMenu">
                            {{-- Category Grid: 4 Kolom x 4 Baris --}}
                            <div class="p-3.5 mega-grid-body overflow-y-auto no-scrollbar" style="max-height: 400px;">
                                <div class="row row-cols-4 g-2.5">
                                    @foreach($categories->where('parent_id', null) as $cat)
                                        @php
                                            $mIconUrl = null;
                                            $mIconMap = [
                                                'saltnic-liquid' => 'saltnic',
                                                'freebase-liquid' => 'freebase',
                                                'aksesoris' => 'accessories',
                                                'cartridge' => 'close-system',
                                                'pod-device' => 'pod',
                                                'disposable-pod' => 'pod',
                                                'pod-close-system' => 'close-system',
                                                'mod-device' => 'mod',
                                                'occ' => 'atomizer',
                                                'aio' => 'aio',
                                                'veev' => 'close-system',
                                                'iluma-iqos' => 'pod',
                                                'bondsblends-by-iqos' => 'pod',
                                                'terea-iqos' => 'pod',
                                                'coil-prebuild' => 'atomizer',
                                            ];
                                            $mNames = array_unique([
                                                $mIconMap[$cat->slug] ?? null,
                                                $cat->slug,
                                                \Illuminate\Support\Str::slug($cat->nama_kategori),
                                                strtolower($cat->nama_kategori),
                                            ]);
                                            foreach ($mNames as $n) {
                                                if (!$n) continue;
                                                foreach (['png', 'svg', 'webp'] as $ext) {
                                                    $rp = "images/categories/{$n}.{$ext}";
                                                    if (file_exists(public_path($rp))) {
                                                        $mIconUrl = asset($rp);
                                                        break 2;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="col">
                                            <a href="{{ route('public.category', $cat->slug) }}" class="mega-grid-card d-flex align-items-center gap-2.5 p-2.5 rounded-3 text-decoration-none transition-all">
                                                <div class="mega-grid-icon-box rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    @if($mIconUrl)
                                                        <img src="{{ $mIconUrl }}" alt="{{ $cat->nama_kategori }}" style="max-height: 24px; max-width: 24px; object-fit: contain;">
                                                    @else
                                                        <i class="fas fa-box opacity-40 text-dark" style="font-size: 0.85rem;"></i>
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="fw-bold text-dark text-truncate mega-card-title" style="font-size: 0.82rem;">
                                                        {{ $cat->nama_kategori }}
                                                    </div>
                                                    <span class="text-muted text-truncate d-block mega-card-subtitle" style="font-size: 0.7rem;">
                                                        Lihat Produk &rarr;
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Footer Quick Actions --}}
                            <div class="p-2.5 px-4 bg-light border-top d-flex align-items-center justify-content-between mega-menu-footer">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-secondary small fw-medium" style="font-size: 0.75rem;">Akses Cepat:</span>
                                    <a href="{{ route('home') }}?sort=newest" class="badge bg-white text-dark text-decoration-none rounded-pill px-2.5 py-1 border shadow-2xs mega-footer-pill">
                                        <i class="fas fa-bolt text-warning me-1"></i> Produk Terbaru
                                    </a>
                                    <a href="{{ route('home') }}?sort=promo" class="badge bg-white text-dark text-decoration-none rounded-pill px-2.5 py-1 border shadow-2xs mega-footer-pill">
                                        <i class="fas fa-percent text-danger me-1"></i> Promo &amp; Diskon
                                    </a>
                                </div>
                                <div class="d-flex align-items-center gap-1 text-muted small" style="font-size: 0.75rem;">
                                    <i class="fas fa-shield-alt text-primary me-1"></i> 100% Produk Original 21+
                                </div>
                            </div>
                        </div>
                    </li>

                    {{-- 2. MOBILE CATEGORIES BUTTON (Hanya Muncul di Mobile/HP < 992px) --}}
                    <li class="nav-item d-lg-none">
                        <button class="nav-link text-dark fw-semibold border-0 bg-transparent py-1 text-nowrap" type="button" data-bs-toggle="offcanvas" data-bs-target="#categoryCanvas">
                            Categories <i class="fas fa-chevron-right ms-1 opacity-50" style="font-size: 0.65rem;"></i>
                        </button>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium py-1 text-nowrap" href="{{ route('home') }}?sort=newest">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium py-1 text-nowrap" href="{{ route('home') }}?sort=promo">Sale</a>
                    </li>
                </ul>
            </div>

            {{-- RIGHT ICONS (Search, Bookmark/Wishlist, Bag, Theme Switcher) --}}
            <div class="d-flex align-items-center gap-1 gap-sm-1.5 gap-md-2 ms-auto order-2 order-lg-3">
                {{-- Search Icon Trigger --}}
                <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none" 
                        type="button" 
                        onclick="toggleSearchDrawer()" 
                        title="Search">
                    <i class="fas fa-search text-dark"></i>
                </button>

                {{-- Wishlist Ribbon Icon --}}
                <a href="{{ route('wishlist.index') }}" 
                   class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                   id="navbarWishlistBtn"
                   title="Wishlist">
                    <i class="far fa-heart text-danger"></i>
                    @if(($wishlistCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger text-white border border-light wishlist-badge-counter" style="font-size: 0.55rem; padding: 2px 4px;">
                            {{ $wishlistCount }}
                        </span>
                    @endif
                </a>

                {{-- Shopping Bag Icon --}}
                <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                        type="button" 
                        id="navbarCartBtn"
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#offcanvasCart" 
                        onclick="loadOffcanvasCart()" 
                        title="Shopping Bag">
                    <i class="fas fa-shopping-bag text-primary"></i>
                    @if(($cartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-primary text-white border border-light cart-badge-counter" style="font-size: 0.55rem; padding: 2px 4px;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </button>

                {{-- Account Dropdown Icon Button --}}
                <div class="dropdown">
                    <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                            type="button" 
                            id="accountDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false" 
                            title="Akun Saya">
                        <i class="far fa-user text-dark"></i>
                        @auth
                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" style="width: 8px; height: 8px;"></span>
                        @endauth
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2 mt-2" aria-labelledby="accountDropdownBtn" style="min-width: 230px; z-index: 99999;">
                        @auth
                            <li class="px-3 py-2 border-bottom mb-1 bg-light bg-opacity-50 rounded-3">
                                <div class="fw-bold text-dark text-truncate small" style="max-width: 190px;">{{ auth()->user()->name }}</div>
                                <div class="text-muted text-truncate" style="font-size: 0.75rem; max-width: 190px;">{{ auth()->user()->email }}</div>
                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 mt-1" style="font-size: 0.65rem;">
                                    {{ ucfirst(auth()->user()->role ?? 'Pelanggan') }}
                                </span>
                            </li>
                            @if(auth()->user()->role === 'admin')
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-chart-line me-2 text-primary"></i> Dashboard Admin
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->role === 'branch')
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('branch.dashboard') }}">
                                        <i class="fas fa-store me-2 text-primary"></i> Portal Toko Cabang
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('branch.request') }}">
                                        <i class="fas fa-truck-loading me-2 text-primary"></i> Request Stok ke Pusat
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('branch.tracking') }}">
                                        <i class="fas fa-shipping-fast me-2 text-primary"></i> Lacak Pengiriman Barang
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                            @endif
                            <li>
                                <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('profile.show') }}">
                                    <i class="fas fa-user-circle me-2 text-primary"></i> Profil &amp; Pengaturan Akun
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('order.history') }}">
                                    <i class="fas fa-box-open me-2 text-primary"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 small fw-semibold text-dark" href="{{ route('wishlist.index') }}">
                                    <i class="far fa-heart me-2 text-danger"></i> Wishlist Saya
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button type="button" class="dropdown-item rounded-3 py-2 small text-danger fw-semibold" onclick="document.getElementById('logoutForm').submit()">
                                    <i class="fas fa-sign-out-alt me-2"></i> Keluar (Logout)
                                </button>
                            </li>
                        @else
                            <li class="px-3 py-2 mb-2">
                                <div class="fw-bold text-dark small">Selamat Datang!</div>
                                <div class="text-muted small" style="font-size: 0.78rem;">Masuk ke akun Anda untuk bertransaksi</div>
                            </li>
                            <li class="px-2 pb-1">
                                <a class="btn btn-primary btn-sm w-100 rounded-3 py-2.5 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2 nav-login-btn" 
                                   href="{{ route('login') }}" 
                                   style="background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%); border: none; color: #ffffff !important;">
                                    <i class="fas fa-sign-in-alt text-white" style="font-size: 0.9rem;"></i>
                                    <span class="text-white">Masuk (Log In)</span>
                                </a>
                                <a class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2 nav-register-btn" 
                                   href="{{ route('register') }}">
                                    <i class="fas fa-user-plus" style="font-size: 0.88rem;"></i>
                                    <span>Daftar Akun Baru</span>
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>

                {{-- Theme Switcher Toggle (Terang / Gelap) --}}
                {{-- Theme Switcher Toggle (Terang / Gelap) --}}
                <button type="button" 
                        class="btn theme-toggle-btn rounded-circle d-inline-flex align-items-center justify-content-center shadow-none border-0" 
                        id="themeToggleBtn" 
                        onclick="toggleTheme()" 
                        title="Ganti Tema (Gelap/Terang)">
                    <i class="fas fa-moon text-dark" id="themeToggleIcon"></i>
                </button>

                {{-- Mobile Burger Toggle --}}
                <button class="navbar-toggler border-0 shadow-none p-0 d-lg-none ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#surfeitNavMenu" title="Menu">
                    <i class="fas fa-bars text-dark" style="font-size: 1.15rem;"></i>
                </button>
            </div>
        </div>
    </nav>

    {{-- FLOATING SEARCH BAR POPOVER OVERLAY --}}
    <div id="surfeitSearchCollapse" class="surfeit-search-popover position-absolute top-100 start-0 w-100 bg-white border-bottom shadow-lg py-4 px-3" style="display: none; z-index: 99999; transition: all 0.3s ease;">
        <div class="container-fluid px-lg-5">
            <form action="{{ route('public.search') }}" method="GET" class="d-flex align-items-center gap-2 max-w-lg mx-auto" style="max-width: 650px;">
                <div class="input-group input-group-minimalist rounded-pill overflow-hidden border border-light bg-slate-50 w-100 p-1.5 shadow-sm">
                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                        <i class="fas fa-search" style="font-size: 1rem;"></i>
                    </span>
                    <input type="text" class="form-control bg-transparent border-0 shadow-none text-dark fw-medium" placeholder="Search products, liquid, pods..." name="q" value="{{ request('q') }}" required style="font-size: 0.95rem; padding: 10px 14px;" id="searchInputField" autocomplete="off">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold text-white small me-1" style="font-size: 0.88rem;">Search</button>
                </div>
                <button type="button" class="btn btn-light rounded-circle ms-2 p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" onclick="toggleSearchDrawer()" title="Tutup Pencarian" style="width: 42px; height: 42px;">
                    <i class="fas fa-times text-dark"></i>
                </button>
            </form>
            
            {{-- Live Search Autocomplete Results Container --}}
            <div id="liveSearchResultsContainer" class="mt-3 max-w-lg mx-auto overflow-hidden rounded-4 border border-light shadow-sm bg-white" style="max-width: 650px; display: none;">
                <div class="p-3 border-bottom bg-slate-50 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Hasil Pencarian Langsung</span>
                    <span id="liveSearchCount" class="badge bg-primary text-white rounded-pill" style="font-size: 0.68rem;"></span>
                </div>
                <div id="liveSearchResultsList" class="p-2 d-flex flex-column gap-1" style="max-height: 380px; overflow-y: auto;">
                    {{-- Diisi secara realtime via AJAX --}}
                </div>
            </div>

            {{-- Popular Search Suggestions Chips --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3 flex-wrap small" id="popularChipsContainer">
                <span class="text-secondary me-1" style="font-size: 0.82rem;">Populer:</span>
                <a href="{{ route('public.search') }}?q=liquid" class="badge bg-light text-dark text-decoration-none rounded-pill px-3 py-1.5 border">Liquid</a>
                <a href="{{ route('public.search') }}?q=saltnic" class="badge bg-light text-dark text-decoration-none rounded-pill px-3 py-1.5 border">Saltnic</a>
                <a href="{{ route('public.search') }}?q=pod" class="badge bg-light text-dark text-decoration-none rounded-pill px-3 py-1.5 border">Pod System</a>
                <a href="{{ route('public.search') }}?q=coil" class="badge bg-light text-dark text-decoration-none rounded-pill px-3 py-1.5 border">Coil & Cartridge</a>
            </div>
        </div>
    </div>
</div>

{{-- Logout Form --}}
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

{{-- Offcanvas Categories Drawer --}}
<div class="offcanvas offcanvas-start border-0" tabindex="-1" id="categoryCanvas" style="width: 420px; max-width: 88vw;">
    <div class="offcanvas-header border-bottom py-3.5 px-4 bg-white">
        <h6 class="fw-bold mb-0 text-dark text-uppercase tracking-wider fs-7">
            <i class="fas fa-layer-group text-primary me-2"></i> ALL CATEGORIES
        </h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex p-0 bg-white">
        <div class="category-parent-pane bg-slate-50 border-end border-light">
            <div class="list-group list-group-flush mt-0">
                @foreach($categories->where('parent_id', null) as $index => $cat)
                    <button class="list-group-item list-group-item-action border-0 py-3 px-3.5 small fw-semibold text-start text-dark category-item {{ $index === 0 ? 'active' : '' }}" 
                            data-parent-id="{{ $cat->id }}" 
                            onclick="switchParentCategory('{{ $cat->id }}', this)">
                        <div class="d-flex align-items-center justify-content-between">
                            <span>{{ $cat->nama_kategori }}</span>
                            <i class="fas fa-chevron-right fa-xs text-muted ms-1"></i>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="category-child-pane p-3.5 flex-grow-1 overflow-y-auto">
            <div id="childContainer">
                @foreach($categories->where('parent_id', null) as $index => $cat)
                    <div class="category-child-group {{ $index === 0 ? '' : 'd-none' }}" id="child-group-{{ $cat->id }}">
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom border-light">
                            <h6 class="fw-bold mb-0 text-dark small text-uppercase">{{ $cat->nama_kategori }}</h6>
                            <a href="{{ route('public.category', $cat->slug) }}" class="small text-primary text-decoration-none fw-semibold">
                                View All <i class="fas fa-arrow-right ms-1" style="font-size: 0.68rem;"></i>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-1">
                            @forelse($cat->children as $child)
                                <a href="{{ route('public.category', $child->slug) }}" class="py-2 px-3 rounded-0 text-decoration-none text-dark small hover-bg-slate transition-all">
                                    <i class="fas fa-minus text-muted me-2" style="font-size: 0.65rem;"></i> {{ $child->nama_kategori }}
                                </a>
                            @empty
                                <p class="text-muted small py-3 text-center">No sub-categories found.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.btn-icon-nav, .theme-toggle-btn {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.btn-icon-nav:hover, .theme-toggle-btn:hover {
    transform: translateY(-2px);
    background-color: #e2e8f0 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

body.dark-mode .btn-icon-nav,
body.dark-mode .theme-toggle-btn {
    background-color: #1e293b !important;
}

body.dark-mode .btn-icon-nav:hover,
body.dark-mode .theme-toggle-btn:hover {
    background-color: #334155 !important;
}

.hover-underline:hover {
    text-decoration: underline !important;
}

.fw-black {
    font-weight: 900 !important;
}

.nav-link:hover {
    color: #000000 !important;
    text-decoration: underline;
}

.category-parent-pane {
    width: 170px;
    min-width: 140px;
}

.category-item.active {
    background-color: #ffffff !important;
    color: #000000 !important;
    font-weight: 700 !important;
    border-left: 3px solid #000000 !important;
}

.nav-login-btn {
    transition: all 0.25s ease !important;
    color: #ffffff !important;
}
.nav-login-btn * {
    color: #ffffff !important;
}
.nav-login-btn:hover {
    background: linear-gradient(135deg, #078991 0%, #0369a1 100%) !important;
    box-shadow: 0 4px 14px rgba(9, 175, 185, 0.35) !important;
    transform: translateY(-1px);
    color: #ffffff !important;
}

.nav-register-btn {
    transition: all 0.25s ease !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #334155 !important;
}
.nav-register-btn:hover {
    border-color: #09afb9 !important;
    color: #09afb9 !important;
    background-color: #f0fdfa !important;
}

/* DARK MODE STYLING FOR NAVBAR DROPDOWN & BUTTONS */
body.dark-mode .dropdown-menu,
html[data-bs-theme="dark"] .dropdown-menu {
    background-color: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

body.dark-mode .nav-register-btn,
html[data-bs-theme="dark"] .nav-register-btn {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}

body.dark-mode .nav-register-btn:hover,
html[data-bs-theme="dark"] .nav-register-btn:hover {
    border-color: #00d4ff !important;
    color: #00d4ff !important;
    background-color: rgba(9, 175, 185, 0.2) !important;
}

/* ===================================================
   DESKTOP FLOATING MEGA MENU (LUXURY GRID) STYLES
   =================================================== */
.desktop-mega-menu-panel {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    width: 860px;
    max-width: 92vw;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    z-index: 99999;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.22s;
    pointer-events: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.04);
}

.desktop-category-nav-item:hover .desktop-mega-menu-panel,
.desktop-mega-menu-panel.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}

.desktop-category-nav-item:hover .mega-chevron-icon,
.desktop-category-nav-item.is-active .mega-chevron-icon {
    transform: rotate(180deg);
}

.mega-menu-header {
    background-color: #f8fafc;
}

.mega-grid-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}

.mega-grid-icon-box {
    background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    transition: all 0.22s ease;
}

.mega-grid-card:hover {
    transform: translateY(-2px);
    border-color: #09afb9 !important;
    background-color: #f0fdfa !important;
    box-shadow: 0 8px 16px -3px rgba(9, 175, 185, 0.18) !important;
}

.mega-grid-card:hover .mega-grid-icon-box {
    background: #e0f2fe !important;
    border-color: #bae6fd !important;
    transform: scale(1.05);
}

.mega-grid-card:hover .mega-card-title {
    color: #09afb9 !important;
}

.mega-grid-card:hover .mega-card-subtitle {
    color: #0284c7 !important;
}

.mega-footer-pill {
    transition: all 0.2s ease;
}

.mega-footer-pill:hover {
    background-color: #e0f2fe !important;
    color: #0369a1 !important;
    transform: translateY(-1px);
}

/* DARK MODE MEGA MENU */
body.dark-mode .desktop-mega-menu-panel,
html[data-bs-theme="dark"] .desktop-mega-menu-panel {
    background-color: #0f172a !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
}

body.dark-mode .mega-menu-header,
html[data-bs-theme="dark"] .mega-menu-header {
    background-color: #0b1120 !important;
    border-color: rgba(255, 255, 255, 0.06) !important;
}

body.dark-mode .mega-grid-card,
html[data-bs-theme="dark"] .mega-grid-card {
    background-color: #1e293b !important;
    border-color: rgba(255, 255, 255, 0.06) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
}

body.dark-mode .mega-grid-card .mega-card-title,
html[data-bs-theme="dark"] .mega-grid-card .mega-card-title {
    color: #f1f5f9 !important;
}

body.dark-mode .mega-grid-card .mega-card-subtitle,
html[data-bs-theme="dark"] .mega-grid-card .mega-card-subtitle {
    color: #94a3b8 !important;
}

body.dark-mode .mega-grid-icon-box,
html[data-bs-theme="dark"] .mega-grid-icon-box {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

body.dark-mode .mega-grid-icon-box img,
html[data-bs-theme="dark"] .mega-grid-icon-box img {
    filter: brightness(0) invert(1) !important;
    opacity: 0.95;
}

body.dark-mode .mega-grid-card:hover,
html[data-bs-theme="dark"] .mega-grid-card:hover {
    background-color: #334155 !important;
    border-color: #00d4ff !important;
    box-shadow: 0 10px 20px -3px rgba(0, 212, 255, 0.25) !important;
}

body.dark-mode .mega-grid-card:hover .mega-card-title,
html[data-bs-theme="dark"] .mega-grid-card:hover .mega-card-title {
    color: #00d4ff !important;
}

body.dark-mode .mega-grid-card:hover .mega-card-subtitle,
html[data-bs-theme="dark"] .mega-grid-card:hover .mega-card-subtitle {
    color: #38bdf8 !important;
}

body.dark-mode .mega-menu-footer,
html[data-bs-theme="dark"] .mega-menu-footer {
    background-color: #0b1120 !important;
    border-color: rgba(255, 255, 255, 0.06) !important;
}

body.dark-mode .mega-footer-pill,
html[data-bs-theme="dark"] .mega-footer-pill {
    background-color: #1e293b !important;
    color: #e2e8f0 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

body.dark-mode .mega-footer-pill:hover,
html[data-bs-theme="dark"] .mega-footer-pill:hover {
    background-color: #334155 !important;
    color: #00d4ff !important;
}
</style>

<script>
// Click toggle for Mega Menu on Desktop
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('desktopMegaMenuTrigger');
    const menu = document.getElementById('desktopMegaMenu');
    const parentNavItem = trigger ? trigger.closest('.desktop-category-nav-item') : null;
    if (!trigger || !menu) return;

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        menu.classList.toggle('is-open');
        if (parentNavItem) parentNavItem.classList.toggle('is-active');
    });

    document.addEventListener('click', function(e) {
        if (menu.classList.contains('is-open')) {
            if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                menu.classList.remove('is-open');
                if (parentNavItem) parentNavItem.classList.remove('is-active');
            }
        }
    });
});

function toggleSearchDrawer() {
    const el = document.getElementById('surfeitSearchCollapse');
    if (!el) return;

    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
        setTimeout(() => {
            const input = document.getElementById('searchInputField');
            if (input) input.focus();
        }, 50);
    } else {
        el.style.display = 'none';
    }
}

document.addEventListener('click', function(e) {
    const searchPop = document.getElementById('surfeitSearchCollapse');
    const searchBtn = e.target.closest('[onclick="toggleSearchDrawer()"]');
    if (searchPop && searchPop.style.display === 'block') {
        if (!searchPop.contains(e.target) && !searchBtn) {
            searchPop.style.display = 'none';
        }
    }
});

// LIVE SEARCH AUTOCOMPLETE DEBOUNCE
let searchDebounceTimer = null;
const searchInputField = document.getElementById('searchInputField');
const liveResultsContainer = document.getElementById('liveSearchResultsContainer');
const liveResultsList = document.getElementById('liveSearchResultsList');
const liveSearchCount = document.getElementById('liveSearchCount');

if (searchInputField && liveResultsContainer && liveResultsList) {
    searchInputField.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchDebounceTimer);

        if (query.length < 2) {
            liveResultsContainer.style.display = 'none';
            liveResultsList.innerHTML = '';
            return;
        }

        // Show loading state
        liveResultsContainer.style.display = 'block';
        liveResultsList.innerHTML = `
            <div class="text-center py-3 text-muted small">
                <i class="fas fa-spinner fa-spin me-1 text-primary"></i> Mencari produk...
            </div>
        `;

        searchDebounceTimer = setTimeout(() => {
            fetch(`{{ route('public.search.live') }}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    liveResultsCount = data.count;
                    liveSearchCount.textContent = `${data.count} Produk`;
                    liveResultsList.innerHTML = data.products.map(p => `
                        <a href="${p.url}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark hover-bg-light transition-all border-bottom border-light-subtle">
                            <div class="rounded-3 overflow-hidden bg-slate-50 flex-shrink-0 d-flex align-items-center justify-content-center border border-light-subtle" style="width: 52px; height: 52px; min-width: 52px;">
                                ${p.image ? `<img src="${p.image}" alt="${p.name}" class="w-100 h-100 object-fit-contain" onerror="this.src='https://placehold.co/100x100?text=Podgasm'">` : `<i class="fas fa-box text-muted opacity-30"></i>`}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="mb-1 fw-bold text-truncate text-dark" style="font-size: 0.85rem; line-height: 1.25;">${p.name}</h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="text-secondary fw-medium" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;">${p.category}</span>
                                    ${p.variant_match ? `
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem; font-weight: 500;">
                                            <i class="fas fa-tag me-1 text-primary"></i>${p.variant_match}
                                        </span>` : ''}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-primary" style="font-size: 0.88rem;">${p.price}</span>
                                    ${p.is_out_of_stock ? `<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Habis</span>` : `<span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Ready</span>`}
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted opacity-40 small ms-auto flex-shrink-0"></i>
                        </a>
                    `).join('') + `
                        <div class="border-top pt-2.5 pb-1 mt-1 text-center bg-slate-50 rounded-bottom">
                            <a href="{{ route('public.search') }}?q=${encodeURIComponent(query)}" class="small fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1.5 py-1 px-3 rounded-pill hover-bg-primary-subtle transition-all">
                                <span>Lihat Semua ${data.count} Hasil Pencarian</span>
                                <i class="fas fa-arrow-right small"></i>
                            </a>
                        </div>
                    `;
                } else {
                    liveSearchCount.textContent = '0 Produk';
                    liveResultsList.innerHTML = `
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-search fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Tidak ada produk yang cocok dengan "<strong>${query}</strong>".</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Live search error:', err);
                liveResultsList.innerHTML = `
                    <div class="text-center py-2 text-danger small">
                        Terjadi kesalahan saat memuat hasil pencarian.
                    </div>
                `;
            });
        }, 280);
    });
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('podgasm_theme', next);
    
    updateThemeUI(next);
}

function updateThemeUI(theme) {
    const icon = document.getElementById('themeToggleIcon');
    if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
        if (icon) {
            icon.className = 'fas fa-sun text-warning';
        }
    } else {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
        if (icon) {
            icon.className = 'fas fa-moon text-dark';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('podgasm_theme') || 'light';
    updateThemeUI(currentTheme);
});

function switchParentCategory(parentId, btn) {
    document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.category-child-group').forEach(group => group.classList.add('d-none'));
    const targetGroup = document.getElementById('child-group-' + parentId);
    if (targetGroup) {
        targetGroup.classList.remove('d-none');
    }
}
</script>