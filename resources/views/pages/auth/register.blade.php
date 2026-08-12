@extends('layouts.auth')

@section('content_auth')
<!-- Pastikan FontAwesome ter-load untuk Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="card auth-card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 850px; overflow: hidden;">
    <div class="row g-0">
        
        {{-- BAGIAN KIRI: Banner/Ilustrasi --}}
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
            <p class="mb-4" style="font-size: 0.95rem; opacity: 0.92; line-height: 1.6;">Sistem Penjualan Terintegrasi. Bergabunglah bersama kami untuk kemudahan berbelanja &amp; hemat harga grosir (Pricing Tier).</p>
            <div class="mt-auto">
                <small style="opacity: 0.85;">&copy; {{ date('Y') }} Gudang Pusat</small>
            </div>
        </div>

        {{-- BAGIAN KANAN: Area Formulir Pendaftaran --}}
        <div class="col-md-7 p-4 p-md-5 flex-column justify-content-center">
            
            {{-- LOGO HANYA MUNCUL DI TAMPILAN MOBILE (HP) --}}
            <div class="d-md-none text-center mb-4">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" style="height: 48px; width: auto;" class="object-fit-contain">
                </a>
            </div>

            <div class="mb-4 text-center text-md-start">
                <h4 class="fw-bold text-dark mb-1">Daftar Akun Baru 👋</h4>
                <p class="text-muted small mb-0">Lengkapi data diri Anda untuk mulai berbelanja</p>
            </div>

            {{-- ALERT ERROR --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4 py-2 px-3">
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                {{-- INPUT NAMA --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-user icon-left"></i>
                    <input type="text" name="name" class="form-control form-control-lg custom-input @error('name') is-invalid @enderror" placeholder="Nama Lengkap" required value="{{ old('name') }}" autofocus>
                </div>

                {{-- INPUT EMAIL --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" name="email" class="form-control form-control-lg custom-input @error('email') is-invalid @enderror" placeholder="Alamat Email" required value="{{ old('email') }}">
                </div>

                {{-- INPUT PASSWORD --}}
                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password" name="password" id="passwordRegister" class="form-control form-control-lg custom-input @error('password') is-invalid @enderror" placeholder="Password (Min. 8 Karakter)" required>
                </div>

                {{-- INPUT KONFIRMASI PASSWORD --}}
                <div class="input-icon-wrapper mb-4">
                    <i class="fas fa-check-circle icon-left"></i>
                    <input type="password" name="password_confirmation" class="form-control form-control-lg custom-input" placeholder="Konfirmasi Password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold shadow-sm mb-4">
                    Daftar Sekarang
                </button>
            </form>

            <div class="text-center mt-auto pt-3 border-top">
                <span class="text-muted small">Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none ms-1 small">Login di sini</a>
            </div>

        </div>
    </div>
</div>

<style>
    /* DESAIN INPUT MODERN */
    .input-icon-wrapper {
        position: relative;
    }
    
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

    .custom-input {
        padding-left: 50px !important;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        font-size: 0.95rem;
    }

    .custom-input:focus {
        background-color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }

    .input-icon-wrapper:focus-within .icon-left {
        color: #0d6efd;
    }
</style>
@endsection