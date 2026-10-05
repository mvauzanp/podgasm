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
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        html {
            font-size: 13.5px;
            -webkit-text-size-adjust: 100%;
        }

        @media (min-width: 1440px) {
            html {
                font-size: 14px;
            }
        }

        @media (max-width: 991px) {
            html {
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            html {
                font-size: 12.5px;
            }
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 0.88rem;
            line-height: 1.45;
            overflow-x: hidden;
            position: relative;
        }

        /* ================= SIDEBAR ================= */

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: white;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logo-area {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .logo-title {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo-title span {
            color: #38bdf8;
        }

        .logo-subtitle {
            font-size: 0.76rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .nav-menu {
            padding: 12px 10px;
            flex: 1;
            overflow-y: auto;
        }

        /* Styling scrollbar nav menu */
        .nav-menu::-webkit-scrollbar {
            width: 4px;
        }
        .nav-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        .nav-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 3px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(3px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            color: white;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .sidebar .collapse .nav-link.active {
            background: rgba(56, 189, 248, 0.15) !important;
            color: #38bdf8 !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .sidebar .nav-link i {
            width: 20px;
            font-size: 0.9rem;
        }

        /* Collapsible Chevron transition */
        .transition-icon {
            transition: transform 0.2s ease;
        }
        .sidebar .nav-link[aria-expanded="true"] .transition-icon {
            transform: rotate(180deg);
        }

        .badge-notif {
            font-size: 0.65rem;
            padding: 3px 7px;
        }

        .logout-area {
            padding: 12px 14px;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .logout-btn {
            color: #f87171 !important;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .logout-btn:hover {
            background: rgba(248,113,113,0.12);
        }

        /* ================= CONTENT ================= */

        .content-area {
            flex: 1;
            min-width: 0;
            padding: 16px 20px;
            overflow-x: hidden;
        }

        /* ================= NAVBAR ================= */

        .navbar-admin {
            background: white;
            border-radius: 12px;
            padding: 10px 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        .welcome-text h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1rem;
            color: #0f172a;
        }

        .welcome-text p {
            margin: 0;
            color: #64748b;
            font-size: 0.78rem;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 6px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .admin-profile i {
            font-size: 1.4rem;
            color: #0284c7;
        }

        /* ================= ALERT ================= */

        .alert {
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            font-size: 0.85rem;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        /* ================= CONTENT CARD ================= */

        .content-wrapper {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            min-height: 450px;
        }

        /* Card overrides for admin */
        .card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .card-header {
            padding: 12px 16px;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 16px;
        }

        /* Table overrides for admin */
        .table {
            font-size: 0.84rem;
        }

        .table th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
            color: #475569;
            padding: 10px 12px;
        }

        .table td {
            padding: 10px 12px;
            vertical-align: middle;
        }

        /* Backdrop for mobile drawer */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 991px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
                z-index: 1050;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content-area {
                padding: 12px;
                width: 100%;
            }

            .content-wrapper {
                padding: 12px;
                border-radius: 12px;
            }

            .welcome-text h5 {
                font-size: 0.92rem;
            }
            .welcome-text p {
                display: none;
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

            {{-- Pesanan Masuk (Order Management) --}}
            <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}"
               href="{{ route('admin.orders.index') }}">
                <div>
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span class="menu-text">Pesanan Masuk</span>
                </div>
            </a>

            {{-- Integrasi Olsera POS & Stok --}}
            <a class="nav-link {{ request()->is('admin/olsera*') ? 'active' : '' }}"
               href="{{ route('admin.olsera.index') }}">
                <div>
                    <i class="fas fa-cash-register me-2"></i>
                    <span class="menu-text">Integrasi Olsera POS</span>
                </div>
                <span class="badge bg-primary rounded-pill badge-notif" style="font-size: 0.65rem;">API</span>
            </a>

            {{-- Monitoring Queue & Failed Jobs --}}
            <a class="nav-link {{ request()->is('admin/failed-jobs*') ? 'active' : '' }}"
               href="{{ route('admin.failed-jobs.index') }}">
                <div>
                    <i class="fas fa-microchip me-2"></i>
                    <span class="menu-text">Monitoring Queue</span>
                </div>
                @php
                    $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
                @endphp
                @if($failedCount > 0)
                    <span class="badge bg-danger rounded-pill badge-notif" style="font-size: 0.65rem;">{{ $failedCount }} Gagal</span>
                @else
                    <span class="badge bg-success-subtle text-success rounded-pill badge-notif" style="font-size: 0.65rem;">OK</span>
                @endif
            </a>

            {{-- SECTION: INVENTORI & PRODUK --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mt-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Stok & Inventori</div>

            @php
                $isInventoryGroupActive = request()->is('admin/categories*') || request()->is('admin/products*') || request()->is('admin/safety-stock*') || request()->is('admin/reports*');
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
                        <span class="menu-text">Katalog & Stok</span>
                    </div>
                    <i class="fas fa-chevron-down transition-icon ms-2" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse {{ $isInventoryGroupActive ? 'show' : '' }}" id="collapseInventory">
                    <div class="ps-3 border-start border-white border-opacity-10 ms-3 mt-1">
                        <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }} py-2 mb-1" href="/admin/products" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-box-open me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Produk & Varian</span>
                            </div>
                        </a>
                        <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }} py-2 mb-1" href="/admin/categories" style="font-size: 0.88rem; background: transparent; box-shadow: none;">
                            <div>
                                <i class="fas fa-tags me-2" style="font-size: 0.85rem;"></i>
                                <span class="menu-text">Kategori (Olsera)</span>
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

            {{-- SECTION: HARGA & PROMOSI --}}
            <div class="menu-category-title text-muted small fw-bold px-3 mt-3 mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; opacity: 0.75;">Harga & Promosi</div>

            {{-- Setting Diskon Grosir Tier --}}
            <a class="nav-link {{ request()->is('admin/b2b-prices*') ? 'active' : '' }}"
               href="{{ route('admin.b2b-prices.index') }}">
                <div>
                    <i class="fas fa-hand-holding-dollar me-2"></i>
                    <span class="menu-text">Diskon Grosir (Tier)</span>
                </div>
            </a>

            {{-- Voucher Belanja --}}
            <a class="nav-link {{ request()->is('admin/vouchers*') ? 'active' : '' }}"
               href="{{ route('admin.vouchers.index') }}">
                <div>
                    <i class="fas fa-ticket-alt me-2"></i>
                    <span class="menu-text">Voucher Diskon</span>
                </div>
            </a>

            {{-- Banner Promo --}}
            <a class="nav-link {{ request()->is('admin/banners*') ? 'active' : '' }}"
               href="{{ route('admin.banners.index') }}">
                <div>
                    <i class="fas fa-images me-2"></i>
                    <span class="menu-text">Banner Promo</span>
                </div>
            </a>

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

    {{-- Mobile Sidebar Backdrop --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- ================= CONTENT ================= --}}
    <div class="content-area">

        {{-- Navbar --}}
        <nav class="navbar-admin d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">
                {{-- Hamburger Button for Mobile / Tablet --}}
                <button type="button" class="btn btn-light d-lg-none me-2.5 border px-2.5 py-1.5 shadow-none" id="sidebarToggleBtn" style="border-radius: 8px;" title="Toggle Menu">
                    <i class="fas fa-bars text-dark"></i>
                </button>

                <div class="welcome-text">
                    <h5>Gudang Pusat Podgasm</h5>
                    <p>Sistem Monitoring Persediaan & Distribusi Barang</p>
                </div>
            </div>

            <div class="admin-profile">
                <i class="fas fa-circle-user"></i>

                <div>
                    <div class="fw-semibold small">{{ Auth::user()->name ?? 'Admin Utama' }}</div>
                    <small class="text-muted" style="font-size: 0.72rem;">{{ ucfirst(Auth::user()->role ?? 'Admin') }} Podgasm HQ</small>
                </div>
            </div>

        </nav>

        {{-- Alert Error --}}
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Alert Success --}}
        @if(session('success'))
            <div class="alert alert-success mb-3">
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

    {{-- Mobile Sidebar Drawer Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    backdrop.classList.toggle('active');
                });

                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    backdrop.classList.remove('active');
                });
            }
        });
    </script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>
</html>