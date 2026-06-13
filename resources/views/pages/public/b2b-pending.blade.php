@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-warning shadow">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-hourglass-half fa-5x text-warning"></i>
                    </div>

                    <h3 class="fw-bold mb-2">Akun Anda Sedang Dalam Review</h3>
                    
                    <p class="text-muted mb-4">
                        Terima kasih telah mendaftar sebagai reseller Podgasm! 
                        <br><br>
                        Tim admin kami akan melakukan review pada dokumen dan informasi bisnis Anda dalam waktu <strong>1-3 hari kerja</strong>.
                    </p>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan menerima notifikasi email setelah admin melakukan review aplikasi Anda.
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body text-start">
                            <h6 class="fw-bold mb-3">Status Aplikasi Anda:</h6>
                            <div class="mb-2">
                                <small class="text-muted">Email Terdaftar:</small>
                                <p class="fw-semibold">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Nama Pemilik:</small>
                                <p class="fw-semibold">{{ auth()->user()->name }}</p>
                            </div>
                            <div>
                                <small class="text-muted">Status Persetujuan:</small>
                                <p><span class="badge bg-warning text-dark">Menunggu Persetujuan</span></p>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-4">
                        Sementara akun Anda belum disetujui, Anda tidak bisa melakukan pembelian. <br>
                        Fitur akan terbuka setelah admin menyetujui aplikasi Anda.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-circle me-2"></i> Lihat Profile
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-list-check text-primary me-2"></i> Checklist Dokumen Anda</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Foto KTP Pemilik <strong>✓ Sudah diunggah</strong></small>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Foto Tampak Depan Lokasi Usaha <strong>✓ Sudah diunggah</strong></small>
                    </div>
                    <div>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Informasi Bisnis <strong>✓ Sudah diisi</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fa-hourglass-half {
        opacity: 0.8;
    }
</style>
@endsection
