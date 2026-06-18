@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content_admin')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Manajemen Kategori</h1>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Kelola kategori dan sub-kategori produk vape</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i> Tambah Kategori
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4 py-3" style="width: 60px;">#</th>
                            <th class="py-3">Kategori</th>
                            <th class="py-3">Slug</th>
                            <th class="py-3 text-center" style="width: 130px;">Sub-Kategori</th>
                            <th class="py-3 text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        {{-- Parent Category Row --}}
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $category->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; font-size: 0.85rem;">
                                        <i class="fas fa-folder"></i>
                                    </span>
                                    <span class="fw-bold">{{ $category->nama_kategori }}</span>
                                </div>
                            </td>
                            <td><code class="bg-light text-dark rounded px-2 py-1" style="font-size: 0.82rem;">{{ $category->slug }}</code></td>
                            <td class="text-center">
                                @if($category->children->count() > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">{{ $category->children->count() }} item</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.82rem;">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" title="Edit">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Yakin ingin menghapus kategori ini beserta sub-kategorinya?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
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
