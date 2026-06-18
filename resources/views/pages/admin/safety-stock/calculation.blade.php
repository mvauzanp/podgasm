@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold m-0 text-dark">
            <i class="fas fa-chart-line me-2 text-primary"></i>
            Optimasi Stok &amp; Safety Stock (Tingkat Varian)
        </h4>
        <form action="{{ route('admin.ss.calculate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm transition-all hover-scale">
                <i class="fas fa-sync me-2"></i> Sinkronkan Sekarang
            </button>
        </form>
    </div>

    {{-- BANNER FORMULA SAFETY STOCK --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex align-items-start">
                <div class="bg-white bg-opacity-20 p-3 rounded-3 me-3">
                    <i class="fas fa-chart-bar fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-2">Safety Stock Statistik &amp; Analisis ABC Otomatis (SKU-Level)</h5>
                    <p class="mb-3 opacity-90 fs-6">
                        Sistem mengklasifikasikan produk dan variasi secara otomatis dengan **Analisis ABC** (berdasarkan volume penjualan 30 hari terakhir) untuk menentukan **Service Level** optimal, lalu menghitung **Safety Stock** menggunakan deviasi standar penjualan harian dan Lead Time rata-rata (default: 3 hari).
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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <div>{{ $errors->first() }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="m-0 fw-bold text-dark">Daftar Produk &amp; Stok Pengaman Varian</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">

                {{-- HEADER --}}
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="min-width: 200px;">Produk / Varian</th>
                        <th class="text-center">Stok Aktual</th>
                        <th class="text-center">Lead Time</th>
                        <th class="text-center">Kelas ABC</th>
                        <th class="text-center">Tingkat Pelayanan (Z)</th>
                        <th class="text-center">Deviasi Penjualan (&sigma;)</th>
                        <th class="text-center" style="width: 150px;">Safety Stock (SS)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($products as $pro)
                        @if(!$pro->hasVariants())
                            {{-- PRODUK SEDERHANA (TANPA VARIAN) --}}
                            <tr class="border-top">
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $pro->nama_barang }}</div>
                                    <small class="text-muted">
                                        {{ $pro->category->nama_kategori ?? '-' }}
                                    </small>
                                </td>

                                <td class="text-center">
                                    <span class="fw-bold fs-6 {{ $pro->stok_aktual <= ($pro->nilai_ss ?? 0) ? 'text-danger bg-danger-subtle px-2 py-1 rounded' : 'text-success bg-success-subtle px-2 py-1 rounded' }}">
                                        {{ $pro->stok_aktual }}
                                    </span>
                                </td>

                                <td class="text-center fw-semibold text-dark">
                                    {{ $pro->lead_time ?? 3 }} hari
                                </td>

                                <td class="text-center">
                                    @if($pro->abc_class === 'A')
                                        <span class="badge bg-danger px-3 py-2 rounded">Kelas A</span>
                                    @elseif($pro->abc_class === 'B')
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded">Kelas B</span>
                                    @else
                                        <span class="badge bg-info text-white px-3 py-2 rounded">Kelas C</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span class="fw-bold text-dark">{{ $pro->service_level }}</span>
                                    <small class="text-muted d-block">Z = {{ $pro->z_score }}</small>
                                </td>

                                <td class="text-center">
                                    <span class="fw-semibold text-dark fs-6">{{ $pro->std_dev_30d ?? 0 }}</span>
                                    <small class="text-muted d-block">unit/hari</small>
                                </td>

                                <td class="text-center">
                                    <span class="badge px-3 py-2 rounded-pill fs-6 
                                        {{ ($pro->nilai_ss ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                        {{ $pro->nilai_ss ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @else
                            {{-- PRODUK BERVARIAN --}}
                            <tr class="table-light">
                                <td colspan="7" class="ps-4 fw-bold text-dark">
                                    <i class="fas fa-box text-muted me-2"></i> {{ $pro->nama_barang }}
                                    <small class="text-muted fw-normal ms-2">
                                        ({{ $pro->category->nama_kategori ?? '-' }} &bull; {{ $pro->variants->count() }} Varian)
                                    </small>
                                </td>
                            </tr>
                            @foreach($pro->variants as $var)
                                <tr class="border-top">
                                    <td class="ps-5 text-muted">
                                        <span class="d-inline-block me-2">&mdash;</span> {{ $var->nama_varian }}
                                        @if($var->kode_barang)
                                            <small class="text-muted d-block ps-3">SKU: {{ $var->kode_barang }}</small>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-bold fs-6 {{ $var->stok_aktual <= ($var->nilai_ss ?? 0) ? 'text-danger bg-danger-subtle px-2 py-1 rounded' : 'text-success bg-success-subtle px-2 py-1 rounded' }}">
                                            {{ $var->stok_aktual }}
                                        </span>
                                    </td>

                                    <td class="text-center fw-semibold text-dark">
                                        {{ $var->lead_time ?? 3 }} hari
                                    </td>

                                    <td class="text-center">
                                        @if($var->abc_class === 'A')
                                            <span class="badge bg-danger px-3 py-2 rounded">Kelas A</span>
                                        @elseif($var->abc_class === 'B')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded">Kelas B</span>
                                        @else
                                            <span class="badge bg-info text-white px-3 py-2 rounded">Kelas C</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-bold text-dark">{{ $var->service_level }}</span>
                                        <small class="text-muted d-block">Z = {{ $var->z_score }}</small>
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-semibold text-dark fs-6">{{ $var->std_dev_30d ?? 0 }}</span>
                                        <small class="text-muted d-block">unit/hari</small>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge px-3 py-2 rounded-pill fs-6 
                                            {{ ($var->nilai_ss ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                            {{ $var->nilai_ss ?? 0 }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
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