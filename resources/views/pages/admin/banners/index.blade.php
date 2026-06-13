@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="fw-bold mb-1">Manajemen Banner Promo</h3>
            <p class="text-muted mb-0">Kelola banner promo yang akan ditampilkan pada slider halaman beranda.</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary rounded-4 px-4 mt-3 mt-md-0">
            <i class="fas fa-plus me-2"></i> Tambah Banner
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 80px;">Urutan</th>
                            <th style="width: 200px;">Gambar Banner</th>
                            <th>Judul / Deskripsi</th>
                            <th>Link URL Redireksi</th>
                            <th style="width: 120px;">Status</th>
                            <th class="text-center pe-4" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border fw-bold px-3 py-2 rounded-3">
                                        {{ $banner->order }}
                                    </span>
                                </td>
                                <td>
                                    <img src="{{ $banner->gambar_url }}" class="rounded-3 border shadow-sm" style="width: 150px; height: 80px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $banner->judul ?? '-' }}</div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">{{ $banner->deskripsi ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($banner->link_url)
                                        <a href="{{ $banner->link_url }}" target="_blank" class="text-decoration-none text-primary fw-medium small">
                                            <i class="fas fa-link me-1"></i> {{ $banner->link_url }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Tidak ada link</span>
                                    @endif
                                </td>
                                <td>
                                    @if($banner->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Aktif</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-outline-warning btn-sm rounded-circle p-2 border-0" title="Edit">
                                            <i class="fas fa-pen-to-square fs-6"></i>
                                        </a>
                                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Hapus">
                                                <i class="fas fa-trash-can fs-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-images fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Belum ada banner promo ditambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
