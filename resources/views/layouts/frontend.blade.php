<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Podgasm</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('LogoPodgasm.png') }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Google Fonts: Poppins & Playfair Display --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    
    {{-- AOS CSS --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- SweetAlert2 --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    {{-- Theme Initialization Script (Prevents White Flash in Dark Mode) --}}
    <script>
        (function() {
            const savedTheme = localStorage.getItem('podgasm_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <style>
        body, input, button, select, textarea, p, h1, h2, h3, h4, h5, h6, a, span, div {
            font-family: 'Poppins', sans-serif;
        }

        .fa, .fas, .far, .fab, .fal, .fad, [class*="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
        }

        .fas, .fa-solid {
            font-weight: 900 !important;
        }

        .far, .fa-regular {
            font-weight: 400 !important;
        }

        .fab, .fa-brands {
            font-weight: 400 !important;
        }

        html {
            overflow-y: scroll;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body{
            background: #ffffff;
            min-height: 100vh;
            color: #0f172a;
            position: relative;
            z-index: 0;
            overflow-x: hidden;
            padding-top: 130px; /* Space untuk top bar + navbar desktop */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ================= HIGH-AESTHETIC DARK MODE STYLES ================= */
        html[data-bs-theme="dark"], body.dark-mode {
            background-color: #0b0f19 !important;
            color: #f8fafc !important;
        }

        body.dark-mode .bg-white,
        body.dark-mode .card,
        body.dark-mode nav.navbar,
        body.dark-mode .offcanvas,
        body.dark-mode .modal-content,
        body.dark-mode .dropdown-menu {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .top-announcement-bar {
            background-color: #080c14 !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .top-announcement-bar div,
        body.dark-mode .top-announcement-bar a {
            color: #94a3b8 !important;
        }

        body.dark-mode .text-dark,
        body.dark-mode .nav-link,
        body.dark-mode .navbar-brand,
        body.dark-mode .text-primary,
        body.dark-mode .price-text,
        body.dark-mode h2.text-primary,
        body.dark-mode .display-6,
        body.dark-mode .surfeit-details-box .fw-bold {
            color: #ffffff !important;
        }

        body.dark-mode .text-secondary,
        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        body.dark-mode .surfeit-image-box,
        body.dark-mode .surfeit-subcategory-card,
        body.dark-mode .product-img-wrapper,
        body.dark-mode .bg-slate-50,
        body.dark-mode .bg-light,
        body.dark-mode .bg-f4,
        body.dark-mode .category-parent-pane {
            background-color: #1e293b !important;
        }

        /* PERBAIKAN TEMA GELAP KATEGORI: Ubah background icon container & invert warna icon agar putih kontras tinggi */
        body.dark-mode .surfeit-subcategory-card > div:first-child,
        body.dark-mode .category-icon-box,
        html[data-bs-theme="dark"] .surfeit-subcategory-card > div:first-child,
        html[data-bs-theme="dark"] .category-icon-box {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
        }

        body.dark-mode .surfeit-subcategory-card img,
        html[data-bs-theme="dark"] .surfeit-subcategory-card img {
            filter: brightness(0) invert(1) !important;
            opacity: 0.95 !important;
            transition: filter 0.3s ease, opacity 0.3s ease;
        }

        body.dark-mode .surfeit-subcategory-card:hover,
        html[data-bs-theme="dark"] .surfeit-subcategory-card:hover {
            background-color: #334155 !important;
        }

        body.dark-mode .surfeit-subcategory-card:hover > div:first-child,
        html[data-bs-theme="dark"] .surfeit-subcategory-card:hover > div:first-child {
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%) !important;
        }

        /* ACTIVE CATEGORY CARD HIGHLIGHT & GLOW */
        .surfeit-subcategory-card.active-category-card {
            border: 2px solid #09afb9 !important;
            box-shadow: 0 4px 18px rgba(9, 175, 185, 0.45), 0 0 0 2px rgba(9, 175, 185, 0.2) !important;
            transform: translateY(-3px) !important;
        }
        .surfeit-subcategory-card.active-category-card .category-icon-box {
            background: linear-gradient(180deg, #e0f2fe 0%, #ccfbf1 100%) !important;
        }

        body.dark-mode .surfeit-subcategory-card.active-category-card,
        html[data-bs-theme="dark"] .surfeit-subcategory-card.active-category-card {
            border: 2px solid #00d4ff !important;
            box-shadow: 0 4px 22px rgba(0, 212, 255, 0.55), 0 0 0 2px rgba(9, 175, 185, 0.35) !important;
        }
        body.dark-mode .surfeit-subcategory-card.active-category-card .category-icon-box,
        html[data-bs-theme="dark"] .surfeit-subcategory-card.active-category-card .category-icon-box {
            background: linear-gradient(180deg, #0f766e 0%, #1e293b 100%) !important;
        }

        /* SKELETON SHIMMER LOADING PLACEHOLDER */
        .skeleton-shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeletonWave 1.4s infinite linear;
            border-radius: 6px;
        }
        body.dark-mode .skeleton-shimmer,
        html[data-bs-theme="dark"] .skeleton-shimmer {
            background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
            background-size: 200% 100%;
        }

        @keyframes skeletonWave {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .product-skeleton-card {
            border-radius: 4px;
            overflow: hidden;
        }
        .product-skeleton-img {
            height: 380px;
            border-radius: 4px;
        }

        /* LUXURY PRICING TIER COMPONENT */
        .card-pricing-tier {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 16px;
        }

        .card-pricing-tier-title {
            color: #0f172a !important;
        }

        /* DARK MODE PRICING TIER CONTAINER */
        body.dark-mode .card-pricing-tier,
        html[data-bs-theme="dark"] .card-pricing-tier {
            background: rgba(30, 41, 59, 0.5) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        body.dark-mode .card-pricing-tier-title,
        html[data-bs-theme="dark"] .card-pricing-tier-title {
            color: #f8fafc !important;
        }

        /* TIER CARDS STYLING */
        .tier-card-option {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tier-card-option .tier-label {
            color: #334155;
            white-space: nowrap;
        }

        .tier-card-option:hover {
            border-color: #09afb9 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(9, 175, 185, 0.15);
        }

        .tier-card-option.active-tier {
            border: 2px solid #09afb9 !important;
            background: #e0f2fe !important;
            box-shadow: 0 4px 16px rgba(9, 175, 185, 0.25) !important;
        }

        .tier-card-option.active-tier .tier-label {
            color: #0f766e !important;
        }

        /* DARK MODE TIER CARDS */
        body.dark-mode .tier-card-option,
        html[data-bs-theme="dark"] .tier-card-option {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        body.dark-mode .tier-card-option .tier-label,
        html[data-bs-theme="dark"] .tier-card-option .tier-label {
            color: #cbd5e1 !important;
            white-space: nowrap;
        }

        body.dark-mode .tier-card-option:hover,
        html[data-bs-theme="dark"] .tier-card-option:hover {
            border-color: #00d4ff !important;
        }

        body.dark-mode .tier-card-option.active-tier,
        html[data-bs-theme="dark"] .tier-card-option.active-tier {
            border: 2px solid #00d4ff !important;
            background: linear-gradient(135deg, rgba(9, 175, 185, 0.3) 0%, rgba(2, 132, 199, 0.2) 100%) !important;
            box-shadow: 0 0 18px rgba(0, 212, 255, 0.35) !important;
        }

        body.dark-mode .tier-card-option.active-tier .tier-label,
        html[data-bs-theme="dark"] .tier-card-option.active-tier .tier-label {
            color: #ffffff !important;
        }

        /* Category Slider Drag Scroll & Navigation Buttons */
        .category-drag-scroll {
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-x;
            scroll-behavior: smooth;
        }
        .category-scroll-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background: #ffffff;
            border-color: #cbd5e1;
            color: #475569;
        }
        .category-scroll-btn:hover {
            background: #09afb9 !important;
            border-color: #09afb9 !important;
            color: #ffffff !important;
            transform: scale(1.08);
        }
        body.dark-mode .category-scroll-btn,
        html[data-bs-theme="dark"] .category-scroll-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        body.dark-mode .category-scroll-btn:hover,
        html[data-bs-theme="dark"] .category-scroll-btn:hover {
            background: #09afb9 !important;
            border-color: #09afb9 !important;
            color: #ffffff !important;
        }

        body.dark-mode .surfeit-product-wrapper:hover .surfeit-image-box {
            background-color: #334155 !important;
        }

        body.dark-mode .border-light,
        body.dark-mode .border-bottom,
        body.dark-mode .border-top,
        body.dark-mode .border {
            border-color: #1e293b !important;
        }

        body.dark-mode .border-dark {
            border-color: #f8fafc !important;
        }

        body.dark-mode .surfeit-category-title {
            color: #ffffff !important;
        }

        body.dark-mode .btn-light {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-mode .theme-toggle-btn {
            background-color: #1e293b !important;
            color: #f59e0b !important;
            border: 1px solid #334155 !important;
        }

        @media(max-width: 768px){
            body{
                padding-top: 58px; /* Space untuk fixed navbar mobile */
            }
        }

        /* ================= MAIN WRAPPER ================= */

        main{
            padding: 35px 20px;
            position: relative;
            z-index: auto;
        }

        /* ================= CARD STYLE ================= */

        .card{
            border: none;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 6px 22px rgba(0,0,0,0.05);
            transition: all 0.25s ease;
            overflow: hidden;
        }

        .card:hover{
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.08);
        }

        .card-header{
            background: transparent;
            border-bottom: 1px solid #eef2f7;
            padding: 20px 24px;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .card-body{
            padding: 24px;
        }

        /* ================= BUTTON (BRAND THEMED) ================= */
        :root {
            --brand-color: #09afb9;
            --brand-gradient: linear-gradient(135deg, #09afb9 0%, #00d4ff 100%);
            --brand-hover: #078991;
        }

        .text-primary {
            color: var(--brand-color) !important;
        }

        .bg-primary {
            background: var(--brand-gradient) !important;
        }

        .btn-primary {
            background: var(--brand-gradient) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 10px 18px !important;
            font-weight: 600 !important;
            color: white !important;
            box-shadow: 0 4px 14px rgba(9, 175, 185, 0.25) !important;
            transition: all 0.25s ease !important;
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #078991 0%, #00b4d8 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(9, 175, 185, 0.4) !important;
            color: white !important;
        }

        .btn-outline-primary {
            color: var(--brand-color) !important;
            border-color: var(--brand-color) !important;
            border-radius: 12px !important;
            transition: all 0.25s ease !important;
        }

        .btn-outline-primary:hover {
            background: var(--brand-gradient) !important;
            color: white !important;
            border-color: transparent !important;
        }

        .badge.bg-primary {
            background: var(--brand-gradient) !important;
        }
        
        .border-primary {
            border-color: var(--brand-color) !important;
        }

        /* ================= TABLE ================= */

        .table{
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table thead th{
            border: none;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.9rem;
            padding-bottom: 14px;
        }

        .table tbody tr{
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            border-radius: 14px;
        }

        .table tbody td{
            vertical-align: middle;
            border: none;
            padding: 16px;
        }

        /* ================= FORM ================= */

        .form-control,
        .form-select{
            border-radius: 12px;
            border: 1px solid #dbe2ea;
            padding: 11px 14px;
            transition: 0.2s;
        }

        .form-control:focus,
        .form-select:focus{
            border-color: var(--brand-color) !important;
            box-shadow: 0 0 0 0.15rem rgba(9, 175, 185, 0.15) !important;
        }

        .form-check-input:checked {
            background-color: var(--brand-color) !important;
            border-color: var(--brand-color) !important;
        }

        /* ================= ALERT ================= */

        .alert{
            border: none;
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        .alert-success{
            background: #ecfdf5;
            color: #065f46;
        }

        .alert-danger{
            background: #fef2f2;
            color: #991b1b;
        }

        /* ================= BADGE ================= */

        .badge{
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 500;
        }

        /* ================= SECTION TITLE ================= */

        .section-title{
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #111827;
        }

        .section-subtitle{
            color: #6b7280;
            margin-top: -10px;
            margin-bottom: 30px;
        }

        /* ================= NAVBAR FIX ================= */
        
        .navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 9999 !important;
            width: 100% !important;
        }

        .offcanvas {
            z-index: 9998 !important;
            top: 90px !important;
            height: calc(100vh - 90px) !important;
        }

        @media(max-width: 768px){
            .offcanvas {
                top: 58px !important;
                height: calc(100vh - 58px) !important;
            }
        }

        .modal {
            z-index: 9997 !important;
        }

        .modal-backdrop {
            z-index: 9996 !important;
        }

        .dropdown-menu {
            z-index: 10000 !important;
        }

        /* ================= LOADING ANIMATION ================= */

        .spinner-animation {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spinner-rotate 0.8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spinner-rotate {
            to { transform: rotate(360deg); }
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 10001;
        }

        .toast-notification {
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
            animation: slideInRight 0.3s ease-out forwards;
        }

        .toast-notification.success {
            border-left: 4px solid #10b981;
        }

        .toast-notification.success .toast-icon {
            color: #10b981;
        }

        .toast-notification.error {
            border-left: 4px solid #ef4444;
        }

        .toast-notification.error .toast-icon {
            color: #ef4444;
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon {
            font-size: 20px;
        }

        .toast-text {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #9ca3af;
            padding: 0;
        }

        .toast-close:hover {
            color: #6b7280;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .toast-notification.hide {
            animation: slideOutRight 0.3s ease-out forwards;
        }

        /* Cart Badge Pulse Animation */
        .cart-badge-pulse {
            animation: badge-pulse 0.5s ease-out;
        }

        @keyframes badge-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 768px){

            main{
                padding: 20px 14px;
            }

            .card-body{
                padding: 18px;
            }

        }

    </style>
</head>

<body>

    {{-- Toast Container --}}
    <div class="toast-container"></div>

    {{-- Navbar --}}
    @include('components._navbar')

    {{-- Main Content --}}
    <main>

        <div class="container-fluid">

            @yield('content')

        </div>

    </main>

    {{-- Footer & Health Warning Banner --}}
    @include('components._footer')

    {{-- Global Offcanvas Cart --}}
    <div class="offcanvas offcanvas-end shadow" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel" style="width: 400px; border-left: none;">
        <div class="offcanvas-header border-bottom py-3">
            <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel">
                <i class="fas fa-shopping-cart text-primary me-2"></i> Keranjang Belanja
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0" id="offcanvasCartBody">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    {{-- AOS JS --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Init AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    </script>

    {{-- Wishlist AJAX Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle wishlist button clicks
            const wishlistButtons = document.querySelectorAll('.wishlist-btn');
            
            wishlistButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const productId = this.getAttribute('data-product-id');
                    const icon = this.querySelector('i');
                    
                    // Send AJAX request
                    fetch(`{{ route('wishlist.add', ':id') }}`.replace(':id', productId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (response.status === 401) {
                            showToast('Silakan login terlebih dahulu untuk menambahkan produk ke wishlist.', 'error', 3000);
                            setTimeout(() => {
                                window.location.href = "{{ route('login') }}";
                            }, 1500);
                            return null;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data) return;
                        if (data.success) {
                            // Fill the heart icon
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            
                            // Show success message
                            showToast(data.message, 'success');
                            
                            // Update wishlist count in navbar
                            updateWishlistCount(data.wishlistCount);
                        } else {
                            showToast(data.message, 'info');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Terjadi kesalahan, silakan coba lagi', 'error');
                    });
                });
            });
        });
        
        // Function to show toast notification with SweetAlert2
        function showToast(message, type = 'success', duration = 3000) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: type === 'info' ? 'info' : (type === 'error' ? 'error' : 'success'),
                title: message
            });
        }
        
        // Function to update cart count with animation
        function updateCartCount(newCount) {
            const cartBtn = document.querySelector('.navbar .fa-shopping-cart').closest('.btn');
            
            if (!cartBtn) return;
            
            // Add animation styles if not present
            const styleId = 'badge-animation-style';
            if (!document.getElementById(styleId)) {
                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = `
                    @keyframes badge-pulse {
                        0% { 
                            transform: scale(1);
                            opacity: 1;
                        }
                        50% {
                            transform: scale(1.3);
                        }
                        100% { 
                            transform: scale(1);
                            opacity: 1;
                        }
                    }
                    .cart-badge-pulse {
                        animation: badge-pulse 0.4s ease-out !important;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Find the existing badge in navbar (use .badge-counter selector)
            let badge = cartBtn.querySelector('.badge-counter');
            
            if (newCount > 0) {
                if (badge) {
                    // Update existing badge
                    badge.textContent = newCount;
                    badge.classList.add('cart-badge-pulse');
                    
                    // Remove animation class after it completes
                    setTimeout(() => {
                        if (badge && badge.parentNode) {
                            badge.classList.remove('cart-badge-pulse');
                        }
                    }, 450);
                }
            } else if (badge) {
                // Remove badge if count is 0
                badge.remove();
            }
        }

        // Function to update wishlist count
        function updateWishlistCount(newCount) {
            const wishlistBtn = document.querySelector('.fa-heart').closest('.btn');
            if (wishlistBtn) {
                const existingBadge = wishlistBtn.querySelector('.badge');
                if (existingBadge) {
                    existingBadge.remove();
                }
                
                if (newCount > 0) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle border border-light rounded-circle';
                    badge.style.padding = '0.35em 0.5em';
                    badge.textContent = newCount;
                    wishlistBtn.appendChild(badge);
                }
            }
        }
    </script>

    {{-- Live Chat CS Widget for B2B/B2C without branch --}}
    @include('components._cs-chat-widget')

    {{-- Age Verification Modal (21+) --}}
    @include('components._age-verification-modal')

    {{-- ✅ PERBAIKAN: Stack untuk @push('scripts') dari child views --}}
    @stack('scripts')

</body>
</html>