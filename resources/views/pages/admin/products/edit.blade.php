@extends('layouts.admin')

@section('content_admin')

<div class="container-fluid">
<style>
.preview-card {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,0.075);
    border: 1px solid #dee2e6;
    transition: all 0.2s ease-in-out;
}
.preview-card:hover {
    transform: scale(1.02);
}
.preview-card .delete-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(33, 37, 41, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    cursor: pointer;
}
.preview-card:hover .delete-overlay {
    opacity: 1;
}
</style>

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                Edit Produk
            </h3>

            <p class="text-muted mb-0">
                Perbarui informasi produk pada sistem inventaris gudang.
            </p>

        </div>

        <a href="{{ route('admin.products.index') }}"
           class="btn btn-light border rounded-4 px-4 mt-3 mt-md-0">

            <i class="fas fa-arrow-left me-2"></i>
            Kembali

        </a>

    </div>

    <div class="row justify-content-center">

        <div class="col-lg-10 col-xl-9">

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                {{-- CARD HEADER --}}
                <div class="card-header bg-white border-0 py-4 px-4">

                    <div class="d-flex align-items-center">

                        <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3">

                            <i class="fas fa-pen-to-square text-warning fs-4"></i>

                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">

                                Edit Produk
                                <span class="text-primary">
                                    {{ $product->nama_barang }}
                                </span>

                            </h5>

                            <small class="text-muted">
                                Pastikan data produk sudah sesuai sebelum disimpan.
                            </small>

                        </div>

                    </div>

                </div>

                {{-- CARD BODY --}}
                <div class="card-body p-4">

                    {{-- ERROR ALERT --}}
                    @if ($errors->any())

                        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">

                            <div class="d-flex">

                                <div class="me-3">
                                    <i class="fas fa-circle-exclamation fs-3"></i>
                                </div>

                                <div>

                                    <h6 class="fw-bold mb-2">
                                        Gagal memperbarui data
                                    </h6>

                                    <ul class="mb-0 small">

                                        @foreach ($errors->all() as $error)

                                            <li>{{ $error }}</li>

                                        @endforeach

                                    </ul>

                                </div>

                            </div>

                        </div>

                    @endif

                    {{-- FORM --}}
                    <form action="{{ route('admin.products.update', $product->id) }}"
                          method="POST"
                          enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        <div class="row g-4">

                            {{-- NAMA --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Nama Barang
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-box text-muted"></i>
                                    </span>

                                    <input type="text"
                                           name="nama_barang"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           value="{{ $product->nama_barang }}"
                                           required>

                                </div>

                            </div>

                            {{-- SKU / KODE BARANG --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Kode Barang / SKU
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-barcode text-muted"></i>
                                    </span>

                                    <input type="text"
                                           name="kode_barang"
                                           id="kode_barang"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           placeholder="Scan barcode atau ketik SKU"
                                           value="{{ old('kode_barang', $product->kode_barang) }}">

                                </div>

                            </div>

                            {{-- KATEGORI --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Kategori
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-layer-group text-muted"></i>
                                    </span>

                                    <select name="category_id"
                                            id="category_id"
                                            class="form-select border-0 bg-light rounded-end-4 py-3"
                                            onchange="handleCategoryChange()"
                                            required>

                                         <option value="">Pilih kategori</option>
                                         @foreach($categories->where('parent_id', null) as $parent)
                                             @if($parent->children->count() > 0)
                                                 <optgroup label="{{ strtoupper($parent->nama_kategori) }}">
                                                     @foreach($parent->children as $child)
                                                        <option value="{{ $child->id }}" data-name="{{ strtolower($child->nama_kategori) }}"
                                                            {{ (old('category_id', $product->category_id) == $child->id) ? 'selected' : '' }}>
                                                            {{ $child->nama_kategori }}
                                                        </option>
                                                     @endforeach
                                                 </optgroup>
                                             @else
                                                 <option value="{{ $parent->id }}" data-name="{{ strtolower($parent->nama_kategori) }}"
                                                     {{ (old('category_id', $product->category_id) == $parent->id) ? 'selected' : '' }}>
                                                     {{ $parent->nama_kategori }}
                                                 </option>
                                             @endif
                                         @endforeach

                                    </select>

                                </div>

                            </div>

                            {{-- HARGA --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Harga Jual
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        Rp
                                    </span>

                                    <input type="text"
                                           id="harga_jual_display"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           required>
                                    <input type="hidden"
                                           name="harga_jual"
                                           id="harga_jual"
                                           value="{{ old('harga_jual', $product->harga_jual) }}"
                                           required>

                                </div>

                            </div>

                            {{-- HARGA BELI --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Harga Beli (Pokok)
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        Rp
                                    </span>

                                    <input type="text"
                                           id="harga_pokok_display"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           required>
                                    <input type="hidden"
                                           name="harga_pokok"
                                           id="harga_pokok"
                                           value="{{ old('harga_pokok', $product->harga_pokok) }}"
                                           required>

                                </div>

                            </div>

                            {{-- STOK --}}
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Stok Aktual
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-cubes text-muted"></i>
                                    </span>

                                    <input type="number"
                                           name="stok_aktual"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           value="{{ $product->stok_aktual }}"
                                           required>

                                </div>
                                <div class="form-text text-muted mt-1 small" style="font-size: 0.78rem;">
                                    Hanya untuk koreksi stok manual (Stock Opname). Untuk penambahan stok dari supplier, gunakan menu <strong>Barang Masuk</strong> agar riwayat pembelian tercatat secara resmi.
                                </div>

                            </div>

                            {{-- EXPIRED --}}
                            <div class="col-md-6" id="expired_field_container">

                                <label class="form-label fw-semibold">
                                    Tanggal Expired
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-calendar-days text-muted"></i>
                                    </span>

                                    <input type="date"
                                           name="tgl_expired"
                                           id="tgl_expired"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           value="{{ $product->tgl_expired }}">

                                </div>

                            </div>

                            {{-- CUKAI --}}
                            <div class="col-md-6" id="cukai_field_container">

                                <label class="form-label fw-semibold">
                                    Tahun Cukai
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-light border-0 rounded-start-4">
                                        <i class="fas fa-stamp text-muted"></i>
                                    </span>

                                    <input type="date"
                                           name="tgl_cukai"
                                           id="tgl_cukai"
                                           class="form-control border-0 bg-light rounded-end-4 py-3"
                                           value="{{ $product->tgl_cukai }}">

                                </div>

                            </div>

                            {{-- AKTIFKAN VARIAN --}}
                            <div class="col-12">
                                <div class="form-check form-switch p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between">
                                    <div class="ps-2">
                                        <label class="form-check-label fw-bold text-dark mb-1" for="has_variants">Aktifkan Varian Produk</label>
                                        <p class="text-muted small mb-0">Aktifkan jika produk ini memiliki opsi rasa, kadar nikotin (nic 3, 6, 9), tingkat dingin, atau variasi lainnya dengan stok terpisah.</p>
                                    </div>
                                    <input class="form-check-input fs-4 me-2" type="checkbox" id="has_variants" name="has_variants" value="1" onchange="toggleVariantSection()" {{ $product->variants->isNotEmpty() ? 'checked' : '' }}>
                                </div>
                            </div>

                            {{-- DOCK DAFTAR VARIAN --}}
                            <div class="col-12" id="variant_section" style="display: {{ $product->variants->isNotEmpty() ? 'block' : 'none' }};">
                                <div class="card border rounded-4 overflow-hidden">
                                    <div class="card-header bg-light border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <i class="fas fa-list-check me-2 text-primary"></i>Daftar Varian Produk
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="addVariantRow()">
                                            <i class="fas fa-plus me-1"></i> Tambah Varian
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0" id="variant_table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-4">Nama Varian <span class="text-danger">*</span></th>
                                                        <th>Kode Barang / SKU</th>
                                                        <th>Harga Beli (Opsional)</th>
                                                        <th>Harga Jual (Opsional)</th>
                                                        <th>Stok Aktual <span class="text-danger">*</span></th>
                                                        <th class="expired-column">Tgl Expired</th>
                                                        <th class="cukai-column">Tgl Cukai</th>
                                                        <th>Gambar Varian</th>
                                                        <th class="text-center pe-4" style="width: 80px;">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="variant_container">
                                                    {{-- Baris Varian ditambahkan via JS --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- GAMBAR --}}
                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Gambar Produk
                                </label>

                                {{-- UPLOAD AREA --}}
                                <div class="position-relative p-4 rounded-4 border border-2 border-dashed bg-light text-center overflow-hidden">

                                    <input type="file"
                                           name="gambar[]"
                                           multiple
                                           class="form-control position-absolute top-0 start-0 opacity-0 w-100 h-100"
                                           accept="image/*"
                                           onchange="previewImages(event)"
                                           style="cursor:pointer;">

                                    <div>

                                        <i class="fas fa-image fs-1 text-primary mb-3"></i>

                                        <h6 class="fw-semibold">
                                            Upload Gambar Baru
                                        </h6>

                                        <p class="text-muted small mb-0">
                                            Klik area ini untuk mengunggah gambar produk tambahan
                                        </p>

                                    </div>

                                </div>

                                {{-- Hidden Input for deleted images --}}
                                <div id="deleted_images_container"></div>

                                {{-- PREVIEW --}}
                                <div class="mt-4 d-flex flex-wrap justify-content-center gap-3" id="preview_container">
                                    {{-- Existing images from DB --}}
                                    @foreach($product->images as $img)
                                        <div class="preview-card" id="existing_img_{{ $img->id }}">
                                            <img src="{{ asset('storage/' . $img->path) }}" class="w-100 h-100 object-fit-cover">
                                            <div class="delete-overlay" onclick="markImageForDeletion({{ $img->id }}, 'existing_img_{{ $img->id }}')">
                                                <i class="fas fa-trash text-white fs-4"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    {{-- New uploads container --}}
                                    <div id="new_uploads_previews" class="d-flex flex-wrap gap-3"></div>
                                </div>

                                <small class="text-muted d-block mt-3">
                                    Kosongkan jika tidak ingin mengganti gambar produk.
                                </small>

                            </div>

                        </div>

                        {{-- FOOTER BUTTON --}}
                        <div class="d-flex justify-content-end gap-3 mt-5">

                            <a href="{{ route('admin.products.index') }}"
                               class="btn btn-light border rounded-4 px-4 py-2">

                                Batal

                            </a>

                            <button type="submit"
                                    class="btn btn-success rounded-4 px-4 py-2 shadow-sm">

                                <i class="fas fa-floppy-disk me-2"></i>
                                Update Produk

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- JAVASCRIPT LOGIC --}}
<script>

let variantIndex = 0;

function toggleVariantSection()
{
    const hasVariants = document.getElementById('has_variants').checked;
    const variantSection = document.getElementById('variant_section');
    const stokInput = document.getElementsByName('stok_aktual')[0];
    
    if (hasVariants) {
        variantSection.style.display = 'block';
        stokInput.value = '';
        stokInput.disabled = true;
        stokInput.removeAttribute('required');
        
        const container = document.getElementById('variant_container');
        if (container.children.length === 0) {
            addVariantRow();
        }
    } else {
        variantSection.style.display = 'none';
        stokInput.disabled = false;
        stokInput.setAttribute('required', 'required');
    }
}

function handleCategoryChange()
{
    const select = document.getElementById('category_id');
    const selectedOption = select.options[select.selectedIndex];
    const categoryName = selectedOption ? (selectedOption.getAttribute('data-name') || '') : '';
    
    const isLiquid = ['liquid', 'freebase', 'saltnic'].includes(categoryName.toLowerCase());
    const expiredContainer = document.getElementById('expired_field_container');
    const expiredInput = document.getElementById('tgl_expired');
    const cukaiContainer = document.getElementById('cukai_field_container');
    const cukaiInput = document.getElementById('tgl_cukai');
    
    if (isLiquid) {
        if (expiredContainer) expiredContainer.style.display = 'block';
        if (expiredInput) expiredInput.disabled = false;
        if (cukaiContainer) cukaiContainer.style.display = 'block';
        if (cukaiInput) cukaiInput.disabled = false;
        
        document.querySelectorAll('.expired-column').forEach(el => {
            el.style.display = 'table-cell';
        });
        document.querySelectorAll('.expired-column input').forEach(el => {
            el.disabled = false;
        });
        
        document.querySelectorAll('.cukai-column').forEach(el => {
            el.style.display = 'table-cell';
        });
        document.querySelectorAll('.cukai-column input').forEach(el => {
            el.disabled = false;
        });
    } else {
        if (expiredContainer) expiredContainer.style.display = 'none';
        if (expiredInput) {
            expiredInput.value = '';
            expiredInput.disabled = true;
        }
        
        if (cukaiContainer) cukaiContainer.style.display = 'none';
        if (cukaiInput) {
            cukaiInput.value = '';
            cukaiInput.disabled = true;
        }
        
        document.querySelectorAll('.expired-column').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.expired-column input').forEach(el => {
            el.value = '';
            el.disabled = true;
        });
        
        document.querySelectorAll('.cukai-column').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.cukai-column input').forEach(el => {
            el.value = '';
            el.disabled = true;
        });
    }
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function addVariantRow(data = {})
{
    const container = document.getElementById('variant_container');
    const row = document.createElement('tr');
    row.id = `variant_row_${variantIndex}`;
    
    let idInput = '';
    let imageHtml = '';
    let originalUrlAttr = '';
    if (data.id) {
        idInput = `<input type="hidden" name="variants[${variantIndex}][id]" value="${data.id}">`;
    }
    
    if (data.gambar_url) {
        originalUrlAttr = `data-original-url="${data.gambar_url}"`;
        imageHtml = `
            <div id="variant_image_preview_${variantIndex}" class="d-flex align-items-center gap-2 mb-2">
                <img src="${data.gambar_url}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                <span class="badge bg-success text-white px-2 py-1" style="font-size: 10px; font-weight: 500; display: inline-flex; align-items: center;">
                    <i class="fas fa-check-circle me-1"></i> Gambar Terunggah
                </span>
            </div>
        `;
    } else {
        imageHtml = `
            <div id="variant_image_preview_${variantIndex}" class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 10px; font-weight: 500; display: inline-flex; align-items: center;">
                    <i class="fas fa-image-slash me-1"></i> Belum Ada Gambar
                </span>
            </div>
        `;
    }
    
    row.innerHTML = `
        <td class="ps-4 py-3">
            ${idInput}
            <input type="text" name="variants[${variantIndex}][nama_varian]" class="form-control rounded-3" placeholder="Contoh: Nic 3mg" required value="${data.nama_varian || ''}">
        </td>
        <td>
            <input type="text" name="variants[${variantIndex}][kode_barang]" class="form-control rounded-3" placeholder="SKU Varian" value="${data.kode_barang || ''}">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text bg-light border">Rp</span>
                <input type="text" class="form-control rounded-end-3 rupiah-variant-pokok-display" placeholder="Ikuti induk">
                <input type="hidden" name="variants[${variantIndex}][harga_pokok]" id="variant_harga_pokok_${variantIndex}" value="${data.harga_pokok || ''}">
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text bg-light border">Rp</span>
                <input type="text" class="form-control rounded-end-3 rupiah-variant-display" placeholder="Ikuti induk">
                <input type="hidden" name="variants[${variantIndex}][harga_jual]" id="variant_harga_${variantIndex}" value="${data.harga_jual || ''}">
            </div>
        </td>
        <td>
            <input type="number" name="variants[${variantIndex}][stok_aktual]" class="form-control rounded-3" placeholder="Stok" required min="0" value="${data.stok_aktual || 0}">
        </td>
        <td class="expired-column">
            <input type="date" name="variants[${variantIndex}][tgl_expired]" class="form-control rounded-3" value="${data.tgl_expired || ''}">
        </td>
        <td class="cukai-column">
            <input type="date" name="variants[${variantIndex}][tgl_cukai]" class="form-control rounded-3" value="${data.tgl_cukai || ''}">
        </td>
        <td>
            ${imageHtml}
            <input type="file" name="variants[${variantIndex}][gambar]" id="variant_gambar_input_${variantIndex}" class="form-control rounded-3 variant-image-input" accept="image/*" onchange="previewVariantImage(this, ${variantIndex})" ${originalUrlAttr}>
        </td>
        <td class="text-center pe-4">
            <button type="button" class="btn btn-outline-danger btn-sm border-0 rounded-circle p-2" onclick="removeVariantRow(${variantIndex})">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    
    // Set column visibility of the newly added row based on category selection
    const select = document.getElementById('category_id');
    const selectedOption = select.options[select.selectedIndex];
    const categoryName = selectedOption ? (selectedOption.getAttribute('data-name') || '') : '';
    const isLiquid = ['liquid', 'freebase', 'saltnic'].includes(categoryName.toLowerCase());
    
    const expiredCol = row.querySelector('.expired-column');
    const expiredInp = row.querySelector('.expired-column input');
    const cukaiCol = row.querySelector('.cukai-column');
    const cukaiInp = row.querySelector('.cukai-column input');
    if (isLiquid) {
        if (expiredCol) expiredCol.style.display = 'table-cell';
        if (expiredInp) expiredInp.disabled = false;
        if (cukaiCol) cukaiCol.style.display = 'table-cell';
        if (cukaiInp) cukaiInp.disabled = false;
    } else {
        if (expiredCol) expiredCol.style.display = 'none';
        if (expiredInp) {
            expiredInp.value = '';
            expiredInp.disabled = true;
        }
        if (cukaiCol) cukaiCol.style.display = 'none';
        if (cukaiInp) {
            cukaiInp.value = '';
            cukaiInp.disabled = true;
        }
    }
    
    // Add formatting listener for variant price
    const displayVal = row.querySelector('.rupiah-variant-display');
    const hiddenVal = row.querySelector(`#variant_harga_${variantIndex}`);
    if (displayVal && hiddenVal) {
        displayVal.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                hiddenVal.value = value;
                this.value = formatNumber(value);
            } else {
                hiddenVal.value = '';
                this.value = '';
            }
        });
        
        if (hiddenVal.value) {
            displayVal.value = formatNumber(hiddenVal.value);
        }
    }

    // Add formatting listener for variant purchase price
    const displayValPokok = row.querySelector('.rupiah-variant-pokok-display');
    const hiddenValPokok = row.querySelector(`#variant_harga_pokok_${variantIndex}`);
    if (displayValPokok && hiddenValPokok) {
        displayValPokok.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                hiddenValPokok.value = value;
                this.value = formatNumber(value);
            } else {
                hiddenValPokok.value = '';
                this.value = '';
            }
        });
        
        if (hiddenValPokok.value) {
            displayValPokok.value = formatNumber(hiddenValPokok.value);
        }
    }
    
    variantIndex++;
}

