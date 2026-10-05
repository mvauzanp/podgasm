@extends('layouts.admin')

@section('content_admin')

<div class="container-fluid p-0">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">

        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">Manajemen Inventaris</h4>
            <p class="text-muted small mb-0">
                Monitoring stok barang, safety stock, dan masa berlaku produk.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0 flex-wrap">
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                <i class="fas fa-check-circle me-1"></i> Master Produk Sinkron Olsera POS
            </span>
            <a href="{{ route('admin.olsera.index') }}"
               class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-rotate me-1.5"></i>
                Panel Olsera
            </a>
        </div>

    </div>

    {{-- SUMMARY CARD --}}
    <div class="row g-2.5 g-md-3 mb-3">

        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-0.5" style="font-size: 0.75rem;">Total Produk</p>
                            <h4 class="fw-bold mb-0" style="font-size: 1.35rem;">{{ $totalProductsCount }}</h4>
                        </div>

                        <div class="bg-primary bg-opacity-10 p-2.5 rounded-3">
                            <i class="fas fa-boxes-stacked text-primary fs-5"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-0.5" style="font-size: 0.75rem;">Stok Menipis</p>
                            <h4 class="fw-bold text-danger mb-0" style="font-size: 1.35rem;">
                                {{ $lowStockCount }}
                            </h4>
                        </div>

                        <div class="bg-danger bg-opacity-10 p-2.5 rounded-3">
                            <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-0.5" style="font-size: 0.75rem;">Stok Aman</p>
                            <h4 class="fw-bold text-success mb-0" style="font-size: 1.35rem;">
                                {{ $safeStockCount }}
                            </h4>
                        </div>

                        <div class="bg-success bg-opacity-10 p-2.5 rounded-3">
                            <i class="fas fa-circle-check text-success fs-5"></i>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">

        {{-- TABLE HEADER --}}
        <div class="card-header bg-white border-0 py-2.5 px-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>
                    <h6 class="fw-semibold mb-0 small">
                        Data Inventaris Produk
                    </h6>

                    <small class="text-muted" style="font-size: 0.72rem;">
                        Daftar seluruh produk gudang pusat Podgasm
                    </small>
                </div>

                <div class="mt-1 mt-md-0">

                    <form action="" method="GET">
                        <div class="input-group input-group-sm">

                            <span class="input-group-text bg-light border-0 rounded-start-3">
                                <i class="fas fa-search text-muted small"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="form-control form-control-sm border-0 bg-light rounded-end-3"
                                   placeholder="Cari produk..." style="font-size: 0.8rem;">

                        </div>
                    </form>

                </div>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th class="ps-3 py-2.5">Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Safety Stock</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($products as $product)

                        <tr class="border-top">

                            {{-- PRODUCT --}}
                            <td class="ps-3 py-2.5">

                                <div class="d-flex align-items-center">

                                    @if($product->gambar)
                                        <img src="{{ $product->gambar_url }}" 
                                             class="rounded-2 shadow-sm me-2.5"
                                             style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary bg-opacity-10 rounded-2 p-2 me-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-box text-primary small"></i>
                                        </div>
                                    @endif

                                    <div>

                                        <div class="fw-semibold text-truncate" style="max-width: 220px; font-size: 0.84rem;">
                                            {{ $product->nama_barang }}
                                        </div>

                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            Beli: Rp {{ number_format($product->harga_pokok, 0, ',', '.') }} | Jual: Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            {{-- CATEGORY --}}
                            <td>
                                <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                                    {{ $product->category->nama_kategori ?? '-' }}
                                </span>
                            </td>

                            {{-- STOCK --}}
                            <td>
                                <div class="fw-bold {{ $product->stok_aktual <= $product->nilai_ss ? 'text-danger' : 'text-success' }}" style="font-size: 0.84rem;">
                                    {{ $product->stok_aktual }} pcs
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    Aktual
                                </small>
                            </td>

                            {{-- SAFETY STOCK --}}
                            <td>
                                <div class="fw-bold text-primary" style="font-size: 0.84rem;">
                                    {{ $product->nilai_ss }} pcs
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    Min. Safety
                                </small>
                            </td>

                            {{-- EXPIRED --}}
                            <td>
                                <div class="small mb-0.5" style="font-size: 0.75rem;">
                                    <i class="fas fa-calendar-days text-muted me-1"></i>
                                    Exp: {{ $product->tgl_expired ? date('d M Y', strtotime($product->tgl_expired)) : '-' }}
                                </div>
                                <div class="small text-muted" style="font-size: 0.72rem;">
                                    <i class="fas fa-stamp text-muted me-1"></i>
                                    Cukai: {{ $product->tgl_cukai ? date('Y', strtotime($product->tgl_cukai)) : '-' }}
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                @if($product->stok_aktual <= $product->nilai_ss)
                                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                        <i class="fas fa-triangle-exclamation me-1"></i> Reorder
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                        <i class="fas fa-circle-check me-1"></i> Aman
                                    </span>
                                @endif
                            </td>

                            {{-- ACTION --}}
                            <td class="text-center pe-3">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- EDIT --}}
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" title="Edit Produk">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('admin.products.destroy', $product->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" title="Hapus Produk">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted small">
                                <i class="fas fa-box-open fa-2x mb-2 opacity-25"></i>
                                <p class="mb-0">Belum ada produk yang ditambahkan ke inventaris.</p>
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- PAGINATION --}}
        @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-2.5 px-3">
            <div class="d-flex justify-content-end">
                {{ $products->links() }}
            </div>
        </div>
        @endif

    </div>

</div>

@endsection