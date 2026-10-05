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
                        <span>Diskon Grosir Otomatis Beli Banyak Lebih Hemat</span>
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

        {{-- BAGIAN KANAN: Area Formulir Pendaftaran --}}
        <div class="col-md-7 p-4 p-lg-4 d-flex flex-column justify-content-center">
            
            {{-- LOGO HANYA MUNCUL DI MOBILE --}}
            <div class="d-md-none text-center mb-3">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" style="height: 42px; width: auto;" class="object-fit-contain">
                </a>
            </div>

            <div class="mb-3 text-center text-md-start">
                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">Daftar Akun Baru 👋</h5>
                <p class="text-muted small mb-0" style="font-size: 0.82rem;">Lengkapi data diri Anda untuk mulai berbelanja</p>
            </div>

            {{-- ALERT ERROR --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3 py-2 px-3">
                    <ul class="mb-0 small ps-3" style="font-size: 0.8rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                {{-- INPUT NAMA --}}
                <div class="input-icon-wrapper mb-2">
                    <i class="fas fa-user icon-left"></i>
                    <input type="text" name="name" class="form-control custom-input @error('name') is-invalid @enderror" placeholder="Nama Lengkap" required value="{{ old('name') }}" autofocus autocomplete="name">
                </div>

                {{-- INPUT EMAIL --}}
                <div class="input-icon-wrapper mb-2">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control custom-input @error('email') is-invalid @enderror" placeholder="Alamat Email" required value="{{ old('email') }}" autocomplete="email">
                </div>

                {{-- INPUT PASSWORD --}}
                <div class="input-icon-wrapper mb-2">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="passwordRegister" class="form-control custom-input @error('password') is-invalid @enderror" placeholder="Password Baru" required autocomplete="new-password">
                    <button type="button" class="btn btn-link toggle-password-btn" onclick="togglePasswordVisibility('passwordRegister', this)" tabindex="-1" title="Lihat Password">
                        <i class="fas fa-eye text-muted"></i>
                    </button>
                </div>

                {{-- PETUNJUK SYARAT PASSWORD (REAL-TIME CHECKLIST) --}}
                <div class="password-requirements-box mb-2 p-2 px-3 rounded-3 bg-light border">
                    <div class="d-flex align-items-center mb-1 text-muted" style="font-size: 0.74rem; font-weight: 600;">
                        <i class="fas fa-shield-alt me-1 text-primary"></i> Ketentuan Password:
                    </div>
                    <div class="row g-1 text-muted" style="font-size: 0.72rem;">
                        <div class="col-12 req-item" id="req-length">
                            <i class="fas fa-circle-notch text-muted me-1"></i> Minimal <strong>8 karakter</strong>
                        </div>
                        <div class="col-12 req-item" id="req-case">
                            <i class="fas fa-circle-notch text-muted me-1"></i> Mengandung <strong>huruf besar (A-Z)</strong> &amp; <strong>huruf kecil (a-z)</strong>
                        </div>
                        <div class="col-12 req-item" id="req-number">
                            <i class="fas fa-circle-notch text-muted me-1"></i> Mengandung setidaknya <strong>1 angka (0-9)</strong>
                        </div>
                    </div>
                </div>

                {{-- INPUT KONFIRMASI PASSWORD --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-check-circle icon-left"></i>
                    <input type="password" name="password_confirmation" id="passwordConfirm" class="form-control custom-input" placeholder="Konfirmasi Password" required autocomplete="new-password">
                    <button type="button" class="btn btn-link toggle-password-btn" onclick="togglePasswordVisibility('passwordConfirm', this)" tabindex="-1" title="Lihat Password">
                        <i class="fas fa-eye text-muted"></i>
                    </button>
                </div>

                <button type="submit" id="submitRegisterBtn" class="btn btn-primary w-100 rounded-3 fw-bold shadow-sm mb-3 py-2" style="font-size: 0.92rem; height: 44px;">
                    Daftar Sekarang
                </button>
            </form>

            <div class="text-center mt-auto pt-2 border-top">
                <span class="text-muted" style="font-size: 0.82rem;">Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none ms-1" style="font-size: 0.82rem;">Login di sini</a>
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

    .toggle-password-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        border: none;
        background: transparent;
        padding: 4px 8px;
        text-decoration: none;
    }

    .toggle-password-btn:hover i {
        color: #0284c7 !important;
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

    .password-requirements-box {
        background-color: #f8fafc !important;
        border: 1px dashed #cbd5e1 !important;
    }

    .req-item {
        transition: color 0.2s ease;
    }

    .req-item.valid {
        color: #0f766e !important;
        font-weight: 600;
    }

    .req-item.valid i {
        color: #10b981 !important;
    }
</style>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('passwordRegister');
        const reqLength = document.getElementById('req-length');
        const reqCase = document.getElementById('req-case');
        const reqNumber = document.getElementById('req-number');

        function updateReq(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.add('valid');
                icon.className = 'fas fa-check-circle text-success me-1';
            } else {
                element.classList.remove('valid');
                icon.className = 'fas fa-circle-notch text-muted me-1';
            }
        }

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            
            // 1. Min 8 karakter
            updateReq(reqLength, val.length >= 8);

            // 2. Huruf besar & kecil
            const hasUpper = /[A-Z]/.test(val);
            const hasLower = /[a-z]/.test(val);
            updateReq(reqCase, hasUpper && hasLower);

            // 3. Mengandung angka
            const hasNum = /\d/.test(val);
            updateReq(reqNumber, hasNum);
        });
    });
</script>
@endsection