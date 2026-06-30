@extends('layouts.admin')

@section('content_admin')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">Manajemen Inventaris</h3>
            <p class="text-muted mb-0">
                Monitoring stok barang, safety stock, dan masa berlaku produk.
            </p>
        </div>

        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" 
                    class="btn btn-outline-success rounded-4 px-4 py-2 shadow-sm"
                    data-bs-toggle="modal" 
                    data-bs-target="#importExcelModal">
                <i class="fas fa-file-excel me-2"></i>
                Import Excel
            </button>
            <a href="{{ route('admin.products.create') }}"
               class="btn btn-primary rounded-4 px-4 py-2 shadow-sm">
                <i class="fas fa-plus me-2"></i>
                Tambah Produk
            </a>
        </div>

    </div>

    {{-- SUMMARY CARD --}}
    <div class="row mb-4">

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted mb-1">Total Produk</p>
                            <h3 class="fw-bold mb-0">{{ $totalProductsCount }}</h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                            <i class="fas fa-boxes-stacked text-primary fs-4"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted mb-1">Stok Menipis</p>
                            <h3 class="fw-bold text-danger mb-0">
                                {{ $lowStockCount }}
                            </h3>
                        </div>

                        <div class="bg-danger bg-opacity-10 p-3 rounded-4">
                            <i class="fas fa-triangle-exclamation text-danger fs-4"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted mb-1">Stok Aman</p>
                            <h3 class="fw-bold text-success mb-0">
                                {{ $safeStockCount }}
                            </h3>
                        </div>

                        <div class="bg-success bg-opacity-10 p-3 rounded-4">
                            <i class="fas fa-circle-check text-success fs-4"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        {{-- TABLE HEADER --}}
        <div class="card-header bg-white border-0 py-4 px-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h5 class="fw-semibold mb-1">
                        Data Inventaris Produk
                    </h5>

                    <small class="text-muted">
                        Daftar seluruh produk gudang pusat Podgasm
                    </small>
                </div>

                <div class="mt-3 mt-md-0">

                    <form action="" method="GET">
                        <div class="input-group">

                            <span class="input-group-text bg-light border-0 rounded-start-4">
                                <i class="fas fa-search text-muted"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="form-control border-0 bg-light rounded-end-4"
                                   placeholder="Cari produk...">

                        </div>
                    </form>

                </div>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead class="bg-light">

                        <tr>
                            <th class="ps-4 py-3">Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Safety Stock</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($products as $product)

                        <tr class="border-top">

                            {{-- PRODUCT --}}
                            <td class="ps-4 py-4">

                                <div class="d-flex align-items-center">

                                    @if($product->gambar)
                                        <img src="{{ asset('storage/' . $product->gambar) }}" 
                                             class="rounded-3 shadow-sm me-3"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary bg-opacity-10 rounded-4 p-3 me-3">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                    @endif

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $product->nama_barang }}
                                        </div>

                                        <small class="text-muted">
                                            Beli: Rp {{ number_format($product->harga_pokok, 0, ',', '.') }} | Jual: Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            {{-- CATEGORY --}}
                            <td>
                                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                    {{ $product->category->nama_kategori ?? '-' }}
                                </span>
                            </td>

                            {{-- STOCK --}}
                            <td>

                                <div class="fw-bold {{ $product->stok_aktual <= $product->nilai_ss ? 'text-danger' : 'text-success' }}">
                                    {{ $product->stok_aktual }} pcs
                                </div>

                                <small class="text-muted">
                                    Aktual
                                </small>

                            </td>

                            {{-- SAFETY STOCK --}}
                            <td>

                                <div class="fw-bold text-primary">
                                    {{ $product->nilai_ss }} pcs
                                </div>

                                <small class="text-muted">
                                    Minimum stok
                                </small>

                            </td>

                            {{-- EXPIRED --}}
                            <td>

                                <div class="small mb-1">

                                    <i class="fas fa-calendar-days text-muted me-1"></i>

                                    Exp:
                                    {{ $product->tgl_expired ? date('d M Y', strtotime($product->tgl_expired)) : '-' }}

                                </div>

                                <div class="small">

                                    <i class="fas fa-stamp text-muted me-1"></i>

                                    Cukai:
                                    {{ $product->tgl_cukai ? date('Y', strtotime($product->tgl_cukai)) : '-' }}

                                </div>

                            </td>

                            {{-- STATUS --}}
                            <td>

                                @if($product->stok_aktual <= $product->nilai_ss)

                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                        <i class="fas fa-triangle-exclamation me-1"></i>
                                        Reorder
                                    </span>

                                @else

                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-circle-check me-1"></i>
                                        Aman
                                    </span>

                                @endif

                            </td>

                            {{-- ACTION --}}
                            <td class="text-center pe-4">

                                <div class="d-flex justify-content-center gap-2">

                                    {{-- EDIT --}}
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                       class="btn btn-light border rounded-3 px-3">

                                        <i class="fas fa-pen text-primary"></i>
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('admin.products.destroy', $product->id) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-light border rounded-3 px-3"
                                                onclick="return confirm('Hapus produk ini?')">

                                            <i class="fas fa-trash text-danger"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="7" class="text-center py-5">

                                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png"
                                     width="120"
                                     class="mb-3 opacity-50">

                                <h5 class="fw-semibold">
                                    Data Produk Kosong
                                </h5>

                                <p class="text-muted mb-0">
                                    Belum ada produk yang ditambahkan ke inventaris.
                                </p>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- PAGINATION --}}
        <div class="card-footer bg-white border-0 py-3 px-4">

            <div class="d-flex justify-content-end">

                {{ $products->links() }}

            </div>

        </div>

    </div>

