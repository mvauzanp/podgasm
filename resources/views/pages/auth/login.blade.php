@extends('layouts.auth')

@section('content_auth')
<!-- Pastikan FontAwesome ter-load untuk Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="w-100 mx-auto" style="max-width: 780px;">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('home') }}" class="back-home-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <div class="card auth-card shadow-lg border-0 rounded-4 mx-auto" style="overflow: hidden;">
        <div class="row g-0">
        
        {{-- BAGIAN KIRI: Banner/Ilustrasi --}}
        <div class="col-md-5 d-none d-md-flex" style="background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%); padding: 36px 28px; color: white; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="mb-3">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('LogoPodgasm.png') }}" 
                             alt="Podgasm Logo" 
                             style="height: 46px; width: auto; filter: brightness(0) invert(1) drop-shadow(0 4px 12px rgba(0,0,0,0.18));"
                             class="object-fit-contain">
                    </a>
                </div>
                <h4 class="fw-bold mb-1" style="letter-spacing: -0.3px;">PODGASM</h4>
                <p class="text-white-50 text-uppercase fw-semibold mb-4" style="font-size: 0.7rem; letter-spacing: 1.2px;">Retail &amp; Wholesale Vape Store</p>
                
                <div class="d-flex flex-column gap-2 mb-3" style="font-size: 0.8rem; opacity: 0.95;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle" style="font-size: 0.75rem; color: #a5f3fc;"></i>
                        <span>Produk Resmi &amp; 100% Original</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle" style="font-size: 0.75rem; color: #a5f3fc;"></i>
                        <span>Diskon Grosir Otomatis B2B &amp; B2C</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle" style="font-size: 0.75rem; color: #a5f3fc;"></i>
                        <span>Pengiriman Cepat &amp; Terintegrasi</span>
                    </div>
                </div>
            </div>
            
            <div class="pt-3 border-top border-white border-opacity-10 d-flex align-items-center justify-content-between">
                <small style="font-size: 0.72rem; opacity: 0.85;">
                    <i class="fas fa-shield-alt me-1"></i> Transaksi Aman &amp; Terverifikasi
                </small>
            </div>
        </div>

        {{-- BAGIAN KANAN: Area Formulir Login --}}
        <div class="col-md-7 p-4 p-lg-4 d-flex flex-column justify-content-center">
            
            {{-- LOGO HANYA MUNCUL DI MOBILE --}}
            <div class="d-md-none text-center mb-3">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" style="height: 42px; width: auto;" class="object-fit-contain">
                </a>
            </div>

            <div class="mb-3 text-center text-md-start">
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">Selamat Datang 👋</h5>
                <p class="text-muted small mb-0" style="font-size: 0.82rem;">Silakan masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- ALERT ERROR --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3 py-2 px-3">
                    <div class="d-flex align-items-center small" style="font-size: 0.8rem;">
                        <i class="fas fa-exclamation-circle me-2 flex-shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                
                {{-- INPUT EMAIL --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control custom-input @error('email') is-invalid @enderror" placeholder="Alamat Email" required value="{{ old('email') }}" autofocus autocomplete="email">
                </div>

                {{-- INPUT PASSWORD --}}
                <div class="input-icon-wrapper mb-2">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control custom-input" placeholder="Password" required autocomplete="current-password">
                    <i class="fas fa-eye-slash icon-right toggle-password" id="togglePassword" title="Tampilkan Password"></i>
                </div>

                {{-- REMEMBER ME & FORGOT PASSWORD --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cursor-pointer" id="remember" name="remember" style="font-size: 0.85rem;">
                        <label class="form-check-label text-muted cursor-pointer" for="remember" style="font-size: 0.8rem;">Ingat Saya</label>
                    </div>
                    <a href="#" class="fw-semibold text-decoration-none" style="font-size: 0.8rem; color: #0284c7;">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold shadow-sm mb-3 py-2" style="font-size: 0.92rem; height: 44px;">
                    Masuk Sistem
                </button>
            </form>

            <div class="text-center mt-auto pt-2 border-top">
                <span class="text-muted" style="font-size: 0.82rem;">Belum memiliki akun?</span>
                <a href="{{ url('/register') }}" class="fw-bold text-primary text-decoration-none ms-1" style="font-size: 0.82rem;">Daftar Sekarang</a>
            </div>
            
        </div>
    </div>
</div>
</div>

<style>
    /* DESAIN INPUT MODERN & PROPOSIONAL */
    .input-icon-wrapper {
        position: relative;
    }
    
    .input-icon-wrapper .icon-left {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        transition: 0.2s;
        z-index: 4;
    }

    .input-icon-wrapper .icon-right {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
        cursor: pointer;
        transition: 0.2s;
        z-index: 4;
        padding: 4px;
    }
    
    .input-icon-wrapper .icon-right:hover {
        color: #0284c7;
    }

    .custom-input {
        padding-left: 44px !important;
        padding-right: 42px !important;
        height: 44px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.88rem;
    }

    .custom-input:focus {
        background-color: #ffffff;
        border-color: #09afb9;
        box-shadow: 0 0 0 3px rgba(9, 175, 185, 0.12);
    }

    .input-icon-wrapper:focus-within .icon-left {
        color: #09afb9;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function (e) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>
@endsection