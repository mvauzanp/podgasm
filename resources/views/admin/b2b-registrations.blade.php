@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">List User B2B</h3>
            <p class="text-muted mb-0">
                Kelola dan tinjau seluruh akun mitra cabang (B2B/Reseller).
            </p>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- TABLE HEADER --}}
        <div class="card-header bg-white border-0 py-4 px-4">
            <h5 class="fw-semibold mb-1">Daftar Mitra B2B</h5>
            <small class="text-muted">Status persetujuan dan dokumen verifikasi mitra B2B</small>
        </div>

        {{-- TABLE BODY --}}
        <div class="card-body p-0">
            @if($registrations->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted fw-semibold">Belum ada user B2B terdaftar saat ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Tanggal Daftar</th>
                                <th>Nama Pemilik</th>
                                <th>Nama Toko</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registrations as $registration)
                                <tr class="border-top">
                                    <td class="ps-4 py-4">
                                        <div class="small fw-semibold text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $registration->created_at->format('d M Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $registration->owner_name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                                            <i class="fas fa-store me-1"></i>
                                            {{ $registration->store_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $registration->email }}" class="text-decoration-none text-primary">
                                            {{ $registration->email }}
                                        </a>
                                    </td>
                                    <td>{{ $registration->phone }}</td>
                                    <td>
                                        @if($registration->status == 'pending')
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-clock me-1"></i> Pending
                                            </span>
                                        @elseif($registration->status == 'approved')
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> Approved
                                            </span>
                                        @elseif($registration->status == 'rejected')
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold" title="Alasan: {{ $registration->rejection_reason }}">
                                                <i class="fas fa-times-circle me-1"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <button class="btn btn-sm btn-light border rounded-3 px-3 shadow-sm" data-bs-toggle="modal" 
                                            data-bs-target="#detailsModal{{ $registration->id }}">
                                            <i class="fas fa-eye me-1 text-primary"></i> Detail Profile
                                        </button>
                                    </td>
                                </tr>

                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal{{ $registration->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header border-bottom-0 px-4 pt-4">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-building me-2 text-primary"></i>Profile Mitra B2B</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body px-4">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold">Nama Pemilik</label>
                                                        <div class="p-3 bg-light rounded-3 fw-semibold">{{ $registration->owner_name }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold">Nama Toko</label>
                                                        <div class="p-3 bg-light rounded-3 fw-semibold text-primary"><i class="fas fa-store me-1"></i>{{ $registration->store_name }}</div>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold">Email</label>
                                                        <div class="p-3 bg-light rounded-3 fw-semibold">{{ $registration->email }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold">Telepon</label>
                                                        <div class="p-3 bg-light rounded-3 fw-semibold">{{ $registration->phone }}</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Alamat Usaha</label>
                                                    <div class="p-3 bg-light rounded-3 text-wrap">{{ $registration->address }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Status Persetujuan</label>
                                                    <div>
                                                        @if($registration->status == 'pending')
                                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">Menunggu Persetujuan</span>
                                                        @elseif($registration->status == 'approved')
                                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold">Disetujui / Aktif</span>
                                                        @elseif($registration->status == 'rejected')
                                                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold">Ditolak</span>
                                                            @if($registration->rejection_reason)
                                                                <div class="mt-2 p-2 bg-danger bg-opacity-10 text-danger rounded-3 small fw-medium">
                                                                    <strong>Alasan Penolakan:</strong> {{ $registration->rejection_reason }}
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold d-block">Foto KTP</label>
                                                        @if($registration->ktp_file)
                                                            <div class="d-flex flex-column align-items-start gap-2">
                                                                <a href="{{ Storage::url($registration->ktp_file) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-2">
                                                                    <i class="fas fa-image me-1"></i> Buka Foto KTP
                                                                </a>
                                                                <img src="{{ Storage::url($registration->ktp_file) }}" class="img-thumbnail rounded-3 mt-2" style="max-height: 150px; width: 100%; object-fit: cover;">
                                                            </div>
                                                        @else
                                                            <span class="text-danger">KTP belum diunggah</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted small fw-bold d-block">Foto Depan Toko</label>
                                                        @if($registration->storefront_photo)
                                                            <div class="d-flex flex-column align-items-start gap-2">
                                                                <a href="{{ Storage::url($registration->storefront_photo) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-2">
                                                                    <i class="fas fa-store me-1"></i> Buka Foto Toko
                                                                </a>
                                                                <img src="{{ Storage::url($registration->storefront_photo) }}" class="img-thumbnail rounded-3 mt-2" style="max-height: 150px; width: 100%; object-fit: cover;">
                                                            </div>
                                                        @else
                                                            <span class="text-danger">Foto Toko belum diunggah</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 px-4 pb-4 mt-3">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                                
                                                @if($registration->status == 'pending' || $registration->status == 'approved')
                                                    <form method="POST" action="{{ route('admin.b2b.reject', $registration) }}" class="d-inline" id="rejectForm{{ $registration->id }}">
                                                        @csrf
                                                        <input type="hidden" name="rejection_reason" id="rejectReason{{ $registration->id }}">
                                                        <button type="button" class="btn btn-danger rounded-pill px-4" onclick="promptReject('{{ $registration->id }}')">
                                                            <i class="fas fa-times me-1"></i> Tolak Akun
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($registration->status == 'pending' || $registration->status == 'rejected')
                                                    <form method="POST" action="{{ route('admin.b2b.approve', $registration) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                                                            <i class="fas fa-check me-1"></i> Setujui Akun
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- PAGINATION --}}
        @if(!$registrations->isEmpty())
            <div class="card-footer bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-end">
                    {{ $registrations->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function promptReject(registrationId) {
        const reason = prompt('Masukkan alasan penolakan pendaftaran B2B ini:');
        if (reason !== null) {
            if (reason.trim() === '') {
                alert('Alasan penolakan tidak boleh kosong!');
            } else {
                document.getElementById('rejectReason' + registrationId).value = reason;
                document.getElementById('rejectForm' + registrationId).submit();
            }
        }
    }
</script>
@endsection
