@extends('layouts.frontend')

{{-- REVISI: Pastikan section name sesuai dengan yield di layouts.frontend --}}
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 fw-bold">Status Pengiriman Stok</h1>
            <p class="text-muted small">Pantau status permintaan barang dari gudang pusat ke cabang.</p>
        </div>
        <a href="{{ route('branch.request') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Request Baru
        </a>
    </div>

    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">ID REQ</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Estimasi Tiba</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">#SRQ-{{ $r->id }}</span>
                            </td>
                            <td>
                                {{-- FIX: Ganti nama_produk menjadi nama_barang dan tambah null safety --}}
                                <div class="fw-bold text-dark">
                                    {{ $r->product->nama_barang ?? 'Produk Dihapus' }}
                                    @if($r->variant)
                                        <span class="d-block small text-muted fw-normal mt-1">Varian: {{ $r->variant->nama_varian }}</span>
                                    @endif
                                </div>
                                <small class="badge bg-secondary text-white" style="font-size: 0.7rem;">
                                    {{ strtoupper($r->prioritas ?? 'NORMAL') }} PRIORITY
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3">{{ $r->jumlah }} Unit</span>
                            </td>
                            <td>
                                @if($r->status == 'Pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Menunggu Konfirmasi</span>
                                @elseif($r->status == 'Dikirim')
                                    <span class="badge bg-info text-white"><i class="fas fa-truck me-1"></i> Sedang Dikirim</span>
                                @elseif($r->status == 'Selesai')
                                    <span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> Diterima</span>
                                @else
                                    <span class="badge bg-danger text-white"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $r->tgl_estimasi ? \Carbon\Carbon::parse($r->tgl_estimasi)->format('d M Y') : 'Proses Verifikasi' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $r->id }}">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL DETAIL --}}
                        <div class="modal fade" id="modalDetail{{ $r->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fw-bold">Detail Request #{{ $r->id }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-4 text-center">
                                            <h6 class="text-muted mb-1 small uppercase fw-bold">Produk Yang Diminta</h6>
                                            {{-- FIX: Ganti nama_produk menjadi nama_barang --}}
                                            <h4 class="fw-bold text-dark">
                                                {{ $r->product->nama_barang ?? 'Produk Tidak Ditemukan' }}
                                                @if($r->variant)
                                                    <span class="d-block small text-muted fw-normal mt-2" style="font-size: 0.95rem;">Varian: {{ $r->variant->nama_varian }}</span>
                                                @endif
                                            </h4>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="p-3 bg-light rounded-3">
                                                    <small class="text-muted d-block">Jumlah Unit</small>
                                                    <span class="fw-bold">{{ $r->jumlah }} Unit</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-3 bg-light rounded-3">
                                                    <small class="text-muted d-block">Prioritas</small>
                                                    <span class="fw-bold text-danger">{{ $r->prioritas }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label class="small text-muted d-block mb-1">Catatan Tambahan:</label>
                                            <div class="p-3 border rounded-3 bg-white italic">
                                                "{{ $r->keterangan ?? 'Tidak ada catatan tambahan.' }}"
                                            </div>
                                        </div>
                                        @if($r->keterangan_admin)
                                        <div class="mt-3">
                                            <label class="small text-muted d-block mb-1 text-danger fw-bold">Catatan Admin / Rencana Pengiriman:</label>
                                            <div class="p-3 border border-warning rounded-3 bg-warning-subtle text-dark italic" style="background-color: #fff3cd;">
                                                "{{ $r->keterangan_admin }}"
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer border-0">
                                        <div class="w-100 p-2 bg-light rounded-3 text-center">
                                            <small class="text-muted">Riwayat Update: </small>
                                            <span class="small fw-bold">{{ $r->updated_at->format('d M Y, H:i') }} WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="opacity-50">
                                    <i class="fas fa-shipping-fast fa-3x mb-3"></i>
                                    <p class="mb-0">Belum ada riwayat permintaan stok dari cabang Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection