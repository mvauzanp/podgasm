@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-users-gear text-primary me-2"></i>Manajemen Mitra B2B & Cabang</h3>
            <p class="text-muted mb-0">
                Kelola dan tinjau seluruh akun mitra cabang (B2B/Reseller) dan Cabang Resmi (Branch).
            </p>
        </div>
        @if($activeTab === 'branch')
            <div class="mt-3 mt-md-0">
                <button class="btn btn-primary rounded-pill px-4 py-2-5 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                    <i class="fas fa-plus-circle me-1"></i> Tambah Cabang Baru
                </button>
            </div>
        @endif
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><strong>Terjadi Kesalahan:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TAB NAVIGATION --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
            <ul class="nav nav-pills" id="b2bTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link @if($activeTab === 'reseller') active @endif fw-bold rounded-pill px-4" href="{{ route('admin.b2b.list', ['tab' => 'reseller']) }}">
                        <i class="fas fa-store me-2"></i>Reseller & Distribusi
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link @if($activeTab === 'branch') active @endif fw-bold rounded-pill px-4" href="{{ route('admin.b2b.list', ['tab' => 'branch']) }}">
                        <i class="fas fa-code-branch me-2"></i>Cabang Resmi (Branch)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- TAB CONTENTS --}}
    <div class="tab-content" id="b2bTabContent">
        {{-- TAB 1: RESELLER & DISTRIBUSI --}}
        @if($activeTab === 'reseller')
            <div class="tab-pane fade show active" id="reseller-pane" role="tabpanel">
                {{-- SECTION 1: MENUNGGU PERSETUJUAN --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-semibold mb-1 text-primary"><i class="fas fa-clock me-2"></i>Permohonan Pendaftaran B2B</h5>
                            <small class="text-muted">Status persetujuan dan dokumen verifikasi pendaftar baru</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if($registrations->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted fw-semibold">Tidak ada permohonan pendaftaran saat ini.</p>
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

                    {{-- PAGINATION REGISTRATIONS --}}
                    @if(!$registrations->isEmpty())
                        <div class="card-footer bg-white border-0 py-3 px-4">
                            <div class="d-flex justify-content-end">
                                {{ $registrations->appends(['tab' => 'reseller'])->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- SECTION 2: DAFTAR RESELLER AKTIF --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-4 px-4">
                        <h5 class="fw-semibold mb-1 text-success"><i class="fas fa-store-slash me-2"></i>Daftar Reseller & Distribusi Aktif</h5>
                        <small class="text-muted">Semua mitra reseller/distribusi yang terdaftar dan aktif di sistem</small>
                    </div>

                    <div class="card-body p-0">
                        @if($activeResellers->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-store-slash fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted fw-semibold">Belum ada reseller terdaftar.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3">Nama Kontak</th>
                                            <th>Nama Toko / Bisnis</th>
                                            <th>Email</th>
                                            <th>Telepon</th>
                                            <th>Alamat</th>
                                            <th>Tanggal Bergabung</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activeResellers as $reseller)
                                            <tr class="border-top">
                                                <td class="ps-4 py-4 fw-bold text-dark">
                                                    {{ $reseller->name }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fas fa-store me-1"></i>
                                                        {{ $reseller->b2bRegistration->store_name ?? 'Mitra Reseller' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="mailto:{{ $reseller->email }}" class="text-decoration-none text-primary">
                                                        {{ $reseller->email }}
                                                    </a>
                                                </td>
                                                <td>{{ $reseller->phone }}</td>
                                                <td>
                                                    <div class="text-wrap" style="max-width: 250px;">{{ $reseller->address }}</div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-muted">
                                                        {{ $reseller->created_at->format('d M Y') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- PAGINATION ACTIVE RESELLERS --}}
                    @if(!$activeResellers->isEmpty())
                        <div class="card-footer bg-white border-0 py-3 px-4">
                            <div class="d-flex justify-content-end">
                                {{ $activeResellers->appends(['tab' => 'reseller'])->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- TAB 2: CABANG / BRANCH --}}
        @if($activeTab === 'branch')
            <div class="tab-pane fade show active" id="branch-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-semibold mb-1 text-primary"><i class="fas fa-code-branch me-2"></i>Daftar Cabang Resmi (Branch)</h5>
                            <small class="text-muted">Akun cabang resmi milik Podgasm yang didaftarkan langsung oleh Admin pusat</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if($branchAccounts->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-code-branch fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted fw-semibold">Belum ada akun cabang yang dibuat oleh Admin.</p>
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                                    <i class="fas fa-plus-circle me-1"></i> Buat Akun Cabang Pertama
                                </button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3">Nama Cabang</th>
                                            <th>Penanggung Jawab</th>
                                            <th>Email Login</th>
                                            <th>Telepon</th>
                                            <th>Alamat Cabang</th>
                                            <th>Tanggal Dibuat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($branchAccounts as $branch)
                                            <tr class="border-top">
                                                <td class="ps-4 py-4">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="fas fa-building me-1"></i>
                                                        {{ $branch->b2bRegistration->store_name ?? 'Cabang Resmi' }}
                                                    </span>
                                                </td>
                                                <td class="fw-bold text-dark">
                                                    {{ $branch->name }}
                                                </td>
                                                <td>
                                                    <a href="mailto:{{ $branch->email }}" class="text-decoration-none text-primary">
                                                        {{ $branch->email }}
                                                    </a>
                                                </td>
                                                <td>{{ $branch->phone }}</td>
                                                <td>
                                                    <div class="text-wrap" style="max-width: 250px;">{{ $branch->address }}</div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-muted">
                                                        {{ $branch->created_at->format('d M Y') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- PAGINATION BRANCH ACCOUNTS --}}
                    @if(!$branchAccounts->isEmpty())
                        <div class="card-footer bg-white border-0 py-3 px-4">
                            <div class="d-flex justify-content-end">
                                {{ $branchAccounts->appends(['tab' => 'branch'])->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ADD BRANCH MODAL --}}
            <div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-bottom-0 px-4 pt-4">
                            <h5 class="modal-title fw-bold" id="addBranchModalLabel">
                                <i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Akun Cabang Baru
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('admin.b2b.storeBranch') }}" method="POST">
                            @csrf
                            <div class="modal-body px-4">
                                <div class="mb-3">
                                    <label for="branch_name" class="form-label fw-bold small mb-1">Nama Cabang / Toko <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="branch_name" class="form-control rounded-3" placeholder="Contoh: Podgasm Bandung" required value="{{ old('name') }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="branch_owner" class="form-label fw-bold small mb-1">Nama Penanggung Jawab <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" id="branch_owner" class="form-control rounded-3" placeholder="Nama Lengkap Penanggung Jawab" required value="{{ old('owner_name') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="branch_email" class="form-label fw-bold small mb-1">Email Login Cabang <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="branch_email" class="form-control rounded-3" placeholder="email@cabang.com" required value="{{ old('email') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="branch_phone" class="form-label fw-bold small mb-1">Nomor Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="branch_phone" class="form-control rounded-3" placeholder="Contoh: 08123456789" required value="{{ old('phone') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="branch_address" class="form-label fw-bold small mb-1">Alamat Lengkap Cabang <span class="text-danger">*</span></label>
                                    <textarea name="address" id="branch_address" rows="3" class="form-control rounded-3" placeholder="Alamat lengkap lokasi cabang" required>{{ old('address') }}</textarea>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="branch_password" class="form-label fw-bold small mb-1">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" id="branch_password" class="form-control rounded-3" placeholder="Min. 8 karakter" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="branch_password_confirmation" class="form-label fw-bold small mb-1">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" id="branch_password_confirmation" class="form-control rounded-3" placeholder="Ulangi password" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i>Simpan Akun Cabang
                                </button>
                            </div>
                        </form>
                    </div>
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
