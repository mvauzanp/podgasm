@extends('layouts.auth')

@section('content_auth')
<!-- Pastikan FontAwesome ter-load untuk Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="card auth-card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 850px; overflow: hidden;">
    <div class="row g-0">
        
        {{-- BAGIAN KIRI: Banner/Ilustrasi (Logo Putih Terang di Sini) --}}
        <div class="col-md-5 d-none d-md-block" style="background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%); padding: 45px 35px; color: white; display: flex; flex-direction: column; justify-content: center;">
            <div class="mb-4">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('LogoPodgasm.png') }}" 
                         alt="Podgasm Logo" 
                         style="height: 56px; width: auto; filter: brightness(0) invert(1) drop-shadow(0 4px 12px rgba(0,0,0,0.18));"
                         class="object-fit-contain">
                </a>
            </div>
            <h2 class="fw-black mb-3" style="font-weight: 800; letter-spacing: -0.5px;">PODGASM</h2>
            <p class="mb-4" style="font-size: 0.95rem; opacity: 0.92; line-height: 1.6;">Selamat datang kembali di Sistem Penjualan Terintegrasi. Kelola inventaris, pantau penjualan retail &amp; grosir Anda.</p>
            <div class="mt-auto">
                <small style="opacity: 0.85;"><i class="fas fa-shield-alt me-1"></i> Secure Login System</small>
            </div>
        </div>

        {{-- BAGIAN KANAN: Area Formulir Login --}}
        <div class="col-md-7 p-4 p-md-5 d-flex flex-column justify-content-center">
            
            {{-- LOGO HANYA MUNCUL DI TAMPILAN MOBILE (HP) --}}
            <div class="d-md-none text-center mb-4">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" style="height: 48px; width: auto;" class="object-fit-contain">
                </a>
            </div>

            <div class="mb-4 text-center text-md-start">
                <h4 class="fw-bold text-dark mb-1">Selamat Datang 👋</h4>
                <p class="text-muted small mb-0">Silakan masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- ALERT ERROR (Jika kombinasi email/password salah) --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4 py-2 px-3">
                    <div class="d-flex align-items-center small">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span>Email atau Password yang Anda masukkan salah.</span>
                    </div>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                
                {{-- INPUT EMAIL --}}
                <div class="input-icon-wrapper mb-4">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control form-control-lg custom-input @error('email') is-invalid @enderror" placeholder="Alamat Email" required value="{{ old('email') }}" autofocus>
                </div>

                {{-- INPUT PASSWORD --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control form-control-lg custom-input" placeholder="Password" required>
                    <i class="fas fa-eye-slash icon-right toggle-password" id="togglePassword" title="Tampilkan Password"></i>
                </div>

                {{-- REMEMBER ME & FORGOT PASSWORD --}}
                <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input cursor-pointer" id="remember" name="remember">
                        <label class="form-check-label small text-muted cursor-pointer" for="remember">Ingat Saya</label>
                    </div>
                    <a href="#" class="small fw-semibold text-decoration-none">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold shadow-sm mb-4">
                    Masuk Sistem
                </button>
            </form>

            <div class="text-center mt-auto pt-3 border-top">
                <span class="text-muted small">Belum memiliki akun?</span>
                <a href="{{ url('/register') }}" class="fw-bold text-primary text-decoration-none ms-1 small">Daftar Sekarang</a>
            </div>
            
        </div>
    </div>
</div>

<style>
    /* DESAIN INPUT MODERN */
    .input-icon-wrapper {
        position: relative;
    }
    
    /* Ikon Kiri (Ikon utama) */
    .input-icon-wrapper .icon-left {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 1.1rem;
        transition: 0.3s;
        z-index: 4;
    }

    /* Ikon Kanan (Mata Password) */
    .input-icon-wrapper .icon-right {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
        z-index: 4;
    }
    .input-icon-wrapper .icon-right:hover {
        color: #0d6efd;
    }

    /* Styling Kolom Input */
    .custom-input {
        padding-left: 50px !important; /* Ruang untuk ikon kiri */
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        font-size: 0.95rem;
    }
    
    /* Tambahan padding kanan khusus untuk input password */
    #passwordInput {
        padding-right: 45px !important; 
    }

    .custom-input:focus {
        background-color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    /* Warna ikon berubah saat input fokus */
    .input-icon-wrapper:focus-within .icon-left {
        color: #0d6efd;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fitur Show/Hide Password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function (e) {
                // Toggle tipe input antara password & text
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle ikon mata
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>
@endsection