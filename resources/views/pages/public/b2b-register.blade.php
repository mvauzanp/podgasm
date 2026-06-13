@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Daftar Program B2B - Menjadi Reseller Podgasm</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        Daftarkan toko Anda sebagai reseller Podgasm dan dapatkan harga grosir eksklusif. 
                        Tim admin akan melakukan review terhadap aplikasi Anda dalam 1-3 hari kerja.
                    </p>

                    <form method="POST" action="{{ route('b2b.store') }}" enctype="multipart/form-data" id="b2bForm">
                        @csrf

                        <div class="row">
                            <!-- Owner Name -->
                            <div class="col-md-6 mb-3">
                                <label for="owner_name" class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                                    id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Store Name -->
                            <div class="col-md-6 mb-3">
                                <label for="store_name" class="form-label">Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('store_name') is-invalid @enderror" 
                                    id="store_name" name="store_name" value="{{ old('store_name') }}" required>
                                @error('store_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">No. Telpon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lokasi Usaha <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                            <small class="text-muted">Sertakan alamat lengkap, kota, provinsi, dan kode pos</small>
                            @error('address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- KTP File -->
                        <div class="mb-3">
                            <label for="ktp_file" class="form-label">Foto KTP Pemilik <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('ktp_file') is-invalid @enderror" 
                                id="ktp_file" name="ktp_file" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Format: PDF, JPG, atau PNG. Maksimal 5MB. Foto harus jelas dan mudah dibaca.
                            </small>
                            @error('ktp_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Storefront Photo -->
                        <div class="mb-3">
                            <label for="storefront_photo" class="form-label">Foto Tampak Depan Lokasi Usaha <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('storefront_photo') is-invalid @enderror" 
                                id="storefront_photo" name="storefront_photo" accept=".jpg,.jpeg,.png" required>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> 
                                Format: JPG atau PNG. Maksimal 5MB. Foto harus menampilkan toko dengan jelas.
                            </small>
                            @error('storefront_photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Kirim Aplikasi
                            </button>
                        </div>

                        <p class="text-muted text-center mt-3 mb-0">
                            <small>Sudah terdaftar? <a href="{{ route('login') }}">Login di sini</a></small>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb text-warning me-2"></i>Keuntungan Menjadi Reseller</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Harga grosir eksklusif untuk reseller terdaftar</li>
                        <li>Akses ke dashboard penjualan dengan analitik lengkap</li>
                        <li>Support khusus dari tim Podgasm</li>
                        <li>Program komisi atau insentif menarik</li>
                        <li>Akses prioritas ke produk-produk terbaru</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-danger {
        color: #dc3545;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
    }
    
    .card {
        border-radius: 8px;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('b2bForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
        });
    });
</script>
@endpush
@endsection
