@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 fw-bold">Setting Harga B2B (Grosir)</h1>
            <p class="text-muted small">Kelola potongan harga khusus B2B (Reseller/Cabang) berdasarkan jumlah minimal pembelian.</p>
        </div>
        <a href="{{ route('admin.b2b-prices.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i> Tambah Setting Harga
        </a>
    </div>

    {{-- Notifikasi Sukses/Gagal --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small fw-bold">PRODUK</th>
                            <th class="text-secondary small fw-bold">VARIAN</th>
                            <th class="text-secondary small fw-bold">MINIMAL PEMBELIAN</th>
                            <th class="text-secondary small fw-bold">POTONGAN</th>
                            <th class="text-end pe-4 text-secondary small fw-bold">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $s)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $s->product->nama_barang ?? 'Produk Dihapus' }}</div>
                                <div class="text-muted small">Eceran: Rp {{ number_format($s->product->harga_jual ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td>
                                @if($s->variant)
                                    <span class="badge bg-info-subtle text-info px-3 py-2 rounded-3">
                                        <i class="fas fa-code-branch me-1"></i> {{ $s->variant->nama_varian }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-3">
                                        Semua Varian
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                    ≥ {{ $s->min_qty }} Unit
                                </span>
                            </td>
                            <td>
                                @if($s->discount_type === 'percentage')
                                    <span class="text-success fw-bold fs-5">
                                        {{ number_format($s->discount_value, 0) }}%
                                    </span>
                                    <span class="text-muted small">dari harga eceran</span>
                                @else
                                    <span class="text-success fw-bold fs-5">
                                        Rp {{ number_format($s->discount_value, 0, ',', '.') }}
                                    </span>
                                    <span class="text-muted small">per unit</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.b2b-prices.edit', $s->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2">
                                    Edit
                                </a>
                                <form action="{{ route('admin.b2b-prices.destroy', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Apakah Anda yakin ingin menghapus setting harga ini?')">
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
