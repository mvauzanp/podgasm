@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    {{-- PAGE HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">Manajemen Banner Promo</h4>
            <p class="text-muted small mb-0">Kelola banner promo yang akan ditampilkan pada slider halaman beranda.</p>
        </div>
        <div>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-plus me-1.5"></i> Tambah Banner
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2.5" style="width: 70px;">Urutan</th>
                            <th style="width: 120px;">Gambar Banner</th>
                            <th>Judul / Deskripsi</th>
                            <th>Link URL Redireksi</th>
                            <th style="width: 100px;">Status</th>
                            <th class="text-center pe-3" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            <tr class="border-top">
                                <td class="ps-3 py-2.5">
                                    <span class="badge bg-light text-dark border fw-bold rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        #{{ $banner->order }}
                                    </span>
                                </td>
                                <td>
                                    <img src="{{ $banner->gambar_url }}" class="rounded-2 border shadow-sm" style="width: 90px; height: 46px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size: 0.84rem;">{{ $banner->judul ?? '-' }}</div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 220px; font-size: 0.72rem;">{{ $banner->deskripsi ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($banner->link_url)
                                        <a href="{{ $banner->link_url }}" target="_blank" class="text-decoration-none text-primary small" style="font-size: 0.78rem;">
                                            <i class="fas fa-link me-1"></i> {{ Str::limit($banner->link_url, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted small" style="font-size: 0.75rem;">Tidak ada link</span>
                                    @endif
                                </td>
                                <td>
                                    @if($banner->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Aktif</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" title="Edit">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" title="Hapus">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">
                                    <i class="fas fa-images fa-2x mb-2 opacity-25"></i>
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
