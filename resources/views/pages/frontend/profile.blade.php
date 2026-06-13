@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-4">Profil Pengguna</h4>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-5">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x text-primary"></i>
                            </div>
                            <h5 class="mt-3 fw-bold">{{ Auth::user()->name }}</h5>
                            <span class="badge bg-primary text-capitalize">{{ Auth::user()->role }}</span>
                        </div>
                        <div class="col-md-9">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Email</label>
                                    <p class="mb-0">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small d-block mb-1">Nomor Telepon</label>
                                    <p class="mb-0">{{ Auth::user()->phone ?? 'Belum diisi' }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="text-muted small d-block mb-1">Alamat</label>
                                    <p class="mb-0">{{ Auth::user()->address ?? 'Belum diisi' }}</p>
                                </div>
                            </div>

                            @if($b2bRegistration)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="text-muted small d-block mb-1">Status Kemitraan B2B</label>
                                    <div>
                                        @if($b2bRegistration->status == 'pending')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan
                                            </span>
                                            <small class="text-muted d-block mt-1">Pendaftaran sedang ditinjau (estimasi 1-3 hari kerja).</small>
                                        @elseif($b2bRegistration->status == 'approved')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> Disetujui
                                            </span>
                                            <small class="text-muted d-block mt-1">Kemitraan aktif. Anda dapat berbelanja dengan harga grosir.</small>
                                        @elseif($b2bRegistration->status == 'rejected')
                                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-times-circle me-1"></i> Ditolak
                                            </span>
                                            @if($b2bRegistration->rejection_reason)
                                                <div class="mt-2 p-2 bg-danger bg-opacity-10 text-danger rounded-3 small fw-medium">
                                                    <strong>Alasan Penolakan:</strong> {{ $b2bRegistration->rejection_reason }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <label class="text-muted small d-block mb-1">Tanggal Bergabung</label>
                                    <p class="mb-0">{{ Auth::user()->created_at->format('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-edit me-2"></i> Edit Profil
                            </a>
                            <a href="{{ route('order.history') }}" class="btn btn-outline-primary rounded-pill px-4">
                                <i class="fas fa-history me-2"></i> Riwayat Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info Cards -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-shopping-bag text-primary me-2"></i> Total Pesanan
                            </h6>
                            <p class="text-muted mb-0 fs-5">
                                {{ Auth::user()->orders()->count() }} pesanan
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i> Pesanan Selesai
                            </h6>
                            <p class="text-muted mb-0 fs-5">
                                {{ Auth::user()->orders()->whereIn('status', ['delivered', 'completed'])->count() }} pesanan
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
