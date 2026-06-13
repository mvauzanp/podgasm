@extends('layouts.auth')

@section('content_auth')
<!-- Pastikan FontAwesome ter-load untuk Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="card auth-card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 850px; overflow: hidden;">
    <div class="row g-0">
        
        {{-- BAGIAN KIRI: Banner/Ilustrasi (Opsional, agar lebih premium) --}}
        <div class="col-md-4 d-none d-md-block" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); padding: 40px; color: white; display: flex; flex-direction: column; justify-content: center;">
            <h2 class="fw-black mb-3" style="font-weight: 800;">PODGASM.</h2>
            <p class="mb-4" style="font-size: 0.95rem; opacity: 0.9;">Sistem Penjualan Multi-Channel Terintegrasi. Bergabunglah bersama kami untuk kemudahan transaksi retail maupun grosir.</p>
            <div class="mt-auto">
                <small style="opacity: 0.7;">&copy; 2026 Gudang Pusat</small>
            </div>
        </div>

        {{-- BAGIAN KANAN: Area Formulir --}}
        <div class="col-md-8 p-4 p-md-5">
            
            {{-- ALERT ERROR --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. TAMPILAN PEMILIHAN TIPE AKUN --}}
            <div id="typeSelector" class="form-view fade-in" style="display: {{ old('account_type') ? 'none' : 'block' }};">
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-dark">Daftar Akun Baru</h3>
                    <p class="text-muted">Pilih jenis kemitraan yang sesuai dengan Anda</p>
                </div>

                <div class="row g-4">
                    <div class="col-sm-6">
                        <button type="button" class="btn w-100 h-100 p-4 rounded-4 type-btn custom-card-btn" data-type="b2c" id="btnB2C">
                            <div class="icon-circle bg-primary-subtle text-primary mb-3 mx-auto">
                                <i class="fas fa-shopping-bag fa-2x"></i>
                            </div>
                            <h5 class="fw-bold mb-1 text-dark">Retail</h5>
                            <small class="text-muted">Pembeli langsung (B2C)</small>
                        </button>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn w-100 h-100 p-4 rounded-4 type-btn custom-card-btn" data-type="b2b" id="btnB2B">
                            <div class="icon-circle bg-success-subtle text-success mb-3 mx-auto">
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                            <h5 class="fw-bold mb-1 text-dark">Partner</h5>
                            <small class="text-muted">Mitra grosir (B2B)</small>
                        </button>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <span class="text-muted small">Sudah punya akun?</span>
                    <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none ms-1">Login di sini</a>
                </div>
            </div>

            {{-- 2. FORM B2C (RETAIL) --}}
            <form id="formB2C" class="form-view fade-in" action="{{ route('register') }}" method="POST" style="display: {{ old('account_type') == 'b2c' ? 'block' : 'none' }};">
                @csrf
                <input type="hidden" name="account_type" value="b2c">

                <button type="button" class="btn btn-link text-decoration-none text-muted p-0 mb-4 backBtn">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </button>

                <h4 class="fw-bold mb-1">Daftar Retail</h4>
                <p class="text-muted mb-4 small">Lengkapi data diri Anda untuk mulai berbelanja.</p>

                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" class="form-control form-control-lg custom-input @error('name') is-invalid @enderror" placeholder="Nama Lengkap" required value="{{ old('name') }}">
                </div>

                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control form-control-lg custom-input @error('email') is-invalid @enderror" placeholder="Email Aktif" required value="{{ old('email') }}">
                </div>

                <div class="input-icon-wrapper mb-3">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control form-control-lg custom-input @error('password') is-invalid @enderror" placeholder="Password (Min. 8 karakter)" required>
                </div>

                <div class="input-icon-wrapper mb-4">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" name="password_confirmation" class="form-control form-control-lg custom-input" placeholder="Konfirmasi Password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold">Daftar Sekarang</button>
            </form>

            {{-- 3. FORM B2B (PARTNER/RESELLER) --}}
            <form id="formB2B" class="form-view fade-in" action="{{ route('register.b2b') }}" method="POST" enctype="multipart/form-data" style="display: {{ old('account_type') == 'b2b' ? 'block' : 'none' }};">
                @csrf
                <input type="hidden" name="account_type" value="b2b">

                <button type="button" class="btn btn-link text-decoration-none text-muted p-0 mb-3 backBtn">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </button>

                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-store fs-5"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Daftar Partner Grosir</h4>
                        <small class="text-muted">Isi formulir ini untuk mendapatkan akses harga B2B</small>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Section 1: Informasi Usaha (Kiri & Kanan) --}}
                    <div class="col-12 mt-2">
                        <h6 class="fw-bold text-dark mb-2">1. Informasi Usaha</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user-tie"></i>
                            <input type="text" name="owner_name" class="form-control form-control-lg custom-input" placeholder="Nama Pemilik (Sesuai KTP)" required value="{{ old('owner_name') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-store-alt"></i>
                            <input type="text" name="store_name" class="form-control form-control-lg custom-input" placeholder="Nama Toko / Usaha" required value="{{ old('store_name') }}">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt" style="top: 25px;"></i>
                            <textarea name="address" class="form-control form-control-lg custom-input" rows="2" placeholder="Alamat Lengkap Toko (Jalan, Kota, Kode Pos)" required>{{ old('address') }}</textarea>
                        </div>
                    </div>

                    {{-- Section 2: Kontak & Keamanan --}}
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-dark mb-2">2. Kontak & Akun</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" inputmode="numeric" name="phone" class="form-control form-control-lg custom-input" placeholder="No. WhatsApp" required value="{{ old('phone') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control form-control-lg custom-input" placeholder="Email Aktif" required value="{{ old('email') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control form-control-lg custom-input" placeholder="Password (Min. 8 Karakter)" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-icon-wrapper">
                            <i class="fas fa-check-double"></i>
                            <input type="password" name="password_confirmation" class="form-control form-control-lg custom-input" placeholder="Ulangi Password" required>
                        </div>
                    </div>

                    {{-- Section 3: Dokumen --}}
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-dark mb-2">3. Dokumen Verifikasi <span class="text-muted fw-normal small">(Max 5MB)</span></h6>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted mb-1 ms-1">Foto KTP</label>
                        <input type="file" name="ktp_file" class="form-control custom-file-input" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1 ms-1">Foto Depan Toko</label>
                        <input type="file" name="storefront_photo" class="form-control custom-file-input" accept=".jpg,.jpeg,.png" required>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-success btn-lg w-100 rounded-3 fw-bold shadow-sm">
                        Kirim Pengajuan Kemitraan
                    </button>
                    <p class="text-center text-muted mt-2 mb-0" style="font-size: 0.8rem;">
                        <i class="fas fa-shield-alt me-1"></i> Data Anda dienkripsi dan diverifikasi dalam 1x24 jam.
                    </p>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
    /* ANIMASI MUNCUL HALUS */
    .fade-in {
        animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* TOMBOL PILIHAN TIPE AKUN */
    .custom-card-btn {
        background: #fff;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .custom-card-btn:hover {
        border-color: #0d6efd;
        background: #f8faff;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.1);
    }
    .icon-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* DESAIN INPUT MODERN (IKON DI DALAM) */
    .input-icon-wrapper {
        position: relative;
    }
    .input-icon-wrapper i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-size: 1.1rem;
        transition: 0.3s;
    }
    .custom-input {
        padding-left: 50px !important; /* Ruang untuk ikon */
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
    /* Warna ikon berubah saat input fokus */
    .input-icon-wrapper:focus-within i {
        color: #0d6efd;
    }

    /* DESAIN INPUT FILE KHUSUS */
    .custom-file-input {
        background-color: #f8f9fa;
        border: 1px dashed #adb5bd;
        border-radius: 10px;
        padding: 10px;
        cursor: pointer;
    }
    .custom-file-input:hover {
        background-color: #fff;
        border-color: #198754;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeButtons = document.querySelectorAll('.type-btn');
        const formB2C = document.getElementById('formB2C');
        const formB2B = document.getElementById('formB2B');
        const typeSelector = document.getElementById('typeSelector');
        const backButtons = document.querySelectorAll('.backBtn');

        // Pilih type (B2C atau B2B)
        typeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                typeSelector.style.display = 'none';
                
                if (type === 'b2c') {
                    formB2C.style.display = 'block';
                } else if (type === 'b2b') {
                    formB2B.style.display = 'block';
                }
            });
        });

        // Tombol Kembali
        backButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                typeSelector.style.display = 'block';
                formB2C.style.display = 'none';
                formB2B.style.display = 'none';
            });
        });
    });
</script>
@endsection