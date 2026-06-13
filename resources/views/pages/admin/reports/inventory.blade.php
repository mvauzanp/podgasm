@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER + FILTER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold">Laporan Inventaris & Estimasi Kerugian</h4>

        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control form-control-sm"
                   value="{{ request('start_date') }}">
            <input type="date" name="end_date" class="form-control form-control-sm"
                   value="{{ request('end_date') }}">

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i>
            </button>

            <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print"></i>
            </button>
        </form>
    </div>

    {{-- CARD TOTAL --}}
    <div class="row mb-4 no-print">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white rounded-4">
                <div class="card-body">
                    <h6 class="small text-uppercase">Total Potensi Kerugian</h6>
                    <h3 class="fw-bold mb-0">
                        Rp {{ number_format($reports->sum('estimasi_rugi'), 0, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- PRINT HEADER --}}
    <div class="print-header text-center mb-4">
        <h4 class="fw-bold">LAPORAN INVENTARIS</h4>
        <p class="mb-0">
            Periode:
            {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d M Y') : '-' }}
            s/d
            {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d M Y') : '-' }}
        </p>
    </div>

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">

                <thead class="bg-light text-center">
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Tanggal Expired</th>
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