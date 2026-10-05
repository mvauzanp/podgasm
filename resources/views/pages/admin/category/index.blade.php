@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">Manajemen Kategori</h4>
            <p class="text-muted small mb-0">Kelola kategori dan sub-kategori produk vape</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                <i class="fas fa-check-circle me-1"></i> Master Kategori Sinkron Olsera
            </span>
            <a href="{{ route('admin.olsera.index') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-rotate me-1.5"></i> Panel Olsera
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2.5" style="width: 50px;">#</th>
                            <th class="py-2.5">Kategori</th>
                            <th class="py-2.5">Slug</th>
                            <th class="py-2.5 text-center" style="width: 120px;">Sub-Kategori</th>
                            <th class="py-2.5 text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        {{-- Parent Category Row --}}
                        <tr class="border-top">
                            <td class="ps-3 text-muted fw-semibold">{{ $category->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width: 28px; height: 28px; background: linear-gradient(135deg, #0284c7, #38bdf8); color: white; font-size: 0.75rem;">
                                        <i class="fas fa-folder"></i>
                                    </span>
                                    <span class="fw-semibold text-dark">{{ $category->nama_kategori }}</span>
                                </div>
                            </td>
                            <td><code class="bg-light text-dark rounded px-2 py-0.5" style="font-size: 0.75rem;">{{ $category->slug }}</code></td>
                            <td class="text-center">
                                @if($category->children->count() > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">{{ $category->children->count() }} item</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" title="Edit Detail">
                                    <i class="fas fa-pen-to-square me-1"></i> Edit
                                </a>
                            </td>
                        </tr>

                        {{-- Sub-Category Rows --}}
                        @foreach($category->children as $child)
                        <tr style="background-color: #fafbfc;">
                            <td class="ps-4 text-muted" style="font-size: 0.85rem;">{{ $child->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2 ps-4">
                                    <span class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-turn-up fa-rotate-90"></i></span>
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width: 30px; height: 30px; background: #e9ecef; color: #6b7280; font-size: 0.75rem;">
                                        <i class="fas fa-folder-open"></i>
                                    </span>
                                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $child->nama_kategori }}</span>
                                </div>
                            </td>
                            <td><code class="bg-light text-dark rounded px-2 py-1" style="font-size: 0.82rem;">{{ $child->slug }}</code></td>
                            <td class="text-center">
                                <span class="text-muted" style="font-size: 0.82rem;">—</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $child->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" title="Edit">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $child->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus sub-kategori ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach

                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Tidak ada data kategori.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
