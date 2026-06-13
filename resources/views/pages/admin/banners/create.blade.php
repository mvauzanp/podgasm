@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="fw-bold mb-1">Tambah Banner Promo Baru</h3>
            <p class="text-muted mb-0">Buat slide banner promo baru untuk halaman beranda.</p>
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
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                            <i class="fas fa-image text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Form Tambah Banner</h5>
                            <small class="text-muted">Isi detail promo dan unggah aset gambar banner.</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            {{-- JUDUL --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">Judul Banner (Opsional)</label>
                                <input type="text" name="judul" class="form-control rounded-3 py-2 bg-light border-0" placeholder="Contoh: Oxva Xlim Go 2" value="{{ old('judul') }}">
                                <small class="text-muted">Judul akan ditampilkan sebagai teks overlay di atas gambar.</small>
                            </div>

                            {{-- DESKRIPSI --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">Deskripsi Banner (Opsional)</label>
                                <textarea name="deskripsi" rows="3" class="form-control rounded-3 py-2 bg-light border-0" placeholder="Contoh: Dapatkan diskon 50% khusus hari ini!">{{ old('deskripsi') }}</textarea>
                                <small class="text-muted">Deskripsi pendek promosi.</small>
                            </div>

                            {{-- LINK REDIREKSI --}}
                            <div class="col-md-8 mb-4">
                                <label class="form-label fw-semibold">Produk Tujuan Link Banner (Opsional)</label>
                                <select name="product_id" id="product_id" class="form-select rounded-3 py-2 bg-light border-0">
                                    <option value="">— Tidak ada link produk —</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika dipilih, klik banner akan langsung menuju halaman produk tersebut.</small>
                            </div>


                            {{-- URUTAN TAMPIL --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Urutan Tampil (Order)</label>
                                <input type="number" name="order" class="form-control rounded-3 py-2 bg-light border-0" placeholder="0" value="{{ old('order', 0) }}" required min="0">
                                <small class="text-muted">Nilai terkecil tampil pertama.</small>
                            </div>

                            {{-- STATUS AKTIF --}}
                            <div class="col-12 mb-4">
                                <div class="form-check form-switch p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                    <div class="ps-2">
                                        <label class="form-check-label fw-bold text-dark mb-1" for="is_active">Aktifkan Banner</label>
                                        <p class="text-muted small mb-0">Jika aktif, banner akan langsung tampil di beranda utama.</p>
                                    </div>
                                    <input class="form-check-input fs-4 me-2" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                </div>
                            </div>

                            {{-- UPLOAD GAMBAR --}}
                            <div class="col-12 mb-4">
                                <label class="form-label fw-semibold">File Gambar Banner <span class="text-danger">*</span></label>
                                <div class="upload-box position-relative p-4 rounded-4 border-2 border-dashed bg-light text-center" style="border-style: dashed !important; border-width: 2px !important; border-color: #ced4da !important;">
                                    <input type="file" name="gambar" class="form-control position-absolute top-0 start-0 opacity-0 w-100 h-100" accept="image/*" onchange="previewImage(event)" style="cursor:pointer;" required>
                                    <div>
                                        <i class="fas fa-cloud-upload-alt fs-1 text-primary mb-3"></i>
                                        <h6 class="fw-semibold">Upload Gambar Banner</h6>
                                        <p class="text-muted small mb-0">Ukuran direkomendasikan: 1200 x 500 px (Rasio 2.4:1)</p>
                                    </div>
                                </div>
                                
                                {{-- IMAGE PREVIEW --}}
                                <div class="mt-4 text-center">
                                    <img id="preview" class="rounded-4 shadow-sm border img-fluid" style="max-height: 250px; display: none; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER BUTTON --}}
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-light border rounded-4 px-4 py-2">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary rounded-4 px-4 py-2 shadow-sm">
                                <i class="fas fa-floppy-disk me-2"></i> Simpan Banner
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
