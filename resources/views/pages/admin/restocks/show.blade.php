@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-dark">
            <i class="fas fa-file-import me-2 text-primary"></i>
            Bukti Penerimaan Barang Masuk
        </h4>
        <div>
            <button onclick="window.print();" class="btn btn-outline-dark px-3 rounded-pill me-2">
                <i class="fas fa-print me-1"></i> Cetak Bukti
            </button>
            <a href="{{ route('admin.restocks.index') }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- AUDIT TRAIL CARD --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-shield-halved me-1 text-primary"></i> Jejak Audit Pengadaan</h6>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3">
                        <span class="text-muted small d-block text-uppercase fw-bold">Nomor Invoice</span>
                        <span class="fs-5 fw-extrabold text-primary font-monospace">
                            #BM-{{ str_pad($restock->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block text-uppercase fw-bold">Nama Supplier / Vendor</span>
                        <span class="fw-bold text-dark">{{ $restock->supplier_name }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block text-uppercase fw-bold">Tanggal Diterima</span>
                        <span class="fw-semibold text-dark">
                            {{ $restock->created_at->format('d F Y') }}
                            <small class="text-muted d-block">{{ $restock->created_at->format('H:i') }} WIB</small>
                        </span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small d-block text-uppercase fw-bold">Operator Penerima (Admin)</span>
                        <span class="fw-bold text-dark">{{ $restock->user->name ?? '-' }}</span>
                        <small class="text-muted d-block">{{ $restock->user->email ?? '-' }}</small>
                    </div>

                    @if($restock->notes)
                    <div class="mb-3">
                        <span class="text-muted small d-block text-uppercase fw-bold">Catatan Operasional</span>
                        <div class="bg-light p-3 rounded border text-muted fs-6" style="white-space: pre-line;">
                            {{ $restock->notes }}
                        </div>
                    </div>
                    @endif

                    <div class="bg-success-subtle border border-success-subtle p-3 rounded-3 mt-4">
                        <div class="text-success small fw-bold text-uppercase mb-1">Total Biaya Pembelian</div>
                        <h4 class="fw-extrabold text-success m-0">
                            Rp {{ number_format($restock->total_cost, 0, ',', '.') }}
                        </h4>
                    </div>

                </div>
            </div>
        </div>

        {{-- LIST ITEMS --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-boxes-stacked me-1 text-primary"></i> Daftar Item Diterima</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Produk</th>
                                    <th>Varian</th>
                                    <th class="text-center">Jumlah Masuk</th>
                                    <th class="text-end">Harga Beli Satuan</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restock->items as $index => $item)
                                <tr class="border-top">
                                    <td class="ps-4 fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->product->nama_barang }}</div>
                                        <small class="text-muted">{{ $item->product->category->nama_kategori ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($item->variant)
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-bold">
                                                {{ $item->variant->nama_varian }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-- Tanpa Varian --</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-dark">
                                        {{ $item->quantity }} pcs
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($item->purchase_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold text-dark pe-4">
                                        Rp {{ number_format($item->quantity * $item->purchase_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .content-area, .content-area * {
            visibility: visible;
        }
        .content-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .btn, .sidebar, .navbar-admin {
            display: none !important;
        }
    }
</style>
@endsection
