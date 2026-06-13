@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-4">Edit Profil</h4>

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <strong>Gagal memperbarui profil!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <form action="{{ route('profile.update') }}" method="POST" class="needs-validation">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', Auth::user()->name) }}"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input 
                                type="email" 
                                class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', Auth::user()->email) }}"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Email harus unik dan tidak boleh sama dengan email lain
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label fw-bold">Nomor Telepon</label>
                            <input 
                                type="text" 
                                class="form-control rounded-3 @error('phone') is-invalid @enderror" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone', Auth::user()->phone) }}"
                                placeholder="Contoh: 08123456789"
                            >
                            @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label fw-bold">Alamat</label>
                            <textarea 
                                class="form-control rounded-3 @error('address') is-invalid @enderror" 
                                id="address" 
                                name="address" 
                                rows="4"
                                placeholder="Masukkan alamat lengkap Anda"
                            >{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold">
                                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-pill w-100 py-2 fw-bold">
                                    <i class="fas fa-arrow-left me-2"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info alert-dismissible fade show rounded-4 mt-4" role="alert">
                <i class="fas fa-lock me-2"></i>
                <strong>Keamanan Akun:</strong> Jika ingin mengubah password, silakan hubungi administrator atau gunakan fitur "Lupa Password".
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div>
@endsection
