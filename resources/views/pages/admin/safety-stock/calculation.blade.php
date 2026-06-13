@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-dark">
            <i class="fas fa-chart-line me-2 text-primary"></i>
            Optimasi Stok &amp; Safety Stock
        </h4>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
            Metode Safety Stock
        </span>
    </div>

    {{-- BANNER FORMULA SAFETY STOCK --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-start">
                <div class="bg-white bg-opacity-20 p-3 rounded-3 me-3">
                    <i class="fas fa-chart-bar fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-2">Safety Stock Statistik &amp; Analisis ABC (Bebas ROP)</h5>
                    <p class="mb-3 opacity-90 fs-6">
                        Sistem mengklasifikasikan produk secara otomatis dengan **Analisis ABC** (berdasarkan volume penjualan 30 hari terakhir) untuk menentukan **Service Level** optimal, lalu menghitung **Safety Stock** menggunakan deviasi standar penjualan harian.
                    </p>
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 d-inline-block">
                        <span class="badge bg-light text-dark me-2 fw-bold">Rumus Statistik</span>
                        <code class="text-white fs-6 font-monospace">Safety Stock (SS) = Z &times; Deviasi Standar (&sigma;) &times; &radic;Lead Time</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RINGKASAN KLASIFIKASI KARTU --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-danger border-4">
                <div class="card-body py-3">
                    <h6 class="text-muted small text-uppercase mb-1">Kelas A (Fast Moving)</h6>
                    <h5 class="fw-bold m-0 text-danger">SL 98% <span class="text-muted fs-6 fw-normal">(Z = 2.05)</span></h5>
                    <small class="text-muted">Kontribusi penjualan kumulatif 0% - 70%</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-warning border-4">
                <div class="card-body py-3">
                    <h6 class="text-muted small text-uppercase mb-1">Kelas B (Medium Moving)</h6>
                    <h5 class="fw-bold m-0 text-warning">SL 95% <span class="text-muted fs-6 fw-normal">(Z = 1.65)</span></h5>
                    <small class="text-muted">Kontribusi penjualan kumulatif 70% - 90%</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 border-start border-info border-4">
                <div class="card-body py-3">
                    <h6 class="text-muted small text-uppercase mb-1">Kelas C (Slow Moving)</h6>
                    <h5 class="fw-bold m-0 text-info">SL 90% <span class="text-muted fs-6 fw-normal">(Z = 1.28)</span></h5>
                    <small class="text-muted">Kontribusi penjualan kumulatif 90% - 100%</small>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="m-0 fw-bold text-dark">Daftar Produk &amp; Stok Pengaman</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">

                {{-- HEADER --}}
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="min-width: 180px;">Produk</th>
                        <th class="text-center">Stok Aktual</th>
                        <th class="text-center" style="width: 150px;">Avg Lead Time (Hari)</th>
                        <th class="text-center">Kelas ABC</th>
                        <th class="text-center">Tingkat Pelayanan (Z)</th>
                        <th class="text-center">Deviasi Penjualan (&sigma;)</th>
                        <th class="text-center">Safety Stock (SS)</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($products as $pro)
                    <form action="{{ route('admin.ss.calculate', $pro->id) }}" method="POST">
                        @csrf
                        <tr class="border-top">

                            {{-- PRODUK --}}
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $pro->nama_barang }}</div>
                                <small class="text-muted">
                                    {{ $pro->category->nama_kategori ?? '-' }}
                                </small>
                            </td>

                            {{-- STOK --}}
                            <td class="text-center">
                                <span class="fw-bold fs-6 {{ $pro->stok_aktual <= ($pro->nilai_ss ?? 0) ? 'text-danger bg-danger-subtle px-2 py-1 rounded' : 'text-success bg-success-subtle px-2 py-1 rounded' }}">
                                    {{ $pro->stok_aktual }}
                                </span>
                            </td>

                            {{-- INPUT LEAD TIME --}}
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="lead_time"
                                           class="form-control text-center fw-semibold"
                                           value="{{ $pro->lead_time ?? 1 }}" min="0" step="0.01" required>
                                </div>
                            </td>

                            {{-- KELAS ABC --}}
                            <td class="text-center">
                                @if($pro->abc_class === 'A')
                                    <span class="badge bg-danger px-3 py-2 rounded">Kelas A</span>
                                @elseif($pro->abc_class === 'B')
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded">Kelas B</span>
                                @else
                                    <span class="badge bg-info text-white px-3 py-2 rounded">Kelas C</span>
                                @endif
                            </td>

                            {{-- SERVICE LEVEL (Z-SCORE) --}}
                            <td class="text-center">
                                <span class="fw-bold text-dark">{{ $pro->service_level }}</span>
                                <small class="text-muted d-block">Z = {{ $pro->z_score }}</small>
                            </td>

                            {{-- DEV SALES DISPLAY --}}
                            <td class="text-center">
                                <span class="fw-semibold text-dark fs-6">{{ $pro->std_dev_30d ?? 0 }}</span>
                                <small class="text-muted d-block">unit/hari</small>
                            </td>

                            {{-- HASIL SAFETY STOCK --}}
                            <td class="text-center">
                                <span class="badge px-3 py-2 rounded-pill fs-6 
                                    {{ ($pro->nilai_ss ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $pro->nilai_ss ?? 0 }}
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center pe-4">
                                <button type="submit" 
                                        class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm transition-all hover-scale">
                                    <i class="fas fa-calculator me-1"></i> Hitung
                                </button>
                            </td>

                        </tr>
                    </form>
                    @endforeach
                </tbody>

            </table>
        </div>
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