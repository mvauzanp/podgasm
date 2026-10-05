@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">

    {{-- HEADER + FILTER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 no-print">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">Laporan Inventaris &amp; Estimasi Kerugian</h4>
            <p class="text-muted small mb-0">Analisis stok berisiko kadaluarsa dan depresiasi pita cukai lama</p>
        </div>

        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <select name="branch_id" class="form-select form-select-sm rounded-3" style="min-width: 200px; font-size: 0.8rem;" onchange="this.form.submit()">
                <option value="all" {{ ($selectedBranch ?? 'all') == 'all' ? 'selected' : '' }}>📦 Semua Stok (Konsolidasi Pusat)</option>
                <option value="hq" {{ ($selectedBranch ?? '') == 'hq' ? 'selected' : '' }}>🏢 Gudang Utama (HQ Surabaya)</option>
            </select>

            <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-3 px-3 shadow-sm" style="font-size: 0.8rem;">
                <i class="fas fa-print me-1.5"></i> Cetak
            </button>
        </form>
    </div>

    {{-- CARD TOTAL --}}
    <div class="row mb-3 no-print">
        <div class="col-xl-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm bg-danger text-white rounded-3">
                <div class="card-body p-3">
                    <h6 class="small text-uppercase mb-1" style="font-size: 0.72rem; opacity: 0.9;">Total Potensi Kerugian</h6>
                    <h4 class="fw-bold mb-0 text-white" style="font-size: 1.35rem;">
                        Rp {{ number_format($reports->sum('estimasi_rugi'), 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- PRINT HEADER --}}
    <div class="print-header text-center mb-3">
        <h5 class="fw-bold">LAPORAN INVENTARIS GUDANG PUSAT</h5>
        <p class="mb-0 small text-muted">
            Tanggal Cetak: {{ date('d M Y') }}
        </p>
    </div>

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="bg-light text-center">
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Tanggal Expired</th>
                        <th>Tanggal Cukai</th>
                        <th>Status</th>
                        <th>Kerugian (Rp)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($reports as $item)
                    <tr>
                        <td><strong>{{ $item['nama'] }}</strong></td>
                        <td>{{ $item['kategori'] }}</td>
                        <td class="text-center">{{ $item['stok'] }}</td>
                        <td class="text-center">
                            {{ $item['tgl_expired'] 
                                ? \Carbon\Carbon::parse($item['tgl_expired'])->format('d/m/Y') 
                                : '-' }}
                        </td>
                        <td class="text-center">
                            {{ $item['tgl_cukai'] 
                                ? \Carbon\Carbon::parse($item['tgl_cukai'])->format('d/m/Y') 
                                : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge 
                                {{ $item['status'] == 'Aman' ? 'bg-success' : ($item['status'] == 'Risiko Tinggi (Expired)' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $item['status'] }}
                            </span>
                        </td>
                        <td class="text-danger fw-bold text-end">
                            {{ number_format($item['estimasi_rugi'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    {{-- TANDA TANGAN PRINT --}}
    <div class="mt-5 text-end print-footer">
        <p>Sidoarjo, {{ now()->format('d M Y') }}</p>
        <br><br>
        <p class="fw-bold">( Admin )</p>
    </div>

</div>

{{-- 🔥 STYLE PRINT --}}
<style>
/* DEFAULT */
.print-header,
.print-footer {
    display: none;
}

/* PRINT MODE */
@media print {

    /* ❌ sembunyikan semua */
    body * {
        visibility: hidden;
    }

    /* ✅ tampilkan area laporan */
    .print-header,
    .print-header *,
    .table,
    .table *,
    .print-footer,
    .print-footer * {
        visibility: visible;
    }

    /* posisi */
    .print-header {
        display: block;
        position: absolute;
        top: 0;
        width: 100%;
        text-align: center;
    }

    .table {
        position: absolute;
        top: 100px;
        width: 100%;
        font-size: 12px;
    }

    .print-footer {
        display: block;
        position: absolute;
        bottom: 0;
        right: 50px;
    }

    /* ❌ hide UI */
    .no-print {
        display: none !important;
    }

    /* rapihin table */
    table {
        border-collapse: collapse;
    }

    table th, table td {
        border: 1px solid #000 !important;
        padding: 6px !important;
    }
}
</style>

@endsection