</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="importExcelModalLabel">
                    <i class="fas fa-file-excel text-success me-2"></i>
                    Import Produk Massal (Excel)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-5 border-end pe-md-4 mb-3 mb-md-0">
                            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Petunjuk Pengisian</h6>
                            <ol class="ps-3 text-muted small" style="line-height: 1.6;">
                                <li class="mb-2">Unduh template Excel yang telah disediakan melalui tombol di bawah.</li>
                                <li class="mb-2">Isi data produk sesuai kolom (kolom berwarna abu-abu adalah header).</li>
                                <li class="mb-2">Pastikan <b>Kategori / Sub-Kategori</b> diisi sama persis seperti nama kategori/sub-kategori di tab <i>Daftar Kategori</i> Excel.</li>
                                <li class="mb-2">Untuk <b>Produk Varian</b>: Tulis baris baru dengan nama produk yang sama, lalu isi kolom <i>Nama Varian</i>.</li>
                                <li class="mb-2">Kolom harga dan stok harus diisi dengan angka bulat positif.</li>
                                <li class="mb-2">Format tanggal expired/cukai wajib <code>YYYY-MM-DD</code> (contoh: 2027-12-31).</li>
                            </ol>
                            <div class="mt-4 text-center">
                                <a href="{{ route('admin.products.template') }}" class="btn btn-success rounded-4 w-100 py-2 shadow-sm text-white">
                                    <i class="fas fa-download me-2"></i>Unduh Template Excel
                                </a>
                            </div>
                        </div>
                        <div class="col-md-7 ps-md-4 d-flex flex-column justify-content-center">
                            <h6 class="fw-bold mb-3"><i class="fas fa-cloud-upload-alt text-success me-2"></i>Unggah File</h6>
                            <div class="mb-4">
                                <label for="file_excel" class="form-label text-muted small">Pilih file Excel (.xlsx / .xls)</label>
                                <div class="border border-dashed rounded-4 p-4 text-center bg-light cursor-pointer position-relative" style="border-style: dashed !important; border-width: 2px !important; border-color: #198754 !important;">
                                    <i class="fas fa-file-excel text-success fs-1 mb-3"></i>
                                    <p class="mb-1 fw-semibold small">Pilih File Anda</p>
                                    <p class="text-muted small mb-0">Format file yang didukung: .xlsx atau .xls</p>
                                    <input type="file" name="file_excel" id="file_excel" class="form-control mt-3" accept=".xlsx, .xls" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-4 px-4 py-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-4 px-4 py-2 shadow-sm text-white">
                        <i class="fas fa-upload me-2"></i>Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection