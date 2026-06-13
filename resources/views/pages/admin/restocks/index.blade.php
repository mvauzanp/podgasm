@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-dark">
            <i class="fas fa-file-import me-2 text-primary"></i>
            Riwayat Barang Masuk (Restock)
        </h4>
        <a href="{{ route('admin.restocks.create') }}" class="btn btn-primary px-4 rounded-pill shadow-sm transition-all hover-scale">
            <i class="fas fa-plus me-1"></i> Tambah Barang Masuk
        </a>
    </div>

    {{-- BANNER INFORMASI --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-start">
                <div class="bg-white bg-opacity-10 p-3 rounded-3 me-3 border border-white border-opacity-10">
                    <i class="fas fa-dolly fa-2x text-primary-subtle"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-2">Pencatatan &amp; Jejak Audit Barang Masuk</h5>
                    <p class="mb-0 opacity-75 fs-6">
                        Gunakan modul ini untuk mencatat pengadaan barang masuk dari supplier. Sistem akan secara otomatis menambahkan stok aktual di database utama, mencatat harga pokok pembelian (harga beli), dan merekam data admin penginput sebagai audit trail skripsi Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- SEARCH BAR --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.restocks.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" 
                               placeholder="Cari berdasarkan nama supplier atau ID transaksi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-3">Cari</button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE RIWAYAT --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="m-0 fw-bold text-dark">Data Pengadaan &amp; Barang Masuk</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 150px;">ID Transaksi</th>
                        <th>Supplier</th>
                        <th class="text-center">Jumlah Item</th>
                        <th class="text-end">Total Biaya Pembelian</th>
                        <th>Tanggal Masuk</th>
                        <th>Admin Penginput</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restocks as $restock)
                    <tr class="border-top">
                        <td class="ps-4 fw-semibold text-primary">
                            #BM-{{ str_pad($restock->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $restock->supplier_name }}</div>
                            @if($restock->notes)
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                    {{ $restock->notes }}
                                </small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-bold">
                                {{ $restock->items->count() }} jenis barang
                            </span>
                        </td>
                        <td class="text-end fw-bold text-success">
                            Rp {{ number_format($restock->total_cost, 0, ',', '.') }}
                        </td>
                        <td>
                            {{ $restock->created_at->format('d M Y') }}
                            <small class="text-muted d-block">{{ $restock->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $restock->user->name ?? '-' }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.75rem;">{{ $restock->user->role ?? 'admin' }}</small>
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('admin.restocks.show', $restock->id) }}" 
                               class="btn btn-outline-dark btn-sm px-3 rounded-pill shadow-sm transition-all hover-scale">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                <p class="m-0 fw-semibold">Belum ada riwayat transaksi barang masuk.</p>
                                <p class="text-xs text-muted">Silakan klik "Tambah Barang Masuk" untuk menambah stok persediaan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($restocks->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $restocks->links() }}
        </div>
        @endif
    </div>

</div>

<style>
    .hover-scale:hover {
        transform: scale(1.05);
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection
