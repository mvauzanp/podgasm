<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Podgasm - Warehouse Management</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('LogoPodgasm.png') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *{
            font-family: 'Poppins', sans-serif;
        }

        body{
            display: flex;
            min-height: 100vh;
            background: #f5f7fb;
            overflow-x: hidden;
        }

        /* ================= SIDEBAR ================= */

        .sidebar{
            width: 270px;
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            color: white;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 4px 0 20px rgba(0,0,0,0.08);
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .logo-area{
            padding: 28px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .logo-title{
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .logo-title span{
            color: #3b82f6;
        }

        .logo-subtitle{
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 4px;
        }

        .nav-menu{
            padding: 18px 14px;
            flex: 1;
            overflow-y: auto;
        }

        /* Styling scrollbar nav menu */
        .nav-menu::-webkit-scrollbar {
            width: 5px;
        }
        .nav-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        .nav-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .sidebar .nav-link{
            color: #d1d5db;
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 6px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
        }

        .sidebar .nav-link:hover{
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active{
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            box-shadow: 0 6px 16px rgba(37,99,235,0.3);
        }

        .sidebar .collapse .nav-link.active{
            background: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .sidebar .nav-link i{
            width: 22px;
        }

        /* Collapsible Chevron transition */
        .transition-icon {
            transition: transform 0.2s ease;
        }
        .sidebar .nav-link[aria-expanded="true"] .transition-icon {
            transform: rotate(180deg);
        }

        .badge-notif{
            font-size: 0.72rem;
            padding: 5px 9px;
        }

        .logout-area{
            padding: 20px 14px;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .logout-btn{
            color: #f87171 !important;
            border-radius: 14px;
            padding: 12px 16px;
        }

        .logout-btn:hover{
            background: rgba(248,113,113,0.1);
        }

        /* ================= CONTENT ================= */

        .content-area{
            flex: 1;
            padding: 24px;
        }

        /* ================= NAVBAR ================= */

        .navbar-admin{
            background: white;
            border-radius: 20px;
            padding: 16px 22px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eef1f5;
        }

        .welcome-text h5{
            margin: 0;
            font-weight: 700;
            color: #111827;
        }

        .welcome-text p{
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .admin-profile{
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f9fafb;
            padding: 10px 16px;
            border-radius: 14px;
        }

        .admin-profile i{
            font-size: 1.8rem;
            color: #2563eb;
        }

        /* ================= ALERT ================= */

        .alert{
            border: none;
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        .alert-danger{
            background: #fef2f2;
            color: #991b1b;
        }

        .alert-success{
            background: #ecfdf5;
            color: #065f46;
        }

        /* ================= CONTENT CARD ================= */

        .content-wrapper{
            margin-top: 22px;
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            min-height: 500px;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 992px){

            .sidebar{
                width: 85px;
            }

            .logo-title,
            .logo-subtitle,
            .sidebar .nav-link span,
            .sidebar .nav-link .menu-text,
            .badge-notif{
                display: none;
            }

            .sidebar .nav-link{
                justify-content: center;
            }

            .content-area{
                padding: 16px;
            }
        }
    </style>
</head>
<body>

    {{-- ================= SIDEBAR ================= --}}
    <div class="sidebar">

        <div class="logo-area text-center">
            <div class="logo-title">
                PODGASM <span>HQ</span>
            </div>

            <div class="logo-subtitle">
                Warehouse Management System
            </div>
        </div>

        <div class="nav-menu">

            {{-- SECTION: UTAMA --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Menu Utama</div>

            {{-- Dashboard --}}
            <a class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"
               href="/admin/dashboard">
                <div>
                    <i class="fas fa-chart-pie me-2"></i>
                    <span class="menu-text">Dashboard</span>
                </div>
            </a>

            {{-- Kelola Banner --}}
            <a class="nav-link {{ request()->is('admin/banners*') ? 'active' : '' }}"
               href="{{ route('admin.banners.index') }}">
                <div>
                    <i class="fas fa-images me-2"></i>
                    <span class="menu-text">Kelola Banner</span>
                </div>
            </a>

            {{-- SECTION: GROSIR & B2B --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mt-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Grosir & B2B</div>
            
            @php
                $isB2BGroupActive = request()->is('admin/b2b-registrations*') || request()->is('admin/b2b-prices*') || request()->is('admin/stock-requests*');
            @endphp
            <div>
                <a class="nav-link {{ $isB2BGroupActive ? 'active' : '' }}" 
                   data-bs-toggle="collapse" 
                   href="#collapseB2B" 
                   role="button" 
                   aria-expanded="{{ $isB2BGroupActive ? 'true' : 'false' }}" 
                   aria-controls="collapseB2B">
                    <div>
                        <i class="fas fa-users-gear me-2"></i>
                        <span class="menu-text">Kemitraan B2B</span>
                    </div>
                    <i class="fas fa-chevron-down transition-icon ms-2" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ $isB2BGroupActive ? 'show' : '' }}" id="collapseB2B">
                    <div class="ps-3 border-start border-white border-opacity-10 ms-3 mt-1">
                        <a class="nav-link {{ request()->is('admin/b2b-registrations*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.b2b.list') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-id-card me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">List User B2B</span>
                            </div>
                            @if(isset($pendingB2BCount) && $pendingB2BCount > 0)
                                <span class="badge bg-warning text-dark rounded-pill badge-notif">
                                    {{ $pendingB2BCount }}
                                </span>
                            @endif
                        </a>
                        <a class="nav-link {{ request()->is('admin/b2b-prices*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.b2b-prices.index') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-hand-holding-dollar me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Setting Harga B2B</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/stock-requests*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.stock-requests.index') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-truck-loading me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Permintaan Stok</span>
                            </div>
                            @if(isset($pendingRequestCount) && $pendingRequestCount > 0)
                                <span class="badge bg-danger rounded-pill badge-notif">
                                    {{ $pendingRequestCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

            {{-- SECTION: STOK & INVENTORI --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mt-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Stok & Inventori</div>

            @php
                $isInventoryGroupActive = request()->is('admin/categories*') || request()->is('admin/products*') || request()->is('admin/restocks*') || request()->is('admin/safety-stock*') || request()->is('admin/reports*');
            @endphp
            <div>
                <a class="nav-link {{ $isInventoryGroupActive ? 'active' : '' }}" 
                   data-bs-toggle="collapse" 
                   href="#collapseInventory" 
                   role="button" 
                   aria-expanded="{{ $isInventoryGroupActive ? 'true' : 'false' }}" 
                   aria-controls="collapseInventory">
                    <div>
                        <i class="fas fa-boxes-stacked me-2"></i>
                        <span class="menu-text">Manajemen Stok</span>
                    </div>
                    <i class="fas fa-chevron-down transition-icon ms-2" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ $isInventoryGroupActive ? 'show' : '' }}" id="collapseInventory">
                    <div class="ps-3 border-start border-white border-opacity-10 ms-3 mt-1">
                        <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }} py-2 mb-1" href="/admin/categories" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-tags me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Manajemen Kategori</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }} py-2 mb-1" href="/admin/products" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-boxes-stacked me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Produk & Varian</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/restocks*') ? 'active' : '' }} py-2 mb-1" href="/admin/restocks" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-file-import me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Barang Masuk</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/safety-stock*') ? 'active' : '' }} py-2 mb-1" href="/admin/safety-stock" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-calculator me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Safety Stock</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }} py-2 mb-1" href="/admin/reports/inventory" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-file-chart-column me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Laporan Stok</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- SECTION: RITEL & B2C --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mt-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Ritel & B2C</div>

            @php
                $isB2CGroupActive = request()->is('admin/orders*') || request()->is('admin/vouchers*') || request()->is('admin/cs-chats*');
            @endphp
            <div>
                <a class="nav-link {{ $isB2CGroupActive ? 'active' : '' }}" 
                   data-bs-toggle="collapse" 
                   href="#collapseB2C" 
                   role="button" 
                   aria-expanded="{{ $isB2CGroupActive ? 'true' : 'false' }}" 
                   aria-controls="collapseB2C">
                    <div>
                        <i class="fas fa-shopping-bag me-2"></i>
                        <span class="menu-text">Pelanggan Ritel</span>
                    </div>
                    <i class="fas fa-chevron-down transition-icon ms-2" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ $isB2CGroupActive ? 'show' : '' }}" id="collapseB2C">
                    <div class="ps-3 border-start border-white border-opacity-10 ms-3 mt-1">
                        <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.orders.index') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-shopping-cart me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Pesanan</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/vouchers*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.vouchers.index') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-ticket-alt me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Voucher B2C</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/cs-chats*') ? 'active' : '' }} py-2 mb-1" href="{{ route('admin.cs-chats.index') }}" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-comments me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Chat CS</span>
                            </div>
                            @if(isset($unreadCsCount) && $unreadCsCount > 0)
                                <span class="badge bg-danger rounded-pill badge-notif">
                                    {{ $unreadCsCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Logout --}}
        <div class="logout-area">
            <form action="/logout" method="POST">
                @csrf

                <button type="submit"
                        class="nav-link logout-btn w-100 border-0 bg-transparent text-start">

                    <div>
                        <i class="fas fa-right-from-bracket me-2"></i>
                        <span class="menu-text">Keluar</span>
                    </div>

                </button>
            </form>
        </div>

    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="content-area">

        {{-- Navbar --}}
        <nav class="navbar-admin d-flex justify-content-between align-items-center">

            <div class="welcome-text">
                <h5>Gudang Pusat Podgasm Surabaya</h5>
                <p>Sistem Monitoring Persediaan & Distribusi Barang</p>
            </div>

            <div class="admin-profile">
                <i class="fas fa-circle-user"></i>

                <div>
                    <div class="fw-semibold">Admin Utama</div>
                    <small class="text-muted">Warehouse Administrator</small>
                </div>
            </div>

        </nav>

        {{-- Alert Error --}}
        @if($errors->any())
            <div class="alert alert-danger mt-4">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Alert Success --}}
        @if(session('success'))
            <div class="alert alert-success mt-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Content --}}
        <div class="content-wrapper">

            @yield('content_admin')

        </div>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>
</html>