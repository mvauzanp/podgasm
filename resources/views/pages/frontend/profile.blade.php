@extends('layouts.frontend')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- HEADER TITLE --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Profil &amp; Akun Saya</h4>
                    <p class="text-muted small mb-0">Kelola informasi pribadi, ubah password, dan pantau status pesanan Anda.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-bold">
                    <i class="fas fa-edit me-1.5"></i> Edit Profil
                </a>
            </div>

            {{-- ALERT SUCCESS --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 py-3 px-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-5 text-success"></i>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- MAIN PROFILE CARD --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        {{-- Clean Minimalist Avatar Circle --}}
                        <div class="col-md-3 text-center border-end-md">
                            <div class="position-relative d-inline-block mb-2">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center border shadow-xs" style="width: 86px; height: 86px; background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                    <i class="fas fa-user" style="font-size: 2rem; color: #64748b;"></i>
                                </div>
                                <span class="position-absolute bottom-0 end-0 p-1.5 bg-success border border-white rounded-circle" title="Akun Aktif" style="width: 12px; height: 12px;"></span>
                            </div>
                            <h5 class="mt-2 fw-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                            <span class="badge rounded-pill px-3 py-1 text-capitalize small fw-semibold" style="background-color: #0f172a; color: #ffffff;">
                                {{ Auth::user()->role === 'branch' ? 'Cabang Resmi' : (Auth::user()->role === 'admin' ? 'Administrator' : 'Pelanggan Retail') }}
                            </span>
                        </div>

                        {{-- Details List --}}
                        <div class="col-md-9 ps-md-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-white border rounded-3" style="border-color: #e2e8f0 !important;">
                                        <label class="text-muted small d-block mb-1" style="font-size: 0.78rem;"><i class="far fa-envelope me-1 text-secondary"></i> Alamat Email</label>
                                        <strong class="text-dark">{{ Auth::user()->email }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-white border rounded-3" style="border-color: #e2e8f0 !important;">
                                        <label class="text-muted small d-block mb-1" style="font-size: 0.78rem;"><i class="fas fa-phone-alt me-1 text-secondary"></i> Nomor Telepon</label>
                                        <strong class="text-dark">{{ Auth::user()->phone ?? 'Belum diisi' }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-white border rounded-3" style="border-color: #e2e8f0 !important;">
                                        <label class="text-muted small d-block mb-1" style="font-size: 0.78rem;"><i class="fas fa-map-marker-alt me-1 text-secondary"></i> Alamat Default Akun</label>
                                        <p class="mb-0 text-dark fw-medium small leading-relaxed">{{ Auth::user()->address ?? 'Belum diisi. Tambahkan alamat pengiriman di bawah untuk checkout otomatis.' }}</p>
                                    </div>
                                </div>
                                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                                    <small class="text-muted" style="font-size: 0.82rem;">
                                        <i class="far fa-calendar-alt me-1"></i> Tanggal Bergabung: <strong>{{ Auth::user()->created_at->format('d F Y') }}</strong>
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('profile.edit') }}#securityTab" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                                            <i class="fas fa-key me-1"></i> Ganti Password
                                        </a>
                                        <a href="{{ route('profile.edit') }}" class="btn btn-dark btn-sm rounded-pill px-3.5 fw-semibold">
                                            <i class="fas fa-user-edit me-1"></i> Edit Profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 📍 BUKU ALAMAT PENGIRIMAN --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="fas fa-map-marked-alt text-dark"></i>
                                <span>Buku Alamat Pengiriman</span>
                                <span class="badge rounded-pill px-2.5 py-1 fw-normal" style="background-color: #f1f5f9; color: #475569; font-size: 0.72rem; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-check-circle me-1 text-success"></i>Tersimpan
                                </span>
                            </h5>
                            <p class="text-muted small mb-0">Simpan beberapa alamat pengiriman agar saat checkout langsung terisi &amp; ongkir otomatis terhitung.</p>
                        </div>
                        <button type="button" class="btn btn-dark rounded-pill px-3.5 py-2 btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddAddress">
                            <i class="fas fa-plus me-1.5"></i> Tambah Alamat Baru
                        </button>
                    </div>

                    @if($addresses->isEmpty())
                        {{-- EMPTY STATE --}}
                        <div class="text-center py-5 px-3 bg-light rounded-4 border border-dashed">
                            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 64px; height: 64px; border: 1px solid #e2e8f0;">
                                <i class="fas fa-map-marker-alt fa-2x text-muted"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Alamat Pengiriman Tersimpan</h6>
                            <p class="text-muted small mb-3 mx-auto" style="max-width: 480px;">
                                Tambahkan alamat pengiriman rumah, kantor, atau toko Anda agar pesanan dapat diproses secara instan saat checkout.
                            </p>
                            <button type="button" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddAddress">
                                <i class="fas fa-plus me-1"></i> Tambah Alamat Pertama
                            </button>
                        </div>
                    @else
                        {{-- ADDRESS LIST GRID --}}
                        <div class="row g-3">
                            @foreach($addresses as $addr)
                            <div class="col-md-6">
                                <div class="card address-card-item rounded-4 h-100 position-relative shadow-2xs {{ $addr->is_primary ? 'is-primary' : '' }}" style="padding: 20px 22px !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold badge-theme-label" style="{{ $addr->is_primary ? 'background-color: #0f172a; color: #ffffff;' : 'background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0;' }}">
                                                <i class="fas fa-tag me-1 {{ $addr->is_primary ? 'text-white-50' : 'text-muted' }}"></i>{{ $addr->label }}
                                            </span>
                                            @if($addr->is_primary)
                                                <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 0.68rem;">
                                                    <i class="fas fa-check-circle me-1"></i>Utama
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Actions Dropdown --}}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle shadow-none p-0 d-flex align-items-center justify-content-center border" type="button" data-bs-toggle="dropdown" style="width: 28px; height: 28px;">
                                                <i class="fas fa-ellipsis-v text-muted" style="font-size: 0.75rem;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 p-1.5 small">
                                                @if(!$addr->is_primary)
                                                    <li>
                                                        <form action="{{ route('profile.addresses.setPrimary', $addr->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item rounded-2 py-1.5 text-dark fw-semibold">
                                                                <i class="fas fa-star me-1.5 text-warning"></i> Jadikan Utama
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2 py-1.5 text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditAddress_{{ $addr->id }}">
                                                        <i class="fas fa-edit me-1.5 text-secondary"></i> Edit Alamat
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form action="{{ route('profile.addresses.destroy', $addr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item rounded-2 py-1.5 text-danger fw-semibold">
                                                            <i class="fas fa-trash-alt me-1.5"></i> Hapus Alamat
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    {{-- Recipient & Phone --}}
                                    <div class="fw-bold text-dark mb-1.5" style="font-size: 0.98rem;">
                                        {{ $addr->recipient_name }}
                                        <span class="text-muted fw-normal ms-1" style="font-size: 0.85rem;">({{ $addr->phone }})</span>
                                    </div>

                                    {{-- Full Address --}}
                                    <p class="text-secondary small mb-3" style="line-height: 1.5; font-size: 0.86rem;">
                                        {{ $addr->address }}
                                    </p>

                                    {{-- VERIFIED AREA BADGE --}}
                                    <div class="mt-auto p-2.5 rounded-3 d-flex align-items-center gap-2 verified-area-box">
                                        <i class="fas fa-map-pin text-secondary flex-shrink-0" style="font-size: 0.85rem;"></i>
                                        <div class="min-w-0 flex-grow-1">
                                            <small class="d-block fw-semibold text-truncate area-title" style="font-size: 0.8rem;" title="{{ $addr->biteship_area_name ?? 'Wilayah Pengiriman' }}">
                                                {{ $addr->biteship_area_name ?? 'Wilayah Pengiriman' }}
                                            </small>
                                            @if($addr->postal_code)
                                                <small class="d-block area-postal" style="font-size: 0.72rem;">Kode Pos: {{ $addr->postal_code }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- MODALS SECTION (Placed completely outside cards grid to eliminate flicker) --}}
            {{-- ========================================================================= --}}

            {{-- 1. MODAL TAMBAH ALAMAT BARU --}}
            <div class="modal fade" id="modalAddAddress" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <form action="{{ route('profile.addresses.store') }}" method="POST">
                            @csrf
                            <div class="modal-header border-bottom p-4">
                                <h5 class="modal-title fw-bold mb-0" id="modalAddLabel">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i> Tambah Alamat Pengiriman Baru
                                </h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Label Alamat <span class="text-danger">*</span></label>
                                        <input type="text" name="label" class="form-control rounded-3" placeholder="Contoh: Rumah, Kantor, Toko" value="{{ old('label', 'Rumah') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nama Penerima <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" class="form-control rounded-3" value="{{ old('recipient_name', Auth::user()->name) }}" placeholder="Nama lengkap penerima" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">No. Telepon / WA <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', Auth::user()->phone) }}" placeholder="08xxxxxxxx" required>
                                    </div>

                                    {{-- Autocomplete Input for Add (Search Places / Streets / Districts) --}}
                                    <div class="col-12 position-relative">
                                        <label class="form-label small fw-bold d-flex justify-content-between">
                                            <span><i class="fas fa-search-location text-primary me-1"></i> Cari Jalan, Tempat, atau Kecamatan <span class="text-danger">*</span></span>
                                            <span class="text-muted fw-normal" style="font-size: 0.75rem;">Ketik min. 3 huruf (misal: Kertajaya, Pakuwon, Darmo)</span>
                                        </label>
                                        <input type="text" id="area_search_add" class="form-control rounded-3" placeholder="Ketik nama jalan, tempat, atau kota..." autocomplete="off" required>
                                        <div id="area_suggestions_add" class="list-group position-absolute w-100 shadow rounded-3 d-none" style="z-index: 1060; max-height: 220px; overflow-y: auto;"></div>
                                        
                                        <input type="hidden" name="biteship_area_id" id="biteship_area_id_add">
                                        <input type="hidden" name="biteship_area_name" id="biteship_area_name_add">
                                        <input type="hidden" name="postal_code" id="postal_code_add">

                                        {{-- Area Badge Preview --}}
                                        <div id="selected_area_badge_add" class="alert alert-info py-2 px-3 small rounded-3 mt-2 d-none align-items-center gap-2 mb-0">
                                            <i class="fas fa-check-circle text-primary"></i>
                                            <div>
                                                <strong>Wilayah Terverifikasi:</strong>
                                                <span id="selected_area_text_add" class="d-block"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold mb-0">Alamat Jalan &amp; Nomor Rumah <span class="text-danger">*</span></label>
                                            <span id="auto_fill_badge_add" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size: 0.72rem;">
                                                <i class="fas fa-sync-alt fa-spin me-1"></i> Tersinkron Otomatis dari Peta
                                            </span>
                                        </div>
                                        <textarea name="address" id="address_textarea_add" class="form-control rounded-3" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, patokan..." required>{{ old('address') }}</textarea>
                                        <small class="text-muted" style="font-size: 0.73rem;">Tips: Geser pin peta di bawah untuk mengisi alamat jalan &amp; kecamatan secara otomatis.</small>
                                    </div>

                                    {{-- Pin Point GPS Map (Khusus GoSend / Grab Instant) --}}
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border verified-area-box">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                                <div>
                                                    <label class="form-label small fw-bold mb-0 area-title">
                                                        <i class="fas fa-map-pin text-danger me-1"></i> Pin Point Lokasi Peta (GPS)
                                                    </label>
                                                    <small class="d-block text-muted" style="font-size: 0.73rem;">Geser pin atau klik peta untuk menetapkan titik akurat kurir instant</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold btn-get-gps" data-target="add" style="font-size: 0.75rem;">
                                                    <i class="fas fa-crosshairs me-1 text-primary"></i> Gunakan GPS Saya
                                                </button>
                                            </div>

                                            <div id="map_add" style="height: 190px; border-radius: 8px; z-index: 1;" class="mb-2 border"></div>
                                            
                                            <input type="hidden" name="latitude" id="latitude_add">
                                            <input type="hidden" name="longitude" id="longitude_add">

                                            <div class="d-flex align-items-center justify-content-between small text-muted" style="font-size: 0.75rem;">
                                                <span><i class="fas fa-info-circle me-1"></i>Klik pada peta atau geser pin untuk menetapkan titik.</span>
                                                <span class="badge bg-light text-dark border fw-medium" id="coord_display_add">Belum Diset</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch pt-1">
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_primary" value="1" id="switchPrimaryAdd" {{ $addresses->isEmpty() ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold cursor-pointer" for="switchPrimaryAdd">
                                                Jadikan sebagai Alamat Utama
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer p-3 border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold">
                                    <i class="fas fa-save me-1"></i> Simpan Alamat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. MODAL EDIT ALAMAT (Outside card grid) --}}
            @foreach($addresses as $addr)
            <div class="modal fade" id="modalEditAddress_{{ $addr->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <form action="{{ route('profile.addresses.update', $addr->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header border-bottom p-4">
                                <h5 class="modal-title fw-bold mb-0">
                                    <i class="fas fa-edit me-2 text-primary"></i> Edit Alamat Pengiriman
                                </h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Label Alamat <span class="text-danger">*</span></label>
                                        <input type="text" name="label" class="form-control rounded-3" value="{{ $addr->label }}" placeholder="Contoh: Rumah, Kantor, Toko" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nama Penerima <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" class="form-control rounded-3" value="{{ $addr->recipient_name }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">No. Telepon / WA <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control rounded-3" value="{{ $addr->phone }}" required>
                                    </div>

                                    {{-- Autocomplete Input for Edit (Search Places / Streets / Districts) --}}
                                    <div class="col-12 position-relative">
                                        <label class="form-label small fw-bold d-flex justify-content-between">
                                            <span><i class="fas fa-search-location text-primary me-1"></i> Cari Jalan, Tempat, atau Kecamatan <span class="text-danger">*</span></span>
                                            <span class="text-muted fw-normal" style="font-size: 0.75rem;">Ketik min. 3 huruf</span>
                                        </label>
                                        <input type="text" id="area_search_edit_{{ $addr->id }}" class="form-control rounded-3 area-search-edit" data-target="{{ $addr->id }}" value="{{ $addr->biteship_area_name }}" placeholder="Ketik nama jalan, tempat, atau kota..." autocomplete="off" required>
                                        <div id="area_suggestions_edit_{{ $addr->id }}" class="list-group position-absolute w-100 shadow rounded-3 d-none" style="z-index: 1060; max-height: 220px; overflow-y: auto;"></div>
                                        
                                        <input type="hidden" name="biteship_area_id" id="biteship_area_id_edit_{{ $addr->id }}" value="{{ $addr->biteship_area_id }}">
                                        <input type="hidden" name="biteship_area_name" id="biteship_area_name_edit_{{ $addr->id }}" value="{{ $addr->biteship_area_name }}">
                                        <input type="hidden" name="postal_code" id="postal_code_edit_{{ $addr->id }}" value="{{ $addr->postal_code }}">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold mb-0">Alamat Jalan &amp; Nomor Rumah <span class="text-danger">*</span></label>
                                            <span id="auto_fill_badge_edit_{{ $addr->id }}" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size: 0.72rem;">
                                                <i class="fas fa-sync-alt fa-spin me-1"></i> Tersinkron Otomatis dari Peta
                                            </span>
                                        </div>
                                        <textarea name="address" id="address_textarea_edit_{{ $addr->id }}" class="form-control rounded-3" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, blok, patokan..." required>{{ $addr->address }}</textarea>
                                        <small class="text-muted" style="font-size: 0.73rem;">Tips: Geser pin peta di bawah untuk mengisi alamat jalan &amp; kecamatan secara otomatis.</small>
                                    </div>

                                    {{-- Pin Point GPS Map (Khusus GoSend / Grab Instant) --}}
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border verified-area-box">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                                <div>
                                                    <label class="form-label small fw-bold mb-0 area-title">
                                                        <i class="fas fa-map-pin text-danger me-1"></i> Pin Point Lokasi Peta (GPS)
                                                    </label>
                                                    <small class="d-block text-muted" style="font-size: 0.73rem;">Geser pin atau klik peta untuk menetapkan titik akurat kurir instant</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold btn-get-gps" data-target="{{ $addr->id }}" style="font-size: 0.75rem;">
                                                    <i class="fas fa-crosshairs me-1 text-primary"></i> Gunakan GPS Saya
                                                </button>
                                            </div>

                                            <div id="map_edit_{{ $addr->id }}" style="height: 190px; border-radius: 8px; z-index: 1;" class="mb-2 border"></div>
                                            
                                            <input type="hidden" name="latitude" id="latitude_edit_{{ $addr->id }}" value="{{ $addr->latitude }}">
                                            <input type="hidden" name="longitude" id="longitude_edit_{{ $addr->id }}" value="{{ $addr->longitude }}">

                                            <div class="d-flex align-items-center justify-content-between small text-muted" style="font-size: 0.75rem;">
                                                <span><i class="fas fa-info-circle me-1"></i>Klik pada peta atau geser pin untuk menetapkan titik.</span>
                                                <span class="badge bg-light text-dark border fw-medium" id="coord_display_edit_{{ $addr->id }}">
                                                    {{ $addr->latitude && $addr->longitude ? number_format($addr->latitude, 5) . ', ' . number_format($addr->longitude, 5) : 'Belum Diset' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch pt-1">
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_primary" value="1" id="switchPrimary_{{ $addr->id }}" {{ $addr->is_primary ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold cursor-pointer" for="switchPrimary_{{ $addr->id }}">
                                                Jadikan sebagai Alamat Utama
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer p-3 border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold">
                                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- ORDER STATUS TRACKER DASHBOARD --}}
            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-shipping-fast text-dark"></i>
                    <span>Status Pesanan Saya</span>
                </h6>
                <a href="{{ route('order.history') }}" class="small text-dark fw-semibold text-decoration-none">
                    Lihat Semua Pesanan ({{ $totalOrdersCount }}) <i class="fas fa-arrow-right ms-1 fa-xs"></i>
                </a>
            </div>

            <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                {{-- Menunggu Pembayaran --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border rounded-4 text-center p-3.5 h-100 hover-lift transition-all bg-white shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 14px !important;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 46px; height: 46px; background-color: #fef3c7; color: #d97706;">
                                <i class="fas fa-wallet" style="font-size: 1.15rem;"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-0.5">{{ $pendingPaymentCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Menunggu Bayar</small>
                        </div>
                    </a>
                </div>

                {{-- Pesanan Diproses --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border rounded-4 text-center p-3.5 h-100 hover-lift transition-all bg-white shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 14px !important;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 46px; height: 46px; background-color: #f1f5f9; color: #475569;">
                                <i class="fas fa-box" style="font-size: 1.15rem;"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-0.5">{{ $processingCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Sedang Diproses</small>
                        </div>
                    </a>
                </div>

                {{-- Dalam Pengiriman --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border rounded-4 text-center p-3.5 h-100 hover-lift transition-all bg-white shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 14px !important;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 46px; height: 46px; background-color: #e0f2fe; color: #0284c7;">
                                <i class="fas fa-truck" style="font-size: 1.15rem;"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-0.5">{{ $shippingCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Dalam Pengiriman</small>
                        </div>
                    </a>
                </div>

                {{-- Pesanan Selesai --}}
                <div class="col">
                    <a href="{{ route('order.history') }}" class="text-decoration-none">
                        <div class="card border rounded-4 text-center p-3.5 h-100 hover-lift transition-all bg-white shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 14px !important;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 mx-auto" style="width: 46px; height: 46px; background-color: #dcfce7; color: #16a34a;">
                                <i class="fas fa-check-circle" style="font-size: 1.15rem;"></i>
                            </div>
                            <span class="fw-bold text-dark fs-4 d-block mb-0.5">{{ $completedCount }}</span>
                            <small class="text-muted fw-medium" style="font-size: 0.78rem;">Pesanan Selesai</small>
                        </div>
                    </a>
                </div>
            </div>

            {{-- QUICK NAVIGATION CARDS (Wishlist & Cart) --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border rounded-4 p-3.5 bg-white d-flex flex-row align-items-center justify-content-between shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 24px !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #ffe4e6; color: #e11d48;">
                                <i class="far fa-heart fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.95rem;">Wishlist &amp; Favorit Saya</h6>
                                <small class="text-muted" style="font-size: 0.8rem;">Produk impian yang Anda simpan</small>
                            </div>
                        </div>
                        <a href="{{ route('wishlist.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 fw-semibold" style="font-size: 0.82rem;">
                            Buka Wishlist
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border rounded-4 p-3.5 bg-white d-flex flex-row align-items-center justify-content-between shadow-2xs" style="border-color: #e2e8f0 !important; padding: 20px 24px !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #f1f5f9; color: #0f172a;">
                                <i class="fas fa-shopping-bag fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.95rem;">Keranjang Belanja</h6>
                                <small class="text-muted" style="font-size: 0.8rem;">Item siap checkout ({{ $cartCount }} items)</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3.5 fw-semibold" style="font-size: 0.82rem;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" onclick="loadOffcanvasCart()">
                            Lihat Keranjang
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapInstances = {};
    const markerInstances = {};
    let debounceReverseTimer = null;

    // Helper: Sinkronisasi Reverse Geocoding dari Titik Peta ke Form Input
    function syncReverseGeocode(target, lat, lng) {
        const isAdd = target === 'add';
        const addressTextarea = document.getElementById(isAdd ? 'address_textarea_add' : `address_textarea_edit_${target}`);
        const areaInput = document.getElementById(isAdd ? 'area_search_add' : `area_search_edit_${target}`);
        const areaIdInput = document.getElementById(isAdd ? 'biteship_area_id_add' : `biteship_area_id_edit_${target}`);
        const areaNameInput = document.getElementById(isAdd ? 'biteship_area_name_add' : `biteship_area_name_edit_${target}`);
        const postalInput = document.getElementById(isAdd ? 'postal_code_add' : `postal_code_edit_${target}`);
        const badgeEl = document.getElementById(isAdd ? 'selected_area_badge_add' : null);
        const textEl = document.getElementById(isAdd ? 'selected_area_text_add' : null);
        const autoBadge = document.getElementById(isAdd ? 'auto_fill_badge_add' : `auto_fill_badge_edit_${target}`);
        const coordDisplay = document.getElementById(isAdd ? 'coord_display_add' : `coord_display_edit_${target}`);

        if (coordDisplay) {
            coordDisplay.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi Alamat...`;
            coordDisplay.className = 'badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold';
        }

        clearTimeout(debounceReverseTimer);
        debounceReverseTimer = setTimeout(() => {
            fetch(`{{ route('shipping.reverseGeocode') }}?lat=${lat}&lng=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        // 1. Update Textarea Alamat Jalan
                        if (addressTextarea) {
                            if (!addressTextarea.value.trim() || addressTextarea.dataset.autofilled === 'true') {
                                addressTextarea.value = data.street_address || data.full_display;
                                addressTextarea.dataset.autofilled = 'true';
                            }
                        }

                        // 2. Update Wilayah & Kecamatan (Biteship)
                        if (data.biteship_area_id) {
                            if (areaIdInput) areaIdInput.value = data.biteship_area_id;
                            if (areaNameInput) areaNameInput.value = data.biteship_area_name;
                            if (areaInput) areaInput.value = data.biteship_area_name;
                        } else if (data.biteship_area_name && areaInput) {
                            areaInput.value = data.biteship_area_name;
                            if (areaNameInput) areaNameInput.value = data.biteship_area_name;
                        }

                        // 3. Update Kode Pos
                        if (data.postal_code && postalInput) {
                            postalInput.value = data.postal_code;
                        }

                        // 4. Update Badge Verifikasi
                        if (badgeEl && textEl && data.biteship_area_name) {
                            textEl.textContent = `${data.biteship_area_name} ${data.postal_code ? '(Kode Pos: ' + data.postal_code + ')' : ''}`;
                            badgeEl.classList.remove('d-none');
                            badgeEl.classList.add('d-flex');
                        }

                        // 5. Update Status Feedback
                        if (autoBadge) {
                            autoBadge.classList.remove('d-none');
                            autoBadge.innerHTML = `<i class="fas fa-check-circle me-1"></i> Tersinkron: ${data.street_address || 'Peta'}`;
                            setTimeout(() => {
                                autoBadge.classList.add('d-none');
                            }, 4500);
                        }

                        if (coordDisplay) {
                            coordDisplay.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                            coordDisplay.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
                        }
                    } else {
                        if (coordDisplay) {
                            coordDisplay.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                            coordDisplay.className = 'badge bg-light text-dark border fw-medium';
                        }
                    }
                })
                .catch(err => {
                    console.error('Reverse geocode error:', err);
                    if (coordDisplay) {
                        coordDisplay.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                        coordDisplay.className = 'badge bg-light text-dark border fw-medium';
                    }
                });
        }, 350);
    }

    // Helper: Inisialisasi Peta Leaflet
    function initLeafletMap(containerId, latInputId, lngInputId, displayId, initialLat, initialLng, target) {
        if (!window.L) return;
        const container = document.getElementById(containerId);
        if (!container) return;

        const defaultLat = (initialLat && !isNaN(initialLat)) ? parseFloat(initialLat) : -7.279644;
        const defaultLng = (initialLng && !isNaN(initialLng)) ? parseFloat(initialLng) : 112.759850;

        if (mapInstances[containerId]) {
            setTimeout(() => {
                mapInstances[containerId].invalidateSize();
            }, 200);
            return;
        }

        const map = L.map(containerId).setView([defaultLat, defaultLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker = null;
        if (initialLat && initialLng && !isNaN(initialLat) && !isNaN(initialLng)) {
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        }

        function updateCoords(lat, lng, shouldSync = true) {
            const latInput = document.getElementById(latInputId);
            const lngInput = document.getElementById(lngInputId);
            const display = document.getElementById(displayId);
            if (latInput) latInput.value = parseFloat(lat).toFixed(7);
            if (lngInput) lngInput.value = parseFloat(lng).toFixed(7);
            if (display) {
                display.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                display.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
            }

            if (shouldSync) {
                syncReverseGeocode(target, lat, lng);
            }
        }

        if (marker) {
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                updateCoords(pos.lat, pos.lng, true);
            });
        }

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function(ev) {
                    const pos = ev.target.getLatLng();
                    updateCoords(pos.lat, pos.lng, true);
                });
            }
            updateCoords(lat, lng, true);
        });

        mapInstances[containerId] = map;
        markerInstances[containerId] = marker;

        setTimeout(() => {
            map.invalidateSize();
        }, 250);
    }

    // Bind Modals Show Events
    const modalAddEl = document.getElementById('modalAddAddress');
    if (modalAddEl) {
        modalAddEl.addEventListener('shown.bs.modal', function () {
            initLeafletMap('map_add', 'latitude_add', 'longitude_add', 'coord_display_add', null, null, 'add');
        });
    }

    @foreach($addresses as $addr)
    const modalEditEl_{{ $addr->id }} = document.getElementById('modalEditAddress_{{ $addr->id }}');
    if (modalEditEl_{{ $addr->id }}) {
        modalEditEl_{{ $addr->id }}.addEventListener('shown.bs.modal', function () {
            initLeafletMap('map_edit_{{ $addr->id }}', 'latitude_edit_{{ $addr->id }}', 'longitude_edit_{{ $addr->id }}', 'coord_display_edit_{{ $addr->id }}', '{{ $addr->latitude }}', '{{ $addr->longitude }}', '{{ $addr->id }}');
        });
    }
    @endforeach

    // Helper: Setup Place Search & Forward Geocoding Autocomplete
    function setupPlaceAutocomplete(inputId, suggestionsId, target) {
        const isAdd = target === 'add';
        const input = document.getElementById(inputId);
        const suggestionsBox = document.getElementById(suggestionsId);
        const areaIdInput = document.getElementById(isAdd ? 'biteship_area_id_add' : `biteship_area_id_edit_${target}`);
        const areaNameInput = document.getElementById(isAdd ? 'biteship_area_name_add' : `biteship_area_name_edit_${target}`);
        const postalInput = document.getElementById(isAdd ? 'postal_code_add' : `postal_code_edit_${target}`);
        const addressTextarea = document.getElementById(isAdd ? 'address_textarea_add' : `address_textarea_edit_${target}`);
        const badgeEl = document.getElementById(isAdd ? 'selected_area_badge_add' : null);
        const textEl = document.getElementById(isAdd ? 'selected_area_text_add' : null);
        const containerId = isAdd ? 'map_add' : `map_edit_${target}`;
        const latInputId = isAdd ? 'latitude_add' : `latitude_edit_${target}`;
        const lngInputId = isAdd ? 'longitude_add' : `longitude_edit_${target}`;
        const displayId = isAdd ? 'coord_display_add' : `coord_display_edit_${target}`;

        if (!input || !suggestionsBox) return;

        let searchTimeout = null;

        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = input.value.trim();
            if (query.length < 3) {
                suggestionsBox.classList.add('d-none');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('shipping.searchPlaces') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(places => {
                        suggestionsBox.innerHTML = '';
                        if (places && places.length > 0) {
                            suggestionsBox.classList.remove('d-none');
                            places.forEach(item => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action text-start small py-2.5 border-0 border-bottom';
                                
                                const icon = item.latitude ? 'fa-map-pin text-danger' : 'fa-map-marker-alt text-primary';
                                const mainTitle = item.street_name || item.biteship_area_name || item.display_name;
                                const subTitle = item.display_name !== mainTitle ? item.display_name : (item.district ? `${item.district}, ${item.city}` : '');

                                btn.innerHTML = `
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fas ${icon} mt-1 flex-shrink-0"></i>
                                        <div class="min-w-0 flex-grow-1">
                                            <strong class="d-block text-dark text-truncate">${mainTitle}</strong>
                                            ${subTitle ? `<small class="d-block text-muted text-truncate" style="font-size: 0.73rem;">${subTitle}</small>` : ''}
                                        </div>
                                    </div>
                                `;

                                btn.addEventListener('click', () => {
                                    input.value = item.biteship_area_name || mainTitle;
                                    if (areaIdInput && item.biteship_area_id) areaIdInput.value = item.biteship_area_id;
                                    if (areaNameInput && (item.biteship_area_name || mainTitle)) areaNameInput.value = item.biteship_area_name || mainTitle;
                                    if (postalInput && item.postal_code) postalInput.value = item.postal_code;

                                    if (addressTextarea && item.street_name && (!addressTextarea.value.trim() || addressTextarea.dataset.autofilled === 'true')) {
                                        addressTextarea.value = item.street_name;
                                        addressTextarea.dataset.autofilled = 'true';
                                    }

                                    if (badgeEl && textEl && item.biteship_area_name) {
                                        textEl.textContent = `${item.biteship_area_name} ${item.postal_code ? '(Kode Pos: ' + item.postal_code + ')' : ''}`;
                                        badgeEl.classList.remove('d-none');
                                        badgeEl.classList.add('d-flex');
                                    }

                                    // Pindahkan Peta & Marker ke Lokasi Terpilih (Forward Geocoding)
                                    if (item.latitude && item.longitude) {
                                        const lat = item.latitude;
                                        const lng = item.longitude;

                                        const latInput = document.getElementById(latInputId);
                                        const lngInput = document.getElementById(lngInputId);
                                        const display = document.getElementById(displayId);

                                        if (latInput) latInput.value = parseFloat(lat).toFixed(7);
                                        if (lngInput) lngInput.value = parseFloat(lng).toFixed(7);
                                        if (display) {
                                            display.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                                            display.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
                                        }

                                        if (mapInstances[containerId]) {
                                            mapInstances[containerId].flyTo([lat, lng], 16, { animate: true, duration: 1.2 });
                                            
                                            if (markerInstances[containerId]) {
                                                markerInstances[containerId].setLatLng([lat, lng]);
                                            } else {
                                                const newMarker = L.marker([lat, lng], { draggable: true }).addTo(mapInstances[containerId]);
                                                newMarker.on('dragend', function(ev) {
                                                    const pos = ev.target.getLatLng();
                                                    if (latInput) latInput.value = pos.lat.toFixed(7);
                                                    if (lngInput) lngInput.value = pos.lng.toFixed(7);
                                                    syncReverseGeocode(target, pos.lat, pos.lng);
                                                });
                                                markerInstances[containerId] = newMarker;
                                            }
                                        }
                                    }

                                    suggestionsBox.classList.add('d-none');
                                });

                                suggestionsBox.appendChild(btn);
                            });
                        } else {
                            suggestionsBox.classList.add('d-none');
                        }
                    });
            }, 350);
        });
    }

    // Setup Autocomplete for Add & Edit
    setupPlaceAutocomplete('area_search_add', 'area_suggestions_add', 'add');
    @foreach($addresses as $addr)
    setupPlaceAutocomplete('area_search_edit_{{ $addr->id }}', 'area_suggestions_edit_{{ $addr->id }}', '{{ $addr->id }}');
    @endforeach

    // GPS Geolocation Handler (1-Klik Deteksi & Auto-Fill Alamat)
    document.querySelectorAll('.btn-get-gps').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const isAdd = target === 'add';
            const latInputId = isAdd ? 'latitude_add' : `latitude_edit_${target}`;
            const lngInputId = isAdd ? 'longitude_add' : `longitude_edit_${target}`;
            const displayId = isAdd ? 'coord_display_add' : `coord_display_edit_${target}`;
            const containerId = isAdd ? 'map_add' : `map_edit_${target}`;

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
                return;
            }

            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi...';
            this.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    const latInput = document.getElementById(latInputId);
                    const lngInput = document.getElementById(lngInputId);
                    const display = document.getElementById(displayId);

                    if (latInput) latInput.value = parseFloat(lat).toFixed(7);
                    if (lngInput) lngInput.value = parseFloat(lng).toFixed(7);
                    if (display) {
                        display.textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
                        display.className = 'badge bg-success-subtle text-success border border-success-subtle fw-semibold';
                    }

                    if (mapInstances[containerId]) {
                        mapInstances[containerId].setView([lat, lng], 16);
                        if (markerInstances[containerId]) {
                            markerInstances[containerId].setLatLng([lat, lng]);
                        } else {
                            const marker = L.marker([lat, lng], { draggable: true }).addTo(mapInstances[containerId]);
                            marker.on('dragend', function(e) {
                                const pos = e.target.getLatLng();
                                if (latInput) latInput.value = pos.lat.toFixed(7);
                                if (lngInput) lngInput.value = pos.lng.toFixed(7);
                                syncReverseGeocode(target, pos.lat, pos.lng);
                            });
                            markerInstances[containerId] = marker;
                        }
                    }

                    // Panggil sinkronisasi reverse geocode otomatis
                    syncReverseGeocode(target, lat, lng);

                    this.innerHTML = originalText;
                    this.disabled = false;
                },
                (error) => {
                    alert('Gagal mendeteksi lokasi GPS: ' + error.message);
                    this.innerHTML = originalText;
                    this.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    });

    // Close suggestions on outside click
    document.addEventListener('click', function(e) {
        const searchAdd = document.getElementById('area_search_add');
        const suggestionsAdd = document.getElementById('area_suggestions_add');
        if (suggestionsAdd && searchAdd && !searchAdd.contains(e.target) && !suggestionsAdd.contains(e.target)) {
            suggestionsAdd.classList.add('d-none');
        }
        document.querySelectorAll('.area-search-edit').forEach(input => {
            const targetId = input.dataset.target;
            const suggestionsBox = document.getElementById(`area_suggestions_edit_${targetId}`);
            if (suggestionsBox && !input.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });
    });
});
</script>
<style>
.hover-lift {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
}
.shadow-2xs {
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
</style>
@endsection
