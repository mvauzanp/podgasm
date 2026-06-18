@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content_admin')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Tambah Kategori</h1>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Buat kategori utama atau sub-kategori baru</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control rounded-3 @error('nama_kategori') is-invalid @enderror" value="{{ old('nama_kategori') }}" placeholder="Contoh: Close System, Disposable, dll.">
                        @error('nama_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori Induk <span class="text-muted fw-normal">(Opsional)</span></label>
                        <select name="parent_id" class="form-select rounded-3 @error('parent_id') is-invalid @enderror">
                            <option value="">— Tidak ada (Kategori Utama) —</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted mt-1 d-block">Kosongkan jika ini adalah kategori utama. Pilih induk jika ini sub-kategori.</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save me-2"></i> Simpan
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
