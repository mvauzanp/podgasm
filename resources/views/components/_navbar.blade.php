{{-- NAVBAR UTAMA - MODERN AESTHETIC --}}
<nav class="navbar navbar-expand-lg navbar-light sticky-top" style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; width: 100%; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 1rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
    <div class="container">
        {{-- LEFT: Burger & Brand --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light me-1 navbar-burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#categoryCanvas" style="border: none; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                <i class="fas fa-bars" style="color: #2596be;"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center mb-0 ms-3" href="{{ route('home') }}">
                <img src="{{ asset('PodgasmHome.png') }}" alt="Podgasm Logo" class="navbar-logo">
            </a>
        </div>

        {{-- CENTER: Search Bar --}}
        <form class="d-none d-lg-flex grow mx-4" action="{{ route('public.search') }}" method="GET" style="max-width: 400px;">
            <div class="input-group" style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <input type="text" class="form-control" placeholder="🔍 Cari device, liquid..." name="q" style="border: none; padding: 12px 18px; font-size: 0.95rem; background-color: rgba(255,255,255,0.95);">
                <button class="btn" type="submit" style="background: linear-gradient(135deg, #2596be 0%, #00d4ff 100%); border: none; color: white; padding: 12px 18px; transition: all 0.3s ease;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        {{-- RIGHT: Icons & User Menu --}}
        <div class="d-flex align-items-center gap-1">
            {{-- Mobile Search Toggle --}}
            <button class="btn navbar-icon d-lg-none position-relative" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearchCollapse" aria-expanded="false" aria-controls="mobileSearchCollapse" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; color: white; border: none; background: transparent;">
                <i class="fas fa-search"></i>
            </button>

            {{-- Wishlist --}}
            <a href="{{ route('wishlist.index') }}" class="btn navbar-icon position-relative" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; color: white; border: none;">
                <i class="fas fa-heart"></i>
                @if(($wishlistCount ?? 0) > 0)
                <span class="badge badge-counter">
                    {{ $wishlistCount }}
                </span>
                @endif
            </a>

            {{-- Cart --}}
            <button class="btn navbar-icon position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" onclick="loadOffcanvasCart()" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; color: white; border: none; background: transparent;">
                <i class="fas fa-shopping-cart"></i>
                @if(($cartCount ?? 0) > 0)
                <span class="badge badge-counter">
                    {{ $cartCount }}
                </span>
                @endif
            </button>

            {{-- User Dropdown --}}
            @auth
            <div class="dropdown ms-1">
                <button class="btn navbar-icon" data-bs-toggle="dropdown" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; color: white; border: none;">
                    <i class="fas fa-user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 p-0 rounded-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); min-width: 280px; animation: slideDown 0.3s ease;">
                    <li class="px-4 py-3 border-bottom" style="background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);">
                        <small class="text-muted d-block" style="font-size: 0.8rem;">Login sebagai</small>
                        <span class="fw-bold text-primary" style="font-size: 1.1rem;">{{ auth()->user()->name }}</span>
                    </li>
                    <li><a class="dropdown-item py-3 px-4" href="{{ route('profile.show') }}" style="transition: all 0.3s ease; border-radius: 0;"><i class="fas fa-user-circle me-3" style="color: #2596be; width: 20px;"></i> Profile</a></li>
                    <li><a class="dropdown-item py-3 px-4" href="{{ route('order.history') }}" style="transition: all 0.3s ease; border-radius: 0;"><i class="fas fa-clock me-3" style="color: #2596be; width: 20px;"></i> Riwayat Belanja</a></li>
                    <li><hr class="dropdown-divider my-2"></li>
                    <li><button class="dropdown-item py-3 px-4 text-danger w-100 text-start border-0 bg-transparent" onclick="document.getElementById('logoutForm').submit()" style="transition: all 0.3s ease;"><i class="fas fa-sign-out-alt me-3" style="width: 20px;"></i> Logout</button></li>
                </ul>
            </div>
            @else
            <div class="ms-2">
                <a href="/login" class="btn btn-sm rounded-pill px-4" style="background: linear-gradient(135deg, #2596be 0%, #00d4ff 100%); color: white; border: none; padding: 10px 20px !important; font-weight: 600; transition: all 0.3s ease;">Login</a>
            </div>
            @endauth
        </div>
    </div>

    {{-- COLLAPSIBLE MOBILE SEARCH BAR --}}
    <div class="collapse d-lg-none w-100" id="mobileSearchCollapse" style="background: linear-gradient(135deg, #16213e 0%, #1a1a2e 100%); border-top: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="px-3 py-2">
            <form action="{{ route('public.search') }}" method="GET">
                <div class="input-group" style="border-radius: 50px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.15);">
                    <input type="text" class="form-control" placeholder="🔍 Cari device, liquid..." name="q" style="border: none; padding: 10px 18px; font-size: 0.9rem; background-color: rgba(255,255,255,0.95);">
                    <button class="btn" type="submit" style="background: linear-gradient(135deg, #0d6efd 0%, #00d4ff 100%); border: none; color: white; padding: 10px 18px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</nav>

{{-- OFFCANVAS SIDEBAR - MODERN DESIGN --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="categoryCanvas" style="width: 500px; max-width: 90vw;">
    <div class="offcanvas-header border-bottom py-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: none !important;">
        <h5 class="fw-bold mb-0" style="color: white; font-size: 1.4rem;">📂 KATEGORI</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex p-0">
        {{-- Area Kategori Parent (Kiri) --}}
        <div class="category-parent-pane">
            <div class="list-group list-group-flush mt-0">
                @forelse(($categories ?? collect())->where('parent_id', null) as $parent)
                    <div class="list-group-item list-group-item-action border-0 py-3 category-item px-3" 
                         style="cursor: pointer; transition: all 0.3s ease; border-left: 4px solid transparent; font-weight: 500; color: #333;" 
                         data-id="{{ $parent->id }}">
                        {{ strtoupper($parent->nama_kategori) }}
                    </div>
                @empty
                    <p class="text-muted p-3 text-center">Tidak ada kategori</p>
                @endforelse
            </div>
        </div>

        {{-- Area Kategori Child (Kanan) --}}
        <div class="grow p-4 bg-white category-child-pane" id="childContainer">
            <div class="text-center mt-5 text-muted">
                <i class="fas fa-arrow-left fa-2x mb-3 d-block opacity-25"></i>
                <p style="color: #666; font-size: 0.95rem;">Pilih kategori utama untuk melihat isi koleksi</p>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT KHUSUS NAVBAR - ENHANCED --}}
<script>
    const categoriesData = @json($categories ?? collect());

    // Navbar icon hover effects
    document.querySelectorAll('.navbar-icon').forEach(icon => {
        icon.addEventListener('mouseover', function() {
            this.style.background = 'rgba(255,255,255,0.2)';
            this.style.transform = 'scale(1.08)';
        });
        icon.addEventListener('mouseout', function() {
            this.style.background = 'transparent';
            this.style.transform = 'scale(1)';
        });
    });

    // Navbar burger hover effect
    document.querySelector('.navbar-burger').addEventListener('mouseover', function() {
        this.style.background = 'rgba(255,255,255,0.15)';
        this.style.transform = 'scale(1.08)';
    });
    document.querySelector('.navbar-burger').addEventListener('mouseout', function() {
        this.style.background = 'rgba(255,255,255,1)';
        this.style.transform = 'scale(1)';
    });

    // Category menu interactivity
    document.querySelectorAll('.category-item').forEach(item => {
        item.addEventListener('click', function () {
            // Hapus status aktif dari semua item kiri
            document.querySelectorAll('.category-item').forEach(el => {
                el.style.background = 'transparent';
                el.style.color = '#333';
                el.style.borderLeft = '4px solid transparent';
                el.style.fontWeight = '500';
            });

            // Tambah status aktif ke yang baru saja diklik
            this.style.background = 'linear-gradient(135deg, rgba(37, 150, 190, 0.1) 0%, rgba(0, 212, 255, 0.08) 100%)';
            this.style.color = '#2596be';
            this.style.borderLeft = '4px solid #2596be';
            this.style.fontWeight = '700';

            const parentId = this.dataset.id;
            const parent = categoriesData.find(c => c.id == parentId);
            const children = categoriesData.filter(c => c.parent_id == parentId);

            let html = `<h5 class="fw-bold mb-4 text-dark border-bottom pb-3" style="font-size: 1.2rem; color: #1a1a2e; border-color: rgba(37, 150, 190, 0.2) !important;">${parent.nama_kategori}</h5>`;

            if (children.length > 0) {
                html += `<div class="row g-2">`;
                children.forEach((child, index) => {
                    html += `
                        <div class="col-12" style="animation: fadeIn 0.3s ease ${index * 0.05}s;">
                            <a href="/category/${child.slug}" class="text-decoration-none text-muted p-3 d-block rounded-3" style="transition: all 0.3s ease; background: transparent; border-left: 3px solid transparent; margin-left: -3px;">
                                <i class="fas fa-chevron-right me-2 small opacity-50"></i>
                                <span style="color: #333; font-weight: 500;">${child.nama_kategori}</span>
                            </a>
                        </div>`;
                });
                html += `</div>`;
            } else {
                html += `
                    <div class="text-center py-5">
                        <p class="text-muted">Tidak ada sub-kategori.</p>
                        <a href="/category/${parent.slug}" class="btn btn-sm rounded-pill mt-2" style="background: linear-gradient(135deg, #2596be 0%, #00d4ff 100%); color: white; border: none; padding: 8px 20px; transition: all 0.3s ease;">Lihat Semua</a>
                    </div>`;
            }

            document.getElementById('childContainer').innerHTML = html;

            // Add hover effects to child items
            document.querySelectorAll('#childContainer a').forEach(link => {
                link.addEventListener('mouseover', function() {
                    this.style.background = 'rgba(37, 150, 190, 0.08)';
                    this.style.borderLeft = '3px solid #2596be';
                    this.style.color = '#2596be';
                });
                link.addEventListener('mouseout', function() {
                    this.style.background = 'transparent';
                    this.style.borderLeft = '3px solid transparent';
                    this.style.color = '#666';
                });
            });
        });
    });

    // Fetch Offcanvas Cart
    function loadOffcanvasCart() {
        const offcanvasBody = document.getElementById('offcanvasCartBody');
        offcanvasBody.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        fetch('{{ url("cart/offcanvas") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if(response.status === 401) {
                return '<div class="p-5 text-center"><i class="fas fa-lock fa-3x text-muted mb-3"></i><h5>Silakan login</h5><p class="text-muted">Anda harus login untuk melihat keranjang.</p><a href="/login" class="btn btn-primary rounded-pill mt-2">Login Sekarang</a></div>';
            }
            return response.text();
        })
        .then(html => {
            offcanvasBody.innerHTML = html;
        })
        .catch(error => {
            offcanvasBody.innerHTML = '<div class="p-5 text-center text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Gagal memuat keranjang. Silakan coba lagi.</p></div>';
        });
    }

    // Programmatically open offcanvas
    function openOffcanvasCart() {
        loadOffcanvasCart();
        const offcanvasElement = document.getElementById('offcanvasCart');
        let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
        if (!bsOffcanvas) {
            bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
        }
        bsOffcanvas.show();
    }