function removeVariantRow(index)
{
    const row = document.getElementById(`variant_row_${index}`);
    if (row) {
        row.remove();
    }
}

// Preload varian yang sudah ada
document.addEventListener('DOMContentLoaded', function() {
    const existingVariants = {!! json_encode($product->variants) !!};
    if (existingVariants.length > 0) {
        existingVariants.forEach(variant => {
            addVariantRow({
                id: variant.id,
                nama_varian: variant.nama_varian,
                kode_barang: variant.kode_barang,
                harga_pokok: variant.harga_pokok,
                harga_jual: variant.harga_jual,
                stok_aktual: variant.stok_aktual,
                tgl_expired: variant.tgl_expired ? variant.tgl_expired.substring(0, 10) : '',
                tgl_cukai: variant.tgl_cukai ? variant.tgl_cukai.substring(0, 10) : '',
                gambar_url: variant.gambar ? (variant.gambar.startsWith('http') ? variant.gambar : '{{ asset('storage') }}/' + variant.gambar) : ''
            });
        });
        
        // Nonaktifkan input stok utama
        const stokInput = document.getElementsByName('stok_aktual')[0];
        stokInput.disabled = true;
        stokInput.removeAttribute('required');
    }
    
    handleCategoryChange();

    // Init Rupiah main input
    const displayInput = document.getElementById('harga_jual_display');
    const hiddenInput = document.getElementById('harga_jual');
    if (displayInput && hiddenInput) {
        displayInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                hiddenInput.value = value;
                this.value = formatNumber(value);
            } else {
                hiddenInput.value = '';
                this.value = '';
            }
        });
        
        if (hiddenInput.value) {
            displayInput.value = formatNumber(hiddenInput.value);
        }
    }

    // Init Rupiah main input (Harga Beli)
    const displayInputPokok = document.getElementById('harga_pokok_display');
    const hiddenInputPokok = document.getElementById('harga_pokok');
    if (displayInputPokok && hiddenInputPokok) {
        displayInputPokok.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                hiddenInputPokok.value = value;
                this.value = formatNumber(value);
            } else {
                hiddenInputPokok.value = '';
                this.value = '';
            }
        });
        
        if (hiddenInputPokok.value) {
            displayInputPokok.value = formatNumber(hiddenInputPokok.value);
        }
    }

    // Prevent Enter key from submitting the form when focused on SKU / kode_barang inputs
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const activeEl = document.activeElement;
            if (activeEl && (activeEl.id === 'kode_barang' || activeEl.name.includes('kode_barang') || activeEl.classList.contains('sku-scan-input'))) {
                e.preventDefault();
                // Move focus to next input field to speed up inputting!
                const form = activeEl.form;
                if (form) {
                    const elements = Array.from(form.elements).filter(el => !el.disabled && el.type !== 'hidden');
                    const index = elements.indexOf(activeEl);
                    if (index > -1 && elements[index + 1]) {
                        elements[index + 1].focus();
                    }
                }
            }
        }
    });
});

