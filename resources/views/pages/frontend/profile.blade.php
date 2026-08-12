@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- HEADER TITLE --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Profil &amp; Akun Saya</h4>
                    <p class="text-muted small mb-0">Kelola informasi pribadi, ubah password, dan pantau status pesanan Anda.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">
                    <i class="fas fa-edit me-1.5"></i> Edit Profil
                </a>
            </div>

            {{-- ALERT SUCCESS --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 py-3 px-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-5 text-success"></i>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- MAIN PROFILE CARD --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        {{-- Avatar Circle --}}
                        <div class="col-md-3 text-center border-end-md">
                            <div class="position-relative d-inline-block">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 110px; height: 110px; border: 3px solid rgba(9, 175, 185, 0.2);">
                                    <i class="fas fa-user fa-3x"></i>
                                </div>
                                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Akun Aktif" style="width: 14px; height: 14px;"></span>
                            </div>
                            <h5 class="mt-3 fw-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 text-capitalize small">
                                {{ Auth::user()->role === 'branch' ? 'Cabang Resmi' : (Auth::user()->role === 'admin' ? 'Administrator' : 'Pelanggan Retail') }}
                            </span>
                        </div>

                        {{-- Details List --}}
                        <div class="col-md-9 ps-md-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <label class="text-muted small d-block mb-1"><i class="fas fa-envelope me-1 text-primary"></i> Alamat Email</label>
                                        <strong class="text-dark">{{ Auth::user()->email }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <label class="text-muted small d-block mb-1"><i class="fas fa-phone me-1 text-primary"></i> Nomor Telepon</label>
                                        <strong class="text-dark">{{ Auth::user()->phone ?? 'Belum diisi' }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3">
                                        <label class="text-muted small d-block mb-1"><i class="fas fa-map-marker-alt me-1 text-primary"></i> Alamat Pengiriman Utamamu</label>
                                        <p class="mb-0 text-dark fw-medium small leading-relaxed">{{ Auth::user()->address ?? 'Belum diisi. Klik Edit Profil untuk melengkapi alamat pengiriman.' }}</p>
                                    </div>
                                </div>
                                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> Tanggal Bergabung: <strong>{{ Auth::user()->created_at->format('d F Y') }}</strong>
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('profile.edit') }}#securityTab" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                                            <i class="fas fa-key me-1"></i> Ganti Password
                                        </a>
                                        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                                            <i class="fas fa-user-edit me-1"></i> Edit Profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ORDER STATUS TRACKER DASHBOARD --}}
            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-shipping-fast text-primary"></i>
                    <span>Status Pesanan Saya</span>
                </h6>
                <a href="{{ route('order.history') }}" class="small text-primary fw-bold text-decoration-none">
                    Lihat Semua Pesanan ({{ $totalOrdersCount }}) <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                {{-- Menunggu Pembayaran --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 hover-lift transition-all bg-white">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 50px; height: 50px;">
                                <i class="fas fa-wallet fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-1">{{ $pendingPaymentCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Menunggu Pembayaran</small>
                        </div>
                    </a>
                </div>

                {{-- Pesanan Diproses --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 hover-lift transition-all bg-white">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 50px; height: 50px;">
                                <i class="fas fa-box fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-1">{{ $processingCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Sedang Diproses</small>
                        </div>
                    </a>
                </div>

                {{-- Dalam Pengiriman --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 hover-lift transition-all bg-white">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 50px; height: 50px;">
                                <i class="fas fa-truck fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-1">{{ $shippingCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Dalam Pengiriman</small>
                        </div>
                    </a>
                </div>

                {{-- Pesanan Selesai --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100 hover-lift transition-all bg-white">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 50px; height: 50px;">
                                <i class="fas fa-check-circle fs-5"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-1">{{ $completedCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Pesanan Selesai</small>
                        </div>
                    </a>
                </div>
            </div>

            {{-- QUICK NAVIGATION CARDS (Wishlist & Cart) --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="far fa-heart fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Wishlist &amp; Favorit Saya</h6>
                                <small class="text-muted">Produk impian yang Anda simpan</small>
                            </div>
                        </div>
                        <a href="{{ route('wishlist.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                            Buka Wishlist
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-row align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="fas fa-shopping-bag fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Keranjang Belanja</h6>
                                <small class="text-muted">Item siap checkout ({{ $cartCount }} items)</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" onclick="loadOffcanvasCart()">
                            Lihat Keranjang
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
}
</style>
@endsection
