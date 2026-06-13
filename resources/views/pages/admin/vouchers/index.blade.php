@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-ticket-alt text-primary me-2"></i>Manajemen Voucher B2C</h3>
            <p class="text-muted mb-0">
                Kelola dan terapkan promo potongan harga belanja bagi pelanggan ritel B2C.
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary rounded-pill px-4 py-2-5 shadow-sm fw-bold">
                <i class="fas fa-plus-circle me-1"></i> Buat Voucher Baru
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-4 px-4">
            <h5 class="fw-semibold mb-1">Daftar Voucher Promo</h5>
            <small class="text-muted">Master data kode voucher diskon B2C aktif dan riwayat kuota</small>
        </div>

        <div class="card-body p-0">
            @if($vouchers->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted fw-semibold">Belum ada voucher dibuat saat ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Kode Voucher</th>
                                <th>Jenis</th>
                                <th>Potongan</th>
                                <th>Min. Belanja</th>
                                <th>Kuota Terpakai</th>
                                <th>Masa Berlaku</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                                <tr class="border-top">
                                    <td class="ps-4 py-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                            {{ $voucher->code }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($voucher->type === 'nominal')
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">Nominal Rp</span>
                                        @elseif($voucher->type === 'percentage')
                                            <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fw-semibold">Persentase %</span>
                                        @elseif($voucher->type === 'shipping_subsidy')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">Subsidi Ongkir</span>
                                        @else
                                            <span class="badge bg-dark-subtle text-dark px-3 py-2 rounded-pill fw-semibold">{{ $voucher->type }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">
                                        @if($voucher->type === 'nominal' || $voucher->type === 'shipping_subsidy')
                                            Rp {{ number_format($voucher->value, 0, ',', '.') }}
                                        @else
                                            {{ $voucher->value }}%
                                            @if($voucher->max_discount)
                                                <small class="text-muted d-block fw-normal">(Maks. Rp {{ number_format($voucher->max_discount, 0, ',', '.') }})</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $voucher->used_count }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="text-muted">{{ $voucher->quota !== null ? $voucher->quota : '∞' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($voucher->start_date || $voucher->end_date)
                                            <div class="small fw-semibold text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $voucher->start_date ? $voucher->start_date->format('d M Y') : 'Mulai Sekarang' }}
                                                s.d
                                                {{ $voucher->end_date ? $voucher->end_date->format('d M Y') : 'Selamanya' }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Tanpa Batas Tanggal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($voucher->is_active)
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-times-circle me-1"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- PAGINATION --}}
        @if(!$vouchers->isEmpty())
            <div class="card-footer bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-end">
                    {{ $vouchers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
