@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0.5 text-dark" style="font-size: 1.25rem;">Setting Harga B2B (Grosir)</h4>
            <p class="text-muted small mb-0">Kelola potongan harga khusus B2B (Reseller) berdasarkan jumlah minimal pembelian.</p>
        </div>
        <div>
            <a href="{{ route('admin.b2b-prices.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 py-1.5 shadow-sm" style="font-size: 0.82rem;">
                <i class="fas fa-plus me-1.5"></i> Tambah Setting Harga
            </a>
        </div>
    </div>

    {{-- Notifikasi Sukses/Gagal --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="fas fa-check-circle me-1.5"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2.5 text-secondary small fw-bold">PRODUK</th>
                            <th class="text-secondary small fw-bold">VARIAN</th>
                            <th class="text-secondary small fw-bold">MINIMAL PEMBELIAN</th>
                            <th class="text-secondary small fw-bold">POTONGAN</th>
                            <th class="text-end pe-3 text-secondary small fw-bold">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $s)
                        <tr class="border-top">
                            <td class="ps-3 py-2.5">
                                <div class="fw-semibold text-dark" style="font-size: 0.84rem;">{{ $s->product->nama_barang ?? 'Produk Dihapus' }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Eceran: Rp {{ number_format($s->product->harga_jual ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td>
                                @if($s->variant)
                                    <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="fas fa-code-branch me-1"></i> {{ $s->variant->nama_varian }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        Semua Varian
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    &ge; {{ $s->min_qty }} Unit
                                </span>
                            </td>
                            <td>
                                @if($s->discount_type === 'percentage')
                                    <span class="text-success fw-bold" style="font-size: 0.9rem;">
                                        {{ number_format($s->discount_value, 0) }}%
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.72rem;">dari harga eceran</span>
                                @else
                                    <span class="text-success fw-bold" style="font-size: 0.9rem;">
                                        Rp {{ number_format($s->discount_value, 0, ',', '.') }}
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.72rem;">per unit</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.b2b-prices.edit', $s->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 me-1" style="font-size: 0.75rem;">
                                    Edit
                                </a>
                                <form action="{{ route('admin.b2b-prices.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;" onclick="return confirm('Apakah Anda yakin ingin menghapus setting harga ini?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-25">
                                    <i class="fas fa-hand-holding-dollar fa-4x mb-3 text-secondary"></i>
                                    <h5 class="fw-bold">Belum ada setting harga B2B</h5>
                                    <p class="small text-muted">Silakan klik tombol Tambah untuk membuat konfigurasi harga grosir pertama Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $settings->links() }}
    </div>
</div>
@endsection
