@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- HEADER NAVIGATION --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Pengaturan Profil &amp; Keamanan</h4>
                    <p class="text-muted small mb-0">Perbarui data pribadi Anda atau ubah password akun di sini.</p>
                </div>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-semibold">
                    <i class="fas fa-arrow-left me-1.5"></i> Kembali ke Profil
                </a>
            </div>

            {{-- ERROR ALERTS --}}
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 py-3 px-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-exclamation-triangle me-2 text-danger fs-5"></i>
                    <strong class="text-danger">Gagal Memperbarui!</strong>
                </div>
                <ul class="mb-0 small ps-4">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- NAV TABS (Profil & Password) --}}
            <ul class="nav nav-pills custom-profile-tabs mb-4 p-1.5 bg-light rounded-pill border" id="profileTab" role="tablist">
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold w-100 py-2.5 small" id="info-tab" data-bs-toggle="tab" data-bs-target="#infoPane" type="button" role="tab" aria-controls="infoPane" aria-selected="true">
                        <i class="fas fa-user-edit me-2"></i> Informasi Pribadi &amp; Alamat
                    </button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link rounded-pill fw-bold w-100 py-2.5 small" id="security-tab" data-bs-toggle="tab" data-bs-target="#securityPane" type="button" role="tab" aria-controls="securityPane" aria-selected="false">
                        <i class="fas fa-key me-2"></i> Ubah Password Mandiri
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabContent">

                {{-- TAB 1: INFORMASI PRIBADI & ALAMAT --}}
                <div class="tab-pane fade show active" id="infoPane" role="tabpanel" aria-labelledby="info-tab">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-md-5">
                            <form action="{{ route('profile.update') }}" method="POST" class="needs-validation">
                                @csrf
                                @method('PUT')

                                <h6 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center gap-2">
                                    <i class="fas fa-id-card text-primary"></i> Data Diri &amp; Kontak
                                </h6>

                                {{-- NAMA LENGKAP --}}
                                <div class="mb-4">
                                    <label for="name" class="form-label fw-bold text-dark small">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                                        <input 
                                            type="text" 
                                            class="form-control bg-light border-start-0 ps-0 @error('name') is-invalid @enderror" 
                                            id="name" 
                                            name="name" 
                                            value="{{ old('name', Auth::user()->name) }}"
                                            required
                                        >
                                    </div>
                                    @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- EMAIL --}}
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-bold text-dark small">Alamat Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope"></i></span>
                                        <input 
                                            type="email" 
                                            class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                            id="email" 
                                            name="email" 
                                            value="{{ old('email', Auth::user()->email) }}"
                                            required
                                        >
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NOMOR TELEPON --}}
                                <div class="mb-4">
                                    <label for="phone" class="form-label fw-bold text-dark small">Nomor Telepon / WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-phone"></i></span>
                                        <input 
                                            type="text" 
                                            class="form-control bg-light border-start-0 ps-0 @error('phone') is-invalid @enderror" 
                                            id="phone" 
                                            name="phone" 
                                            value="{{ old('phone', Auth::user()->phone) }}"
                                            placeholder="Contoh: 08123456789"
                                        >
                                    </div>
                                    @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ALAMAT LENGKAP --}}
                                <div class="mb-4">
                                    <label for="address" class="form-label fw-bold text-dark small">Alamat Pengiriman Lengkap</label>
                                    <textarea 
                                        class="form-control bg-light @error('address') is-invalid @enderror" 
                                        id="address" 
                                        name="address" 
                                        rows="3"
                                        placeholder="Masukkan nama jalan, nomor rumah, RT/RW, kecamatan, kota, dan kode pos"
                                    >{{ old('address', Auth::user()->address) }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted mt-1.5 d-block style-italic" style="font-size: 0.78rem;">
                                        <i class="fas fa-info-circle me-1"></i> Alamat ini akan digunakan secara otomatis saat Anda melakukan checkout barang.
                                    </small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                    <a href="{{ route('profile.show') }}" class="btn btn-light rounded-pill px-4 fw-semibold border">
                                        Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="fas fa-save me-1.5"></i> Simpan Informasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: UBAH PASSWORD MANDIRI --}}
                <div class="tab-pane fade" id="securityPane" role="tabpanel" aria-labelledby="security-tab">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-md-5">
                            <form action="{{ route('profile.updatePassword') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <h6 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center gap-2">
                                    <i class="fas fa-lock text-warning"></i> Keamanan Password Akun
                                </h6>

                                {{-- PASSWORD SAAT INI --}}
                                <div class="mb-4">
                                    <label for="current_password" class="form-label fw-bold text-dark small">Password Saat Ini <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                        <input 
                                            type="password" 
                                            class="form-control bg-light border-start-0 ps-0 @error('current_password') is-invalid @enderror" 
                                            id="current_password" 
                                            name="current_password" 
                                            placeholder="Masukkan password yang Anda gunakan sekarang"
                                            required
                                        >
                                    </div>
                                    @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- PASSWORD BARU --}}
                                <div class="mb-4">
                                    <label for="new_password" class="form-label fw-bold text-dark small">Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-key"></i></span>
                                        <input 
                                            type="password" 
                                            class="form-control bg-light border-start-0 ps-0 @error('new_password') is-invalid @enderror" 
                                            id="new_password" 
                                            name="new_password" 
                                            placeholder="Password baru (Minimal 8 Karakter)"
                                            required
                                        >
                                    </div>
                                    @error('new_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- KONFIRMASI PASSWORD BARU --}}
                                <div class="mb-4">
                                    <label for="new_password_confirmation" class="form-label fw-bold text-dark small">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-check-double"></i></span>
                                        <input 
                                            type="password" 
                                            class="form-control bg-light border-start-0 ps-0" 
                                            id="new_password_confirmation" 
                                            name="new_password_confirmation" 
                                            placeholder="Ulangi password baru Anda"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                    <a href="{{ route('profile.show') }}" class="btn btn-light rounded-pill px-4 fw-semibold border">
                                        Batal
                                    </a>
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark">
                                        <i class="fas fa-shield-alt me-1.5"></i> Perbarui Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
.custom-profile-tabs .nav-link {
    color: #64748b;
    transition: all 0.25s ease;
}
.custom-profile-tabs .nav-link.active {
    background-color: #ffffff !important;
    color: #09afb9 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
body.dark-mode .custom-profile-tabs .nav-link.active {
    background-color: #1e293b !important;
    color: #00d4ff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#securityTab') {
        const securityTabBtn = document.getElementById('security-tab');
        if (securityTabBtn) {
            const tab = new bootstrap.Tab(securityTabBtn);
            tab.show();
        }
    }
});
</script>
@endsection
