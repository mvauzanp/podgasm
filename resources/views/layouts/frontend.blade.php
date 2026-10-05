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

    {{-- Page-specific Styles --}}
    @stack('styles')

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
            font-size: 14.5px; /* Standar compact scaling untuk look & feel modern minimalis */
            overflow-y: scroll;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body{
            background: #ffffff;
            min-height: 100vh;
            color: #0f172a;
            font-size: 0.92rem;
            position: relative;
            z-index: 0;
            overflow-x: hidden;
            padding-top: 108px; /* Space untuk top bar + navbar sleek */
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

        body.dark-mode .surfeit-subcategory-card,
        body.dark-mode .bg-slate-50,
        body.dark-mode .bg-light,
        body.dark-mode .bg-f4,
        body.dark-mode .category-parent-pane {
            background-color: #1e293b !important;
        }

        body.dark-mode .surfeit-image-box,
        html[data-bs-theme="dark"] .surfeit-image-box,
        body.dark-mode .surfeit-product-wrapper,
        html[data-bs-theme="dark"] .surfeit-product-wrapper {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        body.dark-mode .surfeit-product-wrapper:hover .surfeit-image-box,
        html[data-bs-theme="dark"] .surfeit-product-wrapper:hover .surfeit-image-box {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        body.dark-mode .product-img-wrapper {
            background-color: transparent !important;
            border: none !important;
        }

        body.dark-mode .card.border-0 {
            border: none !important;
        }

        /* SURFEIT SUBCATEGORY CARD STYLES */
        .surfeit-subcategory-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .surfeit-subcategory-card .category-icon-box {
            background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.25s ease;
        }
        .surfeit-subcategory-card .category-card-label {
            background-color: #ffffff;
            transition: background 0.25s ease;
        }
        .surfeit-subcategory-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
            border-color: #09afb9 !important;
        }
        .surfeit-subcategory-card:hover .category-card-label span {
            color: #09afb9 !important;
        }

        /* PERBAIKAN TEMA GELAP KATEGORI: Ubah background icon container & invert warna icon agar putih kontras tinggi */
        body.dark-mode .surfeit-subcategory-card,
        html[data-bs-theme="dark"] .surfeit-subcategory-card {
            background-color: #1a2234 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }
        body.dark-mode .surfeit-subcategory-card .category-icon-box,
        html[data-bs-theme="dark"] .surfeit-subcategory-card .category-icon-box {
            background: linear-gradient(180deg, #0f172a 0%, #1a2234 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        }
        body.dark-mode .surfeit-subcategory-card .category-card-label,
        html[data-bs-theme="dark"] .surfeit-subcategory-card .category-card-label {
            background-color: #1a2234 !important;
        }
        body.dark-mode .surfeit-subcategory-card .category-card-label span,
        html[data-bs-theme="dark"] .surfeit-subcategory-card .category-card-label span {
            color: #e2e8f0 !important;
        }

        body.dark-mode .surfeit-subcategory-card img,
        html[data-bs-theme="dark"] .surfeit-subcategory-card img {
            filter: brightness(0) invert(1) !important;
            opacity: 0.95 !important;
            transition: filter 0.3s ease, opacity 0.3s ease;
        }

        body.dark-mode .surfeit-subcategory-card:hover,
        html[data-bs-theme="dark"] .surfeit-subcategory-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px -2px rgba(0, 212, 255, 0.25);
            border-color: #00d4ff !important;
        }
        body.dark-mode .surfeit-subcategory-card:hover .category-card-label span,
        html[data-bs-theme="dark"] .surfeit-subcategory-card:hover .category-card-label span {
            color: #00d4ff !important;
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
            transform: translateY(-3px) !important;
        }
        body.dark-mode .surfeit-subcategory-card.active-category-card .category-icon-box,
        html[data-bs-theme="dark"] .surfeit-subcategory-card.active-category-card .category-icon-box {
            background: linear-gradient(180deg, #0f766e 0%, #1e293b 100%) !important;
        }

        /* DARK MODE FOR FORMS, TABLES, CHECKOUT & TRANSACTIONS */
        body.dark-mode .form-control,
        body.dark-mode .form-select,
        html[data-bs-theme="dark"] .form-control,
        html[data-bs-theme="dark"] .form-select {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus,
        html[data-bs-theme="dark"] .form-control:focus,
        html[data-bs-theme="dark"] .form-select:focus {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }

        body.dark-mode .table,
        body.dark-mode .table th,
        body.dark-mode .table td,
        html[data-bs-theme="dark"] .table,
        html[data-bs-theme="dark"] .table th,
        html[data-bs-theme="dark"] .table td {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .payment-card,
        html[data-bs-theme="dark"] .payment-card {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
            color: #f8fafc !important;
        }

        body.dark-mode .payment-radio:checked + .payment-card,
        html[data-bs-theme="dark"] .payment-radio:checked + .payment-card {
            background-color: #1e293b !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2) !important;
        }

        body.dark-mode .list-group-item,
        html[data-bs-theme="dark"] .list-group-item {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .product-card-title,
        body.dark-mode .product-card-price,
        html[data-bs-theme="dark"] .product-card-title,
        html[data-bs-theme="dark"] .product-card-price {
            color: #ffffff !important;
        }

        body.dark-mode .product-card-cat,
        html[data-bs-theme="dark"] .product-card-cat {
            color: #94a3b8 !important;
        }

        body.dark-mode .btn-light,
        html[data-bs-theme="dark"] .btn-light {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        body.dark-mode .alert-light,
        html[data-bs-theme="dark"] .alert-light {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        body.dark-mode #mobileStickyBar,
        html[data-bs-theme="dark"] #mobileStickyBar {
            background-color: #0f172a !important;
            border-top: 1px solid #1e293b !important;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.4) !important;
        }

        /* GLOBAL FILTER & SORT DARK MODE */
        body.dark-mode .filter-sort-btn,
        html[data-bs-theme="dark"] .filter-sort-btn {
            color: #f8fafc !important;
        }
        body.dark-mode .filter-sort-label,
        html[data-bs-theme="dark"] .filter-sort-label {
            border-bottom-color: #f8fafc !important;
        }
        body.dark-mode .filter-sort-btn:hover,
        html[data-bs-theme="dark"] .filter-sort-btn:hover {
            color: #38bdf8 !important;
        }
        body.dark-mode .items-counter-text,
        html[data-bs-theme="dark"] .items-counter-text {
            color: #94a3b8 !important;
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
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .card-pricing-tier-title {
            color: #0f172a !important;
        }

        .tier-highlight-badge {
            background: #0f172a !important;
            color: #ffffff !important;
            border: none !important;
        }

        /* DARK MODE PRICING TIER CONTAINER */
        body.dark-mode .card-pricing-tier,
        html[data-bs-theme="dark"] .card-pricing-tier {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        body.dark-mode .card-pricing-tier-title,
        html[data-bs-theme="dark"] .card-pricing-tier-title {
            color: #f8fafc !important;
        }

        body.dark-mode .tier-highlight-badge,
        html[data-bs-theme="dark"] .tier-highlight-badge {
            background: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
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

        /* VARIANT BUTTONS STYLING */
        .variant-btn {
            transition: all 0.2s ease;
            border-width: 1.5px;
        }

        body.dark-mode .variant-btn.btn-outline-primary,
        html[data-bs-theme="dark"] .variant-btn.btn-outline-primary {
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.35) !important;
            background-color: rgba(15, 23, 42, 0.6) !important;
        }

        body.dark-mode .variant-btn.btn-outline-primary:hover,
        html[data-bs-theme="dark"] .variant-btn.btn-outline-primary:hover {
            color: #ffffff !important;
            border-color: #38bdf8 !important;
            background-color: rgba(56, 189, 248, 0.2) !important;
        }

        body.dark-mode .variant-btn.btn-primary,
        html[data-bs-theme="dark"] .variant-btn.btn-primary {
            color: #ffffff !important;
            border-color: #09afb9 !important;
            background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%) !important;
            box-shadow: 0 0 14px rgba(9, 175, 185, 0.4) !important;
        }

        /* Category Slider Drag Scroll & Navigation Buttons */
        .category-drag-scroll {
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-x;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        .category-drag-scroll * {
            user-drag: none;
            -webkit-user-drag: none;
        }
        .category-drag-scroll.is-dragging {
            cursor: grabbing !important;
            scroll-behavior: auto !important;
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

        body.dark-mode .surfeit-product-wrapper:hover .surfeit-image-box,
        html[data-bs-theme="dark"] .surfeit-product-wrapper:hover .surfeit-image-box {
            background-color: rgba(255, 255, 255, 0.04) !important;
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

        /* Dark Mode Modal Enhancements */
        body.dark-mode .modal-content,
        html[data-bs-theme="dark"] .modal-content {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid #1e293b !important;
        }

        body.dark-mode .modal-header,
        html[data-bs-theme="dark"] .modal-header,
        body.dark-mode .modal-footer,
        html[data-bs-theme="dark"] .modal-footer {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
        }

        body.dark-mode .modal-header .btn-close,
        html[data-bs-theme="dark"] .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        body.dark-mode .modal-title,
        html[data-bs-theme="dark"] .modal-title {
            color: #ffffff !important;
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

        /* ================= BASE & RESPONSIVE STYLING FOR ALL DEVICES ================= */
        html, body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
            padding-top: 96px !important;
        }

        .navbar-logo {
            height: 44px !important;
            max-height: 48px !important;
            width: auto !important;
            max-width: 220px !important;
            object-fit: contain !important;
            display: block;
        }

        .btn-icon-nav,
        #themeToggleBtn {
            width: 38px !important;
            height: 38px !important;
            background-color: #f8fafc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-icon-nav i,
        #themeToggleBtn i {
            font-size: 0.92rem;
        }

        @media(max-width: 991.98px) {
            body {
                padding-top: 86px !important;
            }
            .surfeit-image-box {
                height: 210px !important;
                padding: 12px !important;
            }
            .surfeit-floating-img {
                max-height: 165px !important;
            }
        }

        @media(max-width: 767.98px) {
            body {
                padding-top: 84px !important;
                font-size: 0.88rem;
            }

            main {
                padding: 8px 0 !important;
            }

            .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .top-announcement-bar {
                font-size: 0.72rem !important;
                padding: 4px 8px !important;
            }

            .navbar-logo {
                height: 30px !important;
                max-height: 32px !important;
            }

            .btn-icon-nav,
            #themeToggleBtn {
                width: 32px !important;
                height: 32px !important;
            }

            .btn-icon-nav i,
            #themeToggleBtn i {
                font-size: 0.78rem !important;
            }

            .card-body {
                padding: 12px;
            }

            .surfeit-image-box {
                height: 170px !important;
                padding: 10px !important;
            }

            .surfeit-floating-img {
                max-height: 135px !important;
            }

            .product-card-title {
                font-size: 0.84rem !important;
            }

            .product-card-cat {
                font-size: 0.72rem !important;
            }

            .price-text {
                font-size: 0.85rem !important;
            }

            .surfeit-category-title {
                font-size: 1.45rem !important;
            }

            .surfeit-subcategory-card {
                width: 95px !important;
            }

            .surfeit-subcategory-card .category-icon-box {
                height: 60px !important;
            }

            #offcanvasCart {
                width: 100vw !important;
                max-width: 100vw !important;
                border-radius: 0 !important;
            }
        }

        /* ================= OFFCANVAS CART PRECISION STYLES ================= */
        #offcanvasCart {
            z-index: 100010 !important;
            width: 420px;
            max-width: 100vw;
            border-left: 1px solid rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
        }
        .offcanvas-backdrop {
            z-index: 100005 !important;
            background-color: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
        }
        #offcanvasCart .offcanvas-header {
            height: 62px;
            border-bottom: 1px solid #f1f5f9;
        }
        #offcanvasCart .offcanvas-body {
            height: calc(100vh - 62px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Dark Theme Support for Offcanvas */
        body.dark-mode #offcanvasCart,
        html[data-bs-theme="dark"] #offcanvasCart {
            background-color: #0b1324 !important;
            color: #f8fafc !important;
            border-left: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-mode #offcanvasCart .offcanvas-header,
        html[data-bs-theme="dark"] #offcanvasCart .offcanvas-header {
            background-color: #0f172a !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #f8fafc !important;
        }
        body.dark-mode #offcanvasCart .btn-close,
        html[data-bs-theme="dark"] #offcanvasCart .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        body.dark-mode #offcanvasCart .offcanvas-body,
        html[data-bs-theme="dark"] #offcanvasCart .offcanvas-body {
            background-color: #0b1324 !important;
        }

        /* Address Card & Verified Area Box (Light & Dark Theme) */
        .address-card-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.2s ease;
        }
        .address-card-item.is-primary {
            border: 2px solid #0f172a !important;
        }
        .verified-area-box {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            color: #334155 !important;
        }
        .verified-area-box .area-title {
            color: #0f172a !important;
        }
        .verified-area-box .area-postal {
            color: #64748b !important;
        }

        /* Dark Theme Global Components (Cards, Modals, Forms) */
        body.dark-mode .card,
        html[data-bs-theme="dark"] .card {
            background-color: #111827 !important;
            color: #f8fafc !important;
            border-color: #1f2937 !important;
        }
        body.dark-mode .address-card-item,
        html[data-bs-theme="dark"] .address-card-item {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        body.dark-mode .address-card-item.is-primary,
        html[data-bs-theme="dark"] .address-card-item.is-primary {
            border: 2px solid #60a5fa !important;
            box-shadow: 0 0 15px rgba(96, 165, 250, 0.15) !important;
        }
        body.dark-mode .verified-area-box,
        html[data-bs-theme="dark"] .verified-area-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        body.dark-mode .verified-area-box .area-title,
        html[data-bs-theme="dark"] .verified-area-box .area-title {
            color: #f8fafc !important;
        }
        body.dark-mode .verified-area-box .area-postal,
        html[data-bs-theme="dark"] .verified-area-box .area-postal {
            color: #94a3b8 !important;
        }
        body.dark-mode .verified-area-box i,
        html[data-bs-theme="dark"] .verified-area-box i {
            color: #60a5fa !important;
        }
        body.dark-mode .badge-theme-label,
        html[data-bs-theme="dark"] .badge-theme-label {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
        }
        body.dark-mode .modal-content,
        html[data-bs-theme="dark"] .modal-content {
            background-color: #111827 !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        body.dark-mode .modal-header,
        html[data-bs-theme="dark"] .modal-header {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
            color: #f8fafc !important;
        }
        body.dark-mode .modal-footer,
        html[data-bs-theme="dark"] .modal-footer {
            background-color: #1e293b !important;
            border-top-color: #334155 !important;
        }
        body.dark-mode .modal-header .btn-close,
        html[data-bs-theme="dark"] .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        body.dark-mode .form-control,
        html[data-bs-theme="dark"] .form-control {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        body.dark-mode .form-control:focus,
        html[data-bs-theme="dark"] .form-control:focus {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border-color: #6366f1 !important;
        }
        body.dark-mode .text-dark,
        html[data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }
        body.dark-mode .text-muted,
        body.dark-mode .text-secondary,
        html[data-bs-theme="dark"] .text-muted,
        html[data-bs-theme="dark"] .text-secondary {
            color: #94a3b8 !important;
        }
        body.dark-mode .bg-light,
        html[data-bs-theme="dark"] .bg-light {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }
        body.dark-mode .border,
        html[data-bs-theme="dark"] .border {
            border-color: #334155 !important;
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
    <div class="offcanvas offcanvas-end border-0 shadow-2xl" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
        <div class="offcanvas-header py-3 flex-shrink-0 px-3.5">
            <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2 mb-0" id="offcanvasCartLabel">
                <i class="fas fa-shopping-bag text-primary"></i>
                <span>Keranjang Belanja</span>
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
        
        // Function to show toast notification with SweetAlert2 (Theme-Aware)
        function showToast(message, type = 'success', duration = 3000) {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.body.classList.contains('dark-mode');
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                background: isDark ? '#182234' : '#ffffff',
                color: isDark ? '#f8fafc' : '#0f172a',
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
        
        // ==========================================
        // 🛒 ENHANCED CART ANIMATION & COUNTER SUITE
        // ==========================================

        // Function to update cart count with animation
        function updateCartCount(newCount) {
            const cartBtn = document.getElementById('navbarCartBtn') || document.querySelector('.navbar button[data-bs-target="#offcanvasCart"]');
            if (!cartBtn) return;
            
            // Add animation styles if not present
            const styleId = 'badge-animation-style';
            if (!document.getElementById(styleId)) {
                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = `
                    @keyframes badge-pop {
                        0% { transform: scale(0.6) translate(-50%, -50%); opacity: 0; }
                        50% { transform: scale(1.4) translate(-35%, -35%); opacity: 1; }
                        100% { transform: scale(1) translate(-50%, -50%); opacity: 1; }
                    }
                    @keyframes cart-bounce {
                        0%, 100% { transform: translateY(0); }
                        30% { transform: translateY(-6px) scale(1.1); }
                        60% { transform: translateY(2px) scale(0.95); }
                    }
                    .cart-badge-pulse {
                        animation: badge-pop 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
                    }
                    .cart-btn-bounce {
                        animation: cart-bounce 0.5s ease !important;
                    }
                    .fly-to-cart-clone {
                        position: fixed;
                        z-index: 999999;
                        border-radius: 12px;
                        filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
                        pointer-events: none;
                        transition: all 0.65s cubic-bezier(0.2, 0.9, 0.3, 1);
                        object-fit: contain;
                        background: transparent !important;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Trigger cart button bounce
            cartBtn.classList.add('cart-btn-bounce');
            setTimeout(() => cartBtn.classList.remove('cart-btn-bounce'), 550);

            // Find or create badge
            let badge = cartBtn.querySelector('.cart-badge-counter') || cartBtn.querySelector('.badge');
            
            if (newCount > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-circle bg-primary text-white border border-light cart-badge-counter';
                    badge.style.fontSize = '0.55rem';
                    badge.style.padding = '2px 4px';
                    cartBtn.appendChild(badge);
                }
                
                badge.textContent = newCount;
                badge.classList.remove('cart-badge-pulse');
                void badge.offsetWidth; // Trigger reflow
                badge.classList.add('cart-badge-pulse');
            } else if (badge) {
                badge.remove();
            }
        }

        // Global Fly-to-Cart Animation
        window.animateFlyToCart = function(sourceElement) {
            const targetBtn = document.getElementById('navbarCartBtn') || document.querySelector('.navbar button[data-bs-target="#offcanvasCart"]');
            if (!sourceElement || !targetBtn) return;

            const sourceRect = sourceElement.getBoundingClientRect();
            const targetRect = targetBtn.getBoundingClientRect();

            // Create flying clone
            const flyer = document.createElement('img');
            flyer.className = 'fly-to-cart-clone';
            flyer.src = sourceElement.src || (sourceElement.querySelector('img') ? sourceElement.querySelector('img').src : '{{ asset("LogoPodgasm.png") }}');
            
            // Set initial position matching source
            flyer.style.width = Math.min(sourceRect.width, 120) + 'px';
            flyer.style.height = Math.min(sourceRect.height, 120) + 'px';
            flyer.style.top = sourceRect.top + 'px';
            flyer.style.left = sourceRect.left + 'px';
            flyer.style.opacity = '1';
            
            document.body.appendChild(flyer);

            // Animate to target
            requestAnimationFrame(() => {
                flyer.style.top = (targetRect.top + targetRect.height / 2 - 15) + 'px';
                flyer.style.left = (targetRect.left + targetRect.width / 2 - 15) + 'px';
                flyer.style.width = '30px';
                flyer.style.height = '30px';
                flyer.style.opacity = '0.2';
                flyer.style.transform = 'scale(0.3) rotate(360deg)';
            });

            // Cleanup after animation completes
            setTimeout(() => {
                if (flyer && flyer.parentNode) {
                    flyer.remove();
                }
            }, 800);
        };

        // Function to update wishlist count
        function updateWishlistCount(newCount) {
            const wishlistBtn = document.getElementById('navbarWishlistBtn') || document.querySelector('.navbar a[href*="wishlist"]');
            if (!wishlistBtn) return;

            let badge = wishlistBtn.querySelector('.wishlist-badge-counter') || wishlistBtn.querySelector('.badge');
            
            if (newCount > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger text-white border border-light wishlist-badge-counter';
                    badge.style.fontSize = '0.55rem';
                    badge.style.padding = '2px 4px';
                    wishlistBtn.appendChild(badge);
                }
                badge.textContent = newCount;
            } else if (badge) {
                badge.remove();
            }
        }

        // Global function to load offcanvas cart dynamically
        function loadOffcanvasCart() {
            const container = document.getElementById('offcanvasCartBody');
            if (!container) return;

            container.innerHTML = `
                <div class="d-flex justify-content-center align-items-center h-100 py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat keranjang...</span>
                    </div>
                </div>
            `;

            fetch("{{ route('cart.offcanvas') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(res => {
                if (res.status === 401) {
                    return `
                        <div class="d-flex flex-column justify-content-center align-items-center flex-grow-1 p-4 text-center h-100">
                            <div class="icon-circle bg-light text-muted rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-2xs" style="width: 76px; height: 76px;">
                                <i class="fas fa-user-lock fa-2x opacity-40 text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 offcanvas-text-title">Masuk ke Akun Anda</h6>
                            <p class="text-muted small mb-4">Silakan login untuk melihat keranjang belanja dan melanjutkan transaksi.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 py-2.5 small fw-semibold shadow-sm">
                                <i class="fas fa-sign-in-alt me-1.5"></i> Masuk Sekarang
                            </a>
                        </div>
                    `;
                }
                return res.text();
            })
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = `
                    <div class="p-4 text-center text-muted small">
                        <i class="fas fa-exclamation-circle text-warning fa-2x mb-2"></i>
                        <p class="mb-0">Gagal memuat mini cart.</p>
                        <a href="{{ route('cart.index') }}" class="btn btn-primary btn-sm rounded-pill mt-2">Buka Halaman Keranjang</a>
                    </div>
                `;
            });
        }

        // Global initialization function for Category Slider (Drag & Scroll)
        function initCategorySlider() {
            const container = document.getElementById('categoryScrollContainer');
            const scrollNav = document.getElementById('categoryScrollNav');
            const btnLeft = document.getElementById('btnScrollCatLeft');
            const btnRight = document.getElementById('btnScrollCatRight');

            if (!container) return;

            // Check overflow to show/hide scroll buttons
            function checkOverflow() {
                if (!scrollNav) return;
                const isOverflowing = container.scrollWidth > container.clientWidth + 4;
                if (isOverflowing) {
                    scrollNav.classList.remove('d-none');
                    scrollNav.classList.add('d-flex');
                } else {
                    scrollNav.classList.remove('d-flex');
                    scrollNav.classList.add('d-none');
                }
            }

            checkOverflow();
            window.addEventListener('resize', checkOverflow);

            // 1. Navigation buttons
            if (btnLeft && !btnLeft.dataset.hasListener) {
                btnLeft.dataset.hasListener = 'true';
                btnLeft.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.scrollBy({ left: -280, behavior: 'smooth' });
                });
            }
            if (btnRight && !btnRight.dataset.hasListener) {
                btnRight.dataset.hasListener = 'true';
                btnRight.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.scrollBy({ left: 280, behavior: 'smooth' });
                });
            }

            // 2. Mouse Drag & Scroll
            let isDown = false;
            let startX = 0;
            let scrollLeft = 0;
            let isDragging = false;

            // Prevent native HTML5 ghost-drag on images and links
            container.querySelectorAll('img, a').forEach(el => {
                el.setAttribute('draggable', 'false');
            });

            container.addEventListener('mousedown', (e) => {
                isDown = true;
                isDragging = false;
                container.classList.add('is-dragging');
                startX = e.pageX - container.offsetLeft;
                scrollLeft = container.scrollLeft;
            });

            const stopDrag = () => {
                if (isDown) {
                    isDown = false;
                    container.classList.remove('is-dragging');
                    setTimeout(() => { isDragging = false; }, 60);
                }
            };

            window.addEventListener('mouseup', stopDrag);
            container.addEventListener('mouseleave', stopDrag);

            container.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX);
                if (Math.abs(walk) > 4) {
                    isDragging = true;
                    e.preventDefault();
                    container.scrollLeft = scrollLeft - walk;
                }
            });

            // Prevent link click when dragging
            container.addEventListener('click', (e) => {
                if (isDragging) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // 3. Mouse Wheel horizontal scroll
            container.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    container.scrollLeft += e.deltaY;
                }
            }, { passive: false });

            // 4. Auto-center active category card on load
            const activeCard = container.querySelector('.active-category-card');
            if (activeCard) {
                const link = activeCard.closest('a') || activeCard;
                setTimeout(() => {
                    const containerWidth = container.clientWidth;
                    const linkLeft = link.offsetLeft;
                    const linkWidth = link.offsetWidth;
                    container.scrollTo({
                        left: linkLeft - (containerWidth / 2) + (linkWidth / 2),
                        behavior: 'smooth'
                    });
                }, 100);
            }
        }

        document.addEventListener('DOMContentLoaded', initCategorySlider);
    </script>

    {{-- Age Verification Modal (21+) --}}
    @include('components._age-verification-modal')

    {{-- ✅ PERBAIKAN: Stack untuk @push('scripts') dari child views --}}
    @stack('scripts')

</body>
</html>