</script>

{{-- Hidden Logout Form --}}
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
    /* === ANIMATIONS === */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* === NAVBAR BADGE === */
    .badge-counter {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
        color: white;
        position: absolute;
        top: -8px;
        right: -8px;
        padding: 0.4em 0.55em;
        border: 2px solid white;
        border-radius: 50%;
        font-size: 0.7rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        }
        50% {
            box-shadow: 0 2px 15px rgba(220, 53, 69, 0.6);
        }
    }

    /* === DROPDOWN ITEM HOVER === */
    .dropdown-item {
        transition: all 0.3s ease;
        color: #333;
        font-weight: 500;
    }

    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(37, 150, 190, 0.1) 0%, rgba(0, 212, 255, 0.08) 100%);
        color: #2596be;
        padding-left: 2rem;
    }

    .dropdown-item.text-danger:hover {
        background: rgba(220, 53, 69, 0.1) !important;
        color: #dc3545 !important;
    }

    /* === SEARCH BUTTON HOVER === */
    .input-group button:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(37, 150, 190, 0.3);
    }

    /* === OFFCANVAS CUSTOM === */
    .offcanvas {
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(-100%);
        }
        to {
            transform: translateX(0);
        }
    }

    /* === MOBILE SEARCH BAR STYLING === */
    #mobileSearchCollapse {
        transition: all 0.3s ease;
    }

    /* === RESPONSIVE NAVBAR ELEMENTS === */
    .navbar-logo {
        height: 58px;
        width: auto;
        max-height: 60px;
        object-fit: contain;
        transition: all 0.3s ease;
    }

    @media (max-width: 991px) {
        .navbar-brand {
            font-size: 1.5rem !important;
        }
    }

    @media (max-width: 768px) {
        .navbar-logo {
            height: 42px !important;
            max-height: 45px !important;
        }
        .navbar-burger, .navbar-icon {
            width: 38px !important;
            height: 38px !important;
            border-radius: 10px !important;
        }
        .navbar-burger i, .navbar-icon i {
            font-size: 0.95rem !important;
        }
        .navbar {
            padding: 0.5rem 0 !important;
        }
        .navbar .btn-sm.rounded-pill {
            padding: 6px 14px !important;
            font-size: 0.8rem !important;
        }
    }

    /* === CATEGORY SIDEBAR RESPONSIVE === */
    .category-parent-pane {
        width: 180px;
        min-width: 150px;
        background: linear-gradient(180deg, #f8f9fa 0%, #e8eef5 100%);
        border-right: 2px solid rgba(37, 150, 190, 0.1);
    }
    
    .category-child-pane {
        flex-grow: 1;
    }

    @media (max-width: 576px) {
        .category-parent-pane {
            width: 130px !important;
            min-width: 120px !important;
        }
        .category-item {
            padding: 12px 10px !important;
            font-size: 0.85rem !important;
        }
        .category-child-pane {
            padding: 1rem !important;
        }
        #childContainer h5 {
            font-size: 1rem !important;
            margin-bottom: 1rem !important;
        }
        #childContainer a {
            padding: 8px !important;
            font-size: 0.85rem !important;
        }
    }
</style>