let selectedFiles = [];

function previewImages(event) {
    const input = event.target;
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            selectedFiles.push(input.files[i]);
        }
    }
    updateFileInput(input);
    renderAllPreviews();
}

function updateFileInput(input) {
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
}

function removeSelectedFile(index) {
    selectedFiles.splice(index, 1);
    const input = document.querySelector('input[name="gambar[]"]');
    if (input) {
        updateFileInput(input);
    }
    renderAllPreviews();
}

function markImageForDeletion(imageId, elementId) {
    // Hide the element visually
    const el = document.getElementById(elementId);
    if (el) {
        el.style.display = 'none';
    }
    
    // Append to hidden input
    const container = document.getElementById('deleted_images_container');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'deleted_images[]';
    input.value = imageId;
    container.appendChild(input);
}

function renderAllPreviews() {
    const container = document.getElementById('new_uploads_previews');
    container.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const card = document.createElement('div');
            card.className = 'preview-card';
            card.innerHTML = `
                <img src="${e.target.result}" class="w-100 h-100 object-fit-cover">
                <div class="delete-overlay" onclick="removeSelectedFile(${index})">
                    <i class="fas fa-trash text-white fs-4"></i>
                </div>
            `;
            container.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
}

function previewVariantImage(input, index) {
    const previewDiv = document.getElementById(`variant_image_preview_${index}`);
    if (!previewDiv) return;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewDiv.style.setProperty('display', 'flex', 'important');
            previewDiv.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                </div>
                <div>
                    <span class="badge bg-primary text-white px-2 py-1" style="font-size: 10px; font-weight: 500; display: inline-flex; align-items: center;">
                        <i class="fas fa-file-image me-1"></i> Gambar Terpilih
                    </span>
                    <div class="text-muted mt-1" style="font-size: 9px; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${input.files[0].name}
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        const originalUrl = input.getAttribute('data-original-url');
        if (originalUrl) {
            previewDiv.style.setProperty('display', 'flex', 'important');
            previewDiv.innerHTML = `
                <img src="${originalUrl}" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                <span class="badge bg-success text-white px-2 py-1" style="font-size: 10px; font-weight: 500;">
                    <i class="fas fa-check-circle me-1"></i> Gambar Terunggah
                </span>
            `;
        } else {
            previewDiv.style.setProperty('display', 'flex', 'important');
            previewDiv.innerHTML = `
                <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 10px; font-weight: 500; display: inline-flex; align-items: center;">
                    <i class="fas fa-image-slash me-1"></i> Belum Ada Gambar
                </span>
            `;
        }
    }
}

</script>

@endsection