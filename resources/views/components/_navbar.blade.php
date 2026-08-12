{{-- EXACT SURFEIT HEADER & NAVIGATION BAR --}}
<div class="fixed-top shadow-none bg-white" style="z-index: 9999;">
    
    {{-- 1. TOP ANNOUNCEMENT BAR (Sesuai Referensi Gambar Top Bar) --}}
    <div class="top-announcement-bar py-1.5 px-3 border-bottom" style="background-color: #f4f3f0; border-color: #e8e7e3 !important; font-size: 0.8rem;">
        <div class="container-fluid px-lg-5 d-flex justify-content-between align-items-center">
            {{-- Kiri: Teks Miring Informasi --}}
            <div class="text-secondary fst-italic text-truncate" style="font-family: Georgia, 'Times New Roman', serif;">
                Garansi 100% Produk Original & Pengiriman Cepat 21+ ke Seluruh Indonesia
            </div>

            {{-- Kanan: Link Log In & Create Account --}}
            <div class="d-flex align-items-center gap-3 ms-auto small">
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
    <nav class="navbar navbar-expand-lg bg-white border-bottom border-light py-3">
        <div class="container-fluid px-lg-5">
            
            {{-- BRAND LOGO (Gambar Logo Podgasm) --}}
            <a class="navbar-brand me-4 me-xl-5 d-flex align-items-center py-0" href="{{ route('home') }}">
                <img src="{{ asset('PodgasmHome.png') }}" alt="Podgasm Logo" class="navbar-logo" style="height: 72px !important; max-height: 78px !important; width: auto; object-fit: contain;">
            </a>

            {{-- Mobile Burger Toggle --}}
            <button class="navbar-toggler border-0 shadow-none p-0 d-lg-none ms-auto me-3" type="button" data-bs-toggle="collapse" data-bs-target="#surfeitNavMenu">
                <i class="fas fa-bars fs-4 text-dark"></i>
            </button>

            {{-- NAV MENU LINKS (Horizontal Sejajar Sesuai Referensi) --}}
            <div class="collapse navbar-collapse" id="surfeitNavMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center gap-1 gap-xl-3" style="font-size: 0.88rem;">
                    <li class="nav-item">
                        <button class="nav-link text-dark fw-semibold border-0 bg-transparent py-1 text-nowrap" type="button" data-bs-toggle="offcanvas" data-bs-target="#categoryCanvas">
                            Categories <i class="fas fa-chevron-down ms-1 opacity-50" style="font-size: 0.65rem;"></i>
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
            <div class="d-flex align-items-center gap-2 ms-auto">
                {{-- Search Icon Trigger --}}
                <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none" 
                        type="button" 
                        onclick="toggleSearchDrawer()" 
                        title="Search"
                        style="width: 42px; height: 42px; background-color: #f8fafc;">
                    <i class="fas fa-search text-dark" style="font-size: 0.95rem;"></i>
                </button>

                {{-- Wishlist Ribbon Icon --}}
                <a href="{{ route('wishlist.index') }}" 
                   class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                   title="Wishlist"
                   style="width: 42px; height: 42px; background-color: #f8fafc;">
                    <i class="far fa-heart text-danger" style="font-size: 1rem;"></i>
                    @if(($wishlistCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger text-white border border-light" style="font-size: 0.58rem;">
                            {{ $wishlistCount }}
                        </span>
                    @endif
                </a>

                {{-- Shopping Bag Icon --}}
                <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#offcanvasCart" 
                        onclick="loadOffcanvasCart()" 
                        title="Shopping Bag"
                        style="width: 42px; height: 42px; background-color: #f8fafc;">
                    <i class="fas fa-shopping-bag text-primary" style="font-size: 0.95rem;"></i>
                    @if(($cartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-primary text-white border border-light" style="font-size: 0.58rem;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </button>

                {{-- Account Dropdown Icon Button --}}
                <div class="dropdown me-1">
                    <button class="btn btn-icon-nav rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-none position-relative" 
                            type="button" 
                            id="accountDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false" 
                            title="Akun Saya"
                            style="width: 42px; height: 42px; background-color: #f8fafc;">
                        <i class="far fa-user text-dark" style="font-size: 0.95rem;"></i>
                        @auth
                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
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
                <button type="button" 
                        class="btn theme-toggle-btn rounded-circle d-inline-flex align-items-center justify-content-center shadow-none border-0 ms-1" 
                        id="themeToggleBtn" 
                        onclick="toggleTheme()" 
                        title="Ganti Tema (Gelap/Terang)" 
                        style="width: 42px; height: 42px; background-color: #f8fafc;">
                    <i class="fas fa-moon text-dark" id="themeToggleIcon" style="font-size: 0.95rem;"></i>
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
            
            {{-- Popular Search Suggestions Chips --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3 flex-wrap small">
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
</style>

<script>
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