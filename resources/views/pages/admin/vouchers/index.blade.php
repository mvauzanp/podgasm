@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;"><i class="fas fa-ticket-alt text-primary me-2"></i>Manajemen Voucher B2C</h4>
            <p class="text-muted small mb-0">
                Kelola dan terapkan promo potongan harga belanja bagi pelanggan ritel B2C.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-plus-circle me-1"></i> Buat Voucher Baru
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-1.5"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 py-2.5 px-3">
            <h6 class="fw-semibold mb-0 small">Daftar Voucher Promo</h6>
            <small class="text-muted" style="font-size: 0.72rem;">Master data kode voucher diskon B2C aktif dan riwayat kuota</small>
        </div>

        <div class="card-body p-0">
            @if($vouchers->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-ticket-alt fa-2x text-muted mb-2 d-block opacity-25"></i>
                    <p class="text-muted small mb-0">Belum ada voucher dibuat saat ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-2.5">Kode Voucher</th>
                                <th>Jenis</th>
                                <th>Potongan</th>
                                <th>Min. Belanja</th>
                                <th>Kuota Terpakai</th>
                                <th>Masa Berlaku</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                                <tr class="border-top">
                                    <td class="ps-3 py-2.5">
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                            {{ $voucher->code }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($voucher->type === 'nominal')
                                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">Nominal Rp</span>
                                        @elseif($voucher->type === 'percentage')
                                            <span class="badge bg-info-subtle text-info px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">Persentase %</span>
                                        @elseif($voucher->type === 'shipping_subsidy')
                                            <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">Subsidi Ongkir</span>
                                        @else
                                            <span class="badge bg-dark-subtle text-dark px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">{{ $voucher->type }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold" style="font-size: 0.84rem;">
                                        @if($voucher->type === 'nominal' || $voucher->type === 'shipping_subsidy')
                                            Rp {{ number_format($voucher->value, 0, ',', '.') }}
                                        @else
                                            {{ $voucher->value }}%
                                            @if($voucher->max_discount)
                                                <small class="text-muted d-block fw-normal" style="font-size: 0.7rem;">(Maks. Rp {{ number_format($voucher->max_discount, 0, ',', '.') }})</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="font-size: 0.82rem;">
                                        Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 small" style="font-size: 0.8rem;">
                                            <span class="fw-semibold">{{ $voucher->used_count }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="text-muted">{{ $voucher->quota !== null ? $voucher->quota : '∞' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($voucher->start_date || $voucher->end_date)
                                            <div class="small text-muted" style="font-size: 0.75rem;">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $voucher->start_date ? $voucher->start_date->format('d M Y') : 'Mulai Sekarang' }}
                                                s.d
                                                {{ $voucher->end_date ? $voucher->end_date->format('d M Y') : 'Selamanya' }}
                                            </div>
                                        @else
                                            <span class="text-muted small" style="font-size: 0.75rem;">Tanpa Batas Tanggal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($voucher->is_active)
                                            <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.7rem;">
                                                <i class="fas fa-check-circle me-1"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.7rem;">
                                                <i class="fas fa-times-circle me-1"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;">
                                                    <i class="fas fa-trash"></i> Hapus
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
