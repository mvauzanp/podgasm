@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="fw-bold mb-1">Edit Banner Promo</h3>
            <p class="text-muted mb-0">Ubah detail slide banner promo halaman beranda.</p>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-light border rounded-4 px-4 mt-3 mt-md-0">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3">
                            <i class="fas fa-pen-to-square text-warning fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Form Edit Banner</h5>
                            <small class="text-muted">Perbarui data atau ganti gambar banner promo.</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- JUDUL --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">Judul Banner (Opsional)</label>
                                <input type="text" name="judul" class="form-control rounded-3 py-2 bg-light border-0" placeholder="Contoh: Oxva Xlim Go 2" value="{{ old('judul', $banner->judul) }}">
                                <small class="text-muted">Judul akan ditampilkan sebagai teks overlay di atas gambar.</small>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">Deskripsi Banner (Opsional)</label>
                                <textarea name="deskripsi" rows="3" class="form-control rounded-3 py-2 bg-light border-0" placeholder="Contoh: Dapatkan diskon 50% khusus hari ini!">{{ old('deskripsi', $banner->deskripsi) }}</textarea>
                                <small class="text-muted">Deskripsi pendek promosi.</small>
                            </div>

                            {{-- LINK REDIREKSI --}}
                            <div class="col-md-8 mb-4">
                                <label class="form-label fw-semibold">Produk Tujuan Link Banner (Opsional)</label>
                                @php
                                    // Deteksi product yang sedang dipilih dari link_url yang tersimpan
                                    $selectedProductId = old('product_id');
                                    if (!$selectedProductId && $banner->link_url) {
                                        $slug = basename(rtrim($banner->link_url, '/'));
                                        $matched = $products->firstWhere('slug', $slug);
                                        $selectedProductId = $matched?->id;
                                    }
                                @endphp
                                <select name="product_id" id="product_id" class="form-select rounded-3 py-2 bg-light border-0">
                                    <option value="">— Tidak ada link produk —</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ $selectedProductId == $product->id ? 'selected' : '' }}>
                                            {{ $product->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika dipilih, klik banner akan langsung menuju halaman produk tersebut.</small>
                            </div>


                            {{-- URUTAN TAMPIL --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Urutan Tampil (Order)</label>
                                <input type="number" name="order" class="form-control rounded-3 py-2 bg-light border-0" placeholder="0" value="{{ old('order', $banner->order) }}" required min="0">
                                <small class="text-muted">Nilai terkecil tampil pertama.</small>
                            </div>

                            {{-- STATUS AKTIF --}}
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="ps-2">
                                        <label class="form-check-label fw-bold text-dark mb-1" for="is_active">Aktifkan Banner</label>
                                        <p class="text-muted small mb-0">Jika aktif, banner akan langsung tampil di beranda utama.</p>
                                    </div>
                                    <input class="form-check-input fs-4 me-2" type="checkbox" id="is_active" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}>
                                </div>
                            </div>

                            {{-- UPLOAD GAMBAR --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">Ganti File Gambar Banner</label>
                                <div class="upload-box position-relative p-4 rounded-4 border-2 border-dashed bg-light text-center" style="border-style: dashed !important; border-width: 2px !important; border-color: #ced4da !important;">
                                    <input type="file" name="gambar" class="form-control position-absolute top-0 start-0 opacity-0 w-100 h-100" accept="image/*" onchange="previewImage(event)" style="cursor:pointer;">
                                    <div>
                                        <i class="fas fa-cloud-upload-alt fs-1 text-primary mb-3"></i>
                                        <h6 class="fw-semibold">Upload Gambar Baru</h6>
                                        <p class="text-muted small mb-0">Kosongkan jika tidak ingin mengganti gambar banner saat ini.</p>
                                    </div>
                                </div>
                                
                                {{-- IMAGE PREVIEW --}}
                                <div class="mt-4 text-center">
                                    @if($banner->gambar)
                                        <img id="preview" src="{{ $banner->gambar_url }}" class="rounded-4 shadow-sm border img-fluid" style="max-height: 250px; object-fit: cover;">
                                    @else
                                        <img id="preview" class="rounded-4 shadow-sm border img-fluid" style="max-height: 250px; display: none; object-fit: cover;">
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER BUTTON --}}
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-light border rounded-4 px-4 py-2">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-success rounded-4 px-4 py-2 shadow-sm text-white">
                                <i class="fas fa-floppy-disk me-2"></i> Update Banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const img = document.getElementById('preview');
        img.src = reader.result;
        img.style.display = 'inline-block';
    }
    if(event.target.files.length > 0) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>
@endsection
