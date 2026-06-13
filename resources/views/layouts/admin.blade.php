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
        }

        .logo-area{
            padding: 28px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
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
        }

        .nav-link{
            color: #d1d5db;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 8px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
        }

        .nav-link:hover{
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(4px);
        }

        .nav-link.active{
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            box-shadow: 0 6px 16px rgba(37,99,235,0.3);
        }

        .nav-link i{
            width: 22px;
        }

        .badge-notif{
            font-size: 0.72rem;
            padding: 5px 9px;
        }

        .logout-area{
            position: absolute;
            bottom: 20px;
            width: 100%;
            padding: 0 14px;
        }

        .logout-btn{
            color: #f87171 !important;
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
            .nav-link span,
            .nav-link .menu-text,
            .badge-notif{
                display: none;
            }

            .nav-link{
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

            {{-- Dashboard --}}
            <a class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"
               href="/admin/dashboard">

                <div>
                    <i class="fas fa-chart-pie me-2"></i>
                    <span class="menu-text">Dashboard</span>
                </div>
            </a>

            {{-- Manajemen Kategori --}}
            <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}"
               href="/admin/categories">

                <div>
                    <i class="fas fa-tags me-2"></i>
                    <span class="menu-text">Manajemen Kategori</span>
                </div>
            </a>

            {{-- Manajemen Stok --}}
            <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}"
               href="/admin/products">

                <div>
                    <i class="fas fa-boxes-stacked me-2"></i>
                    <span class="menu-text">Manajemen Stok</span>
                </div>
            </a>

            {{-- Barang Masuk --}}
            <a class="nav-link {{ request()->is('admin/restocks*') ? 'active' : '' }}"
               href="/admin/restocks">

                <div>
                    <i class="fas fa-file-import me-2"></i>
                    <span class="menu-text">Barang Masuk</span>
                </div>
            </a>

            {{-- Permintaan Stok --}}
            <a class="nav-link {{ request()->is('admin/stock-requests*') ? 'active' : '' }}"
               href="{{ route('admin.stock-requests.index') }}">

                <div>
                    <i class="fas fa-truck-loading me-2"></i>
                    <span class="menu-text">Permintaan Stok</span>
                </div>

                @if(isset($pendingRequestCount) && $pendingRequestCount > 0)
                    <span class="badge bg-danger rounded-pill badge-notif">
                        {{ $pendingRequestCount }}
                    </span>
                @endif
            </a>

            {{-- List User B2B --}}
            <a class="nav-link {{ request()->is('admin/b2b-registrations*') ? 'active' : '' }}"
               href="{{ route('admin.b2b.list') }}">

                <div>
                    <i class="fas fa-users-gear me-2"></i>
                    <span class="menu-text">List User B2B</span>
                </div>

                @if(isset($pendingB2BCount) && $pendingB2BCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill badge-notif">
                        {{ $pendingB2BCount }}
                    </span>
                @endif
            </a>

            {{-- Safety Stock --}}
            <a class="nav-link {{ request()->is('admin/safety-stock*') ? 'active' : '' }}"
               href="/admin/safety-stock">

                <div>
                    <i class="fas fa-calculator me-2"></i>
                    <span class="menu-text">Safety Stock</span>
                </div>
            </a>

            {{-- Laporan --}}
            <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}"
               href="{{ route('admin.orders.index') }}">

                <div>
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span class="menu-text">Pesanan</span>
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

            {{-- Setting Harga B2B --}}
            <a class="nav-link {{ request()->is('admin/b2b-prices*') ? 'active' : '' }}"
               href="{{ route('admin.b2b-prices.index') }}">

                <div>
                    <i class="fas fa-hand-holding-dollar me-2"></i>
                    <span class="menu-text">Setting Harga B2B</span>
                </div>
            </a>

            {{-- Laporan --}}
            <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}"
               href="/admin/reports/inventory">

                <div>
                    <i class="fas fa-file-chart-column me-2"></i>
                    <span class="menu-text">Laporan</span>
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

</body>
</html>