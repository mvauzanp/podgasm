@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 fw-bold">Persetujuan Stok Cabang</h1>
            <p class="text-muted small">Kelola permintaan barang masuk dari cabang Podgasm (Invoice B2B & Request Manual).</p>
        </div>
    </div>

    {{-- Notifikasi Sukses/Gagal --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TAB NAVIGATION --}}
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders" type="button" role="tab" aria-controls="pills-orders" aria-selected="true">
                <i class="fas fa-file-invoice me-2"></i> Permintaan via Invoice (B2B Checkout)
            </button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link rounded-pill px-4" id="pills-requests-tab" data-bs-toggle="pill" data-bs-target="#pills-requests" type="button" role="tab" aria-controls="pills-requests" aria-selected="false">
                <i class="fas fa-list me-2"></i> Permintaan Manual (Satuan)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        {{-- ================= TAB 1: B2B INVOICE CHECKOUT ================= --}}
        <div class="tab-pane fade show active" id="pills-orders" role="tabpanel" aria-labelledby="pills-orders-tab">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small fw-bold">INVOICE & TANGGAL</th>
                                    <th class="text-secondary small fw-bold">CABANG</th>
                                    <th class="text-secondary small fw-bold text-center">JUMLAH BARANG</th>
                                    <th class="text-secondary small fw-bold text-end">TOTAL NILAI</th>
                                    <th class="text-secondary small fw-bold text-center">STATUS</th>
                                    <th class="text-end pe-4 text-secondary small fw-bold">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchOrders as $order)
                                <tr style="cursor: pointer; transition: all 0.2s;" onclick="window.location='{{ route('admin.stock-requests.show-order', $order->id) }}'">
                                    <td class="ps-4">
                                        <span class="fw-bold text-primary">{{ $order->invoice_number }}</span>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <i class="far fa-clock me-1"></i>{{ $order->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $order->user->name ?? 'Unknown Branch' }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $order->alamat_pengiriman }}">
                                            <i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $order->alamat_pengiriman }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                                            {{ $order->items->sum('quantity') }} PCS
                                        </span>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">{{ $order->items->count() }} Jenis Barang</small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if($order->status == 'pending_payment')
                                            <span class="badge bg-warning text-dark px-3 rounded-pill">Menunggu Persetujuan</span>
                                        @elseif($order->status == 'paid')
                                            <span class="badge bg-info px-3 rounded-pill text-white">Disetujui & Diproses</span>
                                        @elseif($order->status == 'shipped')
                                            <span class="badge bg-primary px-3 rounded-pill">📦 Dikirim</span>
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success px-3 rounded-pill">✅ Selesai</span>
                                        @else
                                            <span class="badge bg-danger px-3 rounded-pill">❌ Dibatalkan / Tolak</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation();">
                                        <a href="{{ route('admin.stock-requests.show-order', $order->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                            Proses Request
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="opacity-25">
                                            <i class="fas fa-inbox fa-4x mb-3"></i>
                                            <h5 class="fw-bold">Belum ada request checkout masuk!</h5>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination Tab 1 --}}
            <div class="d-flex justify-content-center mt-4">
                {!! $branchOrders->appends(['requests_page' => $requests->currentPage()])->links() !!}
            </div>
        </div>

        {{-- ================= TAB 2: MANUAL REQUEST (SATUAN) ================= --}}
        <div class="tab-pane fade" id="pills-requests" role="tabpanel" aria-labelledby="pills-requests-tab">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small fw-bold">CABANG</th>
                                    <th class="text-secondary small fw-bold">PRODUK</th>
                                    <th class="text-secondary small fw-bold">JUMLAH</th>
                                    <th class="text-secondary small fw-bold">PRIORITAS</th>
                                    <th class="text-secondary small fw-bold">STATUS</th>
                                    <th class="text-end pe-4 text-secondary small fw-bold">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $r->user->name ?? 'Unknown Branch' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <i class="far fa-clock me-1"></i>{{ $r->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">
                                            {{ $r->product->nama_barang ?? 'Produk Dihapus' }}
                                            @if($r->variant)
                                                <span class="d-block small text-muted fw-normal mt-1">Varian: {{ $r->variant->nama_varian }}</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-3">{{ $r->jumlah }} Unit</span>
                                    </td>
                                    <td>
                                        @php
                                            $prioClass = [
                                                'Urgent' => 'bg-danger',
                                                'Normal' => 'bg-info',
                                                'Low' => 'bg-secondary'
                                            ][$r->prioritas] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $prioClass }}">{{ $r->prioritas }}</span>
                                    </td>
                                    <td>
                                        @if($r->status == 'Pending')
                                            <span class="badge bg-warning text-dark px-3 rounded-pill">Pending</span>
                                        @elseif($r->status == 'Dikirim')
                                            <span class="badge bg-primary px-3 rounded-pill">📦 Dikirim</span>
                                        @elseif($r->status == 'Selesai')
                                            <span class="badge bg-success px-3 rounded-pill">✅ Selesai</span>
                                        @else
                                            <span class="badge bg-danger px-3 rounded-pill">❌ Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($r->status == 'Pending')
                                            {{-- TOMBOL PEMICU MODAL --}}
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalApprove{{ $r->id }}">
                                                Approve
                                            </button>

                                            <form action="{{ route('admin.stock-requests.reject', $r->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                        onclick="return confirm('Beneran mau ditolak bzir?')">
                                                    Reject
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-light disabled rounded-pill">Sudah Diproses</button>
                                        @endif
                                    </td>
                                </tr>

                                {{-- MODAL APPROVE PER BARIS --}}
                                <div class="modal fade" id="modalApprove{{ $r->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $r->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <form action="{{ route('admin.stock-requests.approve', $r->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-success text-white border-0 py-3">
                                                    <h5 class="modal-title fw-bold" id="modalLabel{{ $r->id }}">Proses Pengiriman</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="text-center mb-4">
                                                        <i class="fas fa-shipping-fast fa-3x text-success mb-3"></i>
                                                        <h5 class="mb-0 fw-bold">
                                                            {{ $r->product->nama_barang ?? '' }}
                                                            @if($r->variant)
                                                                <span class="text-muted small">({{ $r->variant->nama_varian }})</span>
                                                            @endif
                                                        </h5>
                                                        <p class="text-muted small mb-1 mt-1">Diajukan sebanyak {{ $r->jumlah }} unit untuk cabang {{ $r->user->name ?? '' }}</p>
                                                        @if($r->keterangan)
                                                            <div class="alert alert-info py-2 px-3 small mb-0 mt-3 text-start border-0 rounded-3" style="font-size: 0.85rem; border-left: 4px solid #0dcaf0 !important; background-color: #f0fafd; color: #0c5460;">
                                                                <strong>Catatan Cabang:</strong> "{{ $r->keterangan }}"
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    {{-- Penyesuaian Jumlah --}}
                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-bold">Jumlah yang Disetujui (Unit) <span class="text-danger">*</span></label>
                                                        <input type="number" name="jumlah" class="form-control form-control-lg border-success" 
                                                               value="{{ $r->jumlah }}" min="1" required>
                                                        <div class="form-text mt-1 text-muted small">
                                                            Anda dapat menambah atau mengurangi kuantitas permintaan di sini.
                                                        </div>
                                                    </div>

                                                    {{-- Catatan Penyesuaian --}}
                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-bold">Catatan/Alasan Penyesuaian (Opsional)</label>
                                                        <textarea name="keterangan_admin" class="form-control border-success" rows="3" 
                                                                  placeholder="Tulis alasan jika jumlah disesuaikan..."></textarea>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label class="form-label fw-bold">Estimasi Barang Sampai <span class="text-danger">*</span></label>
                                                        <input type="date" name="tgl_estimasi" class="form-control form-control-lg border-success" 
                                                               required min="{{ date('Y-m-d') }}">
                                                        <div class="form-text mt-2 text-info small">
                                                            <i class="fas fa-info-circle me-1"></i> Tanggal ini akan tampil di dashboard Cabang.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 bg-light rounded-bottom-4 py-3">
                                                    <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm">
                                                        Setujui & Kirim
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="opacity-25">
                                            <i class="fas fa-inbox fa-4x mb-3"></i>
                                            <h5 class="fw-bold">Belum ada request manual masuk!</h5>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination Tab 2 --}}
            <div class="d-flex justify-content-center mt-4">
                {!! $requests->appends(['orders_page' => $branchOrders->currentPage()])->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection