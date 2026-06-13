@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid py-4">
    {{-- Header & Back Button --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.stock-requests.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Permintaan Stok
            </a>
            <h1 class="h3 text-gray-800 fw-bold mb-0">
                Detail Permintaan Stok B2B <span class="text-primary">{{ $order->invoice_number }}</span>
            </h1>
            <p class="text-muted small mb-0">Kelola kuantitas, verifikasi ketersediaan stok pusat, dan lakukan konfirmasi pengiriman cabang.</p>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2 rounded-pill fs-7 fw-bold shadow-sm">INVOICE B2B</span>
        </div>
    </div>

    {{-- Main Row --}}
    @if($order->status === 'pending_payment')
        <form action="{{ route('admin.stock-requests.update-order', $order->id) }}" method="POST">
            @csrf
    @endif

    <div class="row">
        {{-- KIRI: Status & Daftar Barang --}}
        <div class="col-lg-8">
            {{-- Status & Konfirmasi Aksi --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light py-3 border-0 rounded-top-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks me-2 text-primary"></i> Status & Aksi Persetujuan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="mb-1 text-muted small"><strong>Status Permintaan Saat Ini:</strong></p>
                            @if($order->status === 'pending_payment')
                                <span class="badge bg-warning text-dark px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                                    <i class="fas fa-hourglass-half me-1"></i> Menunggu Persetujuan Admin
                                </span>
                            @elseif($order->status === 'paid')
                                <span class="badge bg-info text-white px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> Disetujui & Sedang Diproses
                                </span>
                            @elseif($order->status === 'shipped')
                                <span class="badge bg-primary text-white px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                                    <i class="fas fa-shipping-fast me-1"></i> 📦 Sedang Dikirim
                                </span>
                            @elseif($order->status === 'completed')
                                <span class="badge bg-success text-white px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                                    <i class="fas fa-check-double me-1"></i> ✅ Selesai (Diterima di Cabang)
                                </span>
                            @else
                                <span class="badge bg-danger text-white px-4 py-2 rounded-pill fs-6 fw-bold shadow-sm">
                                    <i class="fas fa-times-circle me-1"></i> ❌ Dibatalkan / Ditolak
                                </span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @if(!in_array($order->status, ['completed', 'cancelled']))
                                @if($order->status !== 'pending_payment')
                                    <form action="{{ route('admin.stock-requests.update-order', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                @endif

                                <label class="form-label fw-bold d-block text-secondary small text-uppercase mb-2">Perbarui Status Permintaan:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($order->status === 'pending_payment')
                                        <button type="submit" name="status" value="paid" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="fas fa-check me-1"></i> Setujui & Proses Stok
                                        </button>
                                        <button type="submit" name="status" value="cancelled" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" onclick="return confirm('Apakah Anda yakin ingin menolak permintaan stok cabang ini?')">
                                            <i class="fas fa-times me-1"></i> Tolak Request
                                        </button>
                                    @elseif($order->status === 'paid')
                                        <button type="submit" name="status" value="shipped" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="fas fa-truck me-1"></i> Kirim Barang
                                        </button>
                                        <button type="submit" name="status" value="completed" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="fas fa-check-double me-1"></i> Selesaikan Request
                                        </button>
                                        <button type="submit" name="status" value="cancelled" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan dan merestore kembali stok pusat?')">
                                            <i class="fas fa-times me-1"></i> Batalkan Request
                                        </button>
                                    @elseif($order->status === 'shipped')
                                        <button type="submit" name="status" value="completed" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="fas fa-check-double me-1"></i> Selesaikan Request
                                        </button>
                                        <button type="submit" name="status" value="cancelled" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan dan merestore kembali stok pusat?')">
                                            <i class="fas fa-times me-1"></i> Batalkan Request
                                        </button>
                                    @endif
                                </div>

                                @if($order->status !== 'pending_payment')
                                    </form>
                                @endif
                            @else
                                <div class="alert alert-light border rounded-3 p-3 mb-0 text-center small text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Permintaan stok ini telah selesai dan tidak dapat diubah lagi.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daftar Produk --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-box me-2 text-primary"></i> Rincian Barang Permintaan</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary small fw-bold">Produk</th>
                                <th class="text-secondary small fw-bold text-center" style="width: 160px;">Stok Gudang Pusat</th>
                                <th class="text-secondary small fw-bold text-center" style="width: 140px;">Kuantitas Permintaan</th>
                                <th class="text-secondary small fw-bold text-end" style="width: 150px;">Harga B2B</th>
                                <th class="text-secondary small fw-bold text-end" style="width: 150px;">Subtotal</th>
                                @if($order->status === 'pending_payment')
                                <th class="text-secondary small fw-bold text-center pe-3" style="width: 70px;"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $centralStock = $item->variant ? $item->variant->stok_aktual : $item->product->stok_aktual;
                                    $isStockDeficient = $centralStock < $item->quantity;
                                @endphp
                                <tr class="item-row" id="row-{{ $item->id }}">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            @if($item->product->gambar)
                                                <img src="{{ asset('storage/' . $item->product->gambar) }}"
                                                     width="48" height="48" class="rounded-3 me-3 object-fit-cover shadow-sm border">
                                            @else
                                                <div class="bg-light rounded-3 me-3 border d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                                                    <i class="fas fa-box text-muted fa-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark fs-7">{{ $item->product->nama_barang }}</div>
                                                @if($item->variant)
                                                    <span class="badge bg-secondary text-white my-1" style="font-size: 0.7rem;">Varian: {{ $item->variant->nama_varian }}</span>
                                                @endif
                                                <small class="text-muted d-block" style="font-size: 0.72rem;">SKU: {{ $item->product_variant_id ? ($item->variant->kode_barang ?? $item->product->kode_barang ?? $item->product->id) : ($item->product->kode_barang ?? $item->product->id) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $isStockDeficient ? 'bg-danger text-white' : 'bg-success text-white' }} px-3 py-2 fw-bold rounded-pill">
                                            {{ $centralStock }} Unit
                                        </span>
                                        @if($isStockDeficient && $order->status === 'pending_payment')
                                            <div class="text-danger small mt-1 fw-bold" style="font-size: 0.7rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Stok Kurang!
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($order->status === 'pending_payment')
                                            <input type="number" name="items[{{ $item->id }}][quantity]"
                                                   value="{{ $item->quantity }}" class="form-control form-control-sm text-center fw-bold mx-auto border-primary rounded-3 quantity-input"
                                                   data-price="{{ $item->price }}" data-item-id="{{ $item->id }}"
                                                   min="1" max="{{ $centralStock }}" style="width: 85px;" required>
                                        @else
                                            <span class="badge bg-light text-dark border px-3 py-2 fw-bold rounded-3">{{ $item->quantity }} Unit</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-dark item-subtotal" data-item-id="{{ $item->id }}">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                    @if($order->status === 'pending_payment')
                                    <td class="text-center pe-3">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger rounded-3 px-2 delete-item-btn"
                                                data-item-id="{{ $item->id }}"
                                                title="Hapus barang ini dari permintaan">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        {{-- Hidden input untuk menandai item dihapus, disabled by default --}}
                                        <input type="hidden" name="delete_items[]" value="{{ $item->id }}" class="delete-flag-{{ $item->id }}" disabled>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAMBAH BARANG EKSTRA (hanya saat pending) --}}
            @if($order->status === 'pending_payment')
            @php
                $allProducts = \App\Models\Product::with('variants')->orderBy('nama_barang')->get();
            @endphp

            <style>
            .product-search-wrap { position: relative; }
            .product-search-results {
                position: absolute; z-index: 9999; width: 100%;
                background: #fff; border: 1px solid #dee2e6;
                border-radius: .5rem; box-shadow: 0 4px 16px rgba(0,0,0,.12);
                max-height: 220px; overflow-y: auto; display: none;
            }
            .product-search-results .result-item {
                padding: .5rem .75rem; cursor: pointer; font-size: .875rem;
                transition: background .15s;
            }
            .product-search-results .result-item:hover { background: #e8f4fd; color: #0d6efd; }
            .selected-product-badge {
                display: none; align-items: center; gap: .5rem;
                background: #e8f4fd; border: 1px solid #b6d9f7;
                border-radius: .5rem; padding: .35rem .7rem;
                font-size: .82rem; font-weight: 600; color: #0d6efd;
                margin-top: .35rem;
            }
            .selected-product-badge .clear-product {
                cursor: pointer; color: #6c757d; font-weight: 900;
                margin-left: auto; padding: 0 .2rem;
            }
            .selected-product-badge .clear-product:hover { color: #dc3545; }
            </style>

            <script>
            window.PRODUCT_DATA = {!! $allProducts->map(fn($p) => [
                'id'       => $p->id,
                'nama'     => $p->nama_barang,
                'stok'     => $p->stok_aktual,
                'variants' => $p->variants->map(fn($v) => [
                    'id'   => $v->id,
                    'nama' => $v->nama_varian,
                    'stok' => $v->stok_aktual,
                ])->values()->all(),
            ])->values()->toJson() !!};
            </script>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #e8f4fd, #f0f9ff);">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Barang Ekstra
                        </h5>
                        <small class="text-muted">Barang tambahan di luar permintaan asli cabang</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="extra-items-container">
                        {{-- Baris pertama di-render langsung dari Blade --}}
                        <div class="extra-item-row row g-2 align-items-start mb-3" data-row="0">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold text-secondary mb-1">Cari Produk</label>
                                <div class="product-search-wrap">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light" style="border:1px solid #dee2e6; border-right:0;">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text"
                                               class="form-control form-control-sm extra-search-input"
                                               placeholder="Ketik nama produk..."
                                               autocomplete="off"
                                               style="border-left:0;">
                                    </div>
                                    <input type="hidden" name="extra_items[0][product_id]" class="extra-product-id">
                                    <div class="product-search-results"></div>
                                    <div class="selected-product-badge">
                                        <i class="fas fa-check-circle text-primary"></i>
                                        <span class="selected-name fw-bold"></span>
                                        <span class="clear-product" title="Ganti produk">&#x2715;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 extra-variant-col">
                                <label class="form-label small fw-semibold text-secondary mb-1">Varian <span class="text-muted fw-normal">(jika ada)</span></label>
                                <select name="extra_items[0][variant_id]" class="form-select form-select-sm extra-variant-select" disabled>
                                    <option value="">— Tanpa Varian —</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-secondary mb-1">Jumlah</label>
                                <input type="number" name="extra_items[0][quantity]" value="0" min="1" max="0"
                                       class="form-control form-control-sm text-center fw-bold extra-qty-input">
                            </div>
                            <div class="col-md-2 pt-4">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 w-100 remove-extra-row" disabled>
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="btn-add-extra-row" class="btn btn-sm btn-primary rounded-3">
                        <i class="fas fa-plus me-1"></i> + Tambah Baris
                    </button>
                    <p class="text-muted small mb-0 mt-3">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Harga dihitung otomatis (tarif B2B). Produk sama dengan invoice akan dijumlah kuantitasnya.
                    </p>
                </div>
            </div>
            @endif
        </div>

        {{-- KANAN: Informasi Cabang & Summary --}}
        <div class="col-lg-4">
            {{-- Informasi Cabang --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light py-3 border-0 rounded-top-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-store me-2 text-primary"></i> Cabang Pemohon</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-pill d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                            <i class="fas fa-house-chimney fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $order->user->name ?? 'Unknown Branch' }}</div>
                            <small class="text-muted">ID Cabang: #{{ $order->user->id }}</small>
                        </div>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <table class="table table-borderless table-sm mb-0 small">
                        <tr>
                            <td class="text-muted" style="width: 35%">Email</td>
                            <td class="fw-semibold text-dark"><a href="mailto:{{ $order->email }}" class="text-decoration-none">{{ $order->email }}</a></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Telepon</td>
                            <td class="fw-semibold text-dark">{{ $order->no_telp }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Penerima</td>
                            <td class="fw-semibold text-dark">{{ $order->nama_penerima }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat Kirim</td>
                            <td class="text-dark">{{ $order->alamat_pengiriman }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Ringkasan Pembayaran/Nilai --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light py-3 border-0 rounded-top-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt me-2 text-primary"></i> Ringkasan Nilai Request</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Jumlah Barang</span>
                        <span class="fw-bold text-dark" id="total-quantity-display">{{ $order->items->sum('quantity') }} Unit</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Banyak Item</span>
                        <span class="fw-bold text-dark">{{ $order->items->count() }} Jenis</span>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6">Total Nilai Stok:</span>
                        <h4 class="fw-bold text-success mb-0" id="total-nominal-display">
                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Timeline Status --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light py-3 border-0 rounded-top-4">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left me-2 text-primary"></i> Riwayat Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-simple">
                        <div class="timeline-item-simple pb-3 border-start border-2 border-primary ps-3 position-relative">
                            <div class="timeline-badge-simple bg-primary position-absolute rounded-pill" style="width: 12px; height: 12px; left: -7px; top: 4px;"></div>
                            <div class="fw-bold text-dark small">Permintaan Stok Diajukan</div>
                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        @if($order->status !== 'pending_payment')
                            <div class="timeline-item-simple pb-3 border-start border-2 border-primary ps-3 position-relative">
                                <div class="timeline-badge-simple bg-primary position-absolute rounded-pill" style="width: 12px; height: 12px; left: -7px; top: 4px;"></div>
                                <div class="fw-bold text-dark small">Persetujuan & Pemotongan Stok</div>
                                <small class="text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</small>
                            </div>
                        @endif
                        @if(in_array($order->status, ['shipped', 'completed']))
                            <div class="timeline-item-simple pb-3 border-start border-2 border-primary ps-3 position-relative">
                                <div class="timeline-badge-simple bg-primary position-absolute rounded-pill" style="width: 12px; height: 12px; left: -7px; top: 4px;"></div>
                                <div class="fw-bold text-dark small">Barang Sedang Dikirim</div>
                                <small class="text-muted">Status: {{ ucfirst($order->status) }}</small>
                            </div>
                        @endif
                        @if($order->status === 'completed')
                            <div class="timeline-item-simple ps-3 position-relative">
                                <div class="timeline-badge-simple bg-success position-absolute rounded-pill" style="width: 12px; height: 12px; left: -7px; top: 4px;"></div>
                                <div class="fw-bold text-success small">Pesanan Selesai / Diterima</div>
                                <small class="text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</small>
                            </div>
                        @endif
                        @if($order->status === 'cancelled')
                            <div class="timeline-item-simple ps-3 position-relative">
                                <div class="timeline-badge-simple bg-danger position-absolute rounded-pill" style="width: 12px; height: 12px; left: -7px; top: 4px;"></div>
                                <div class="fw-bold text-danger small">Permintaan Dibatalkan / Ditolak</div>
                                <small class="text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($order->status === 'pending_payment')
        </form>
    @endif
</div>

<style>
.timeline-simple { margin-left: 10px; }
.timeline-item-simple:last-child { border-start: 0 !important; }
</style>

@if($order->status === 'pending_payment')
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ── Hitung ulang subtotal item invoice ── */
    var qtyInputs   = document.querySelectorAll('.quantity-input');
    var dispQty     = document.getElementById('total-quantity-display');
    var dispNominal = document.getElementById('total-nominal-display');

    function fmt(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }

    function recalc() {
        var tQty = 0, tVal = 0;
        qtyInputs.forEach(function(el) {
            if (el.disabled) {
                /* Item ditandai hapus: set subtotalnya ke 0 */
                var c = document.querySelector('.item-subtotal[data-item-id="' + el.getAttribute('data-item-id') + '"]');
                if (c) c.textContent = fmt(0);
                return;
            }
            var q = parseInt(el.value) || 0;
            var p = parseFloat(el.getAttribute('data-price')) || 0;
            tQty += q; tVal += q * p;
            var c = document.querySelector('.item-subtotal[data-item-id="' + el.getAttribute('data-item-id') + '"]');
            if (c) c.textContent = fmt(q * p);
        });
        if (dispQty)     dispQty.textContent    = tQty + ' Unit';
        if (dispNominal) dispNominal.textContent = fmt(tVal);
    }
     qtyInputs.forEach(function(el) {
         el.addEventListener('input', function() {
             var max = parseInt(el.getAttribute('max')) || 999999;
             var val = parseInt(el.value);
             if (!isNaN(val)) {
                 if (val > max) {
                     el.value = max;
                 } else if (val < 1) {
                     el.value = 1;
                 }
             }
             recalc();
         });
         el.addEventListener('change', recalc);
     });

     /* ── Live Search extra items ── */
     var ALL = window.PRODUCT_DATA || [];
     var wrap = document.getElementById('extra-items-container');
     var rowN = 0;

     function initRow(row) {
         var inp  = row.querySelector('.extra-search-input');
         var res  = row.querySelector('.product-search-results');
         var pid  = row.querySelector('.extra-product-id');
         var bdg  = row.querySelector('.selected-product-badge');
         var bnm  = row.querySelector('.selected-name');
         var clr  = row.querySelector('.clear-product');
         var vcol = row.querySelector('.extra-variant-col');
         var del  = row.querySelector('.remove-extra-row');
         var qtyInp = row.querySelector('.extra-qty-input');
         if (!inp) return;

         inp.addEventListener('input', function() {
             var q = inp.value.trim().toLowerCase();
             res.innerHTML = '';
             if (!q) { res.style.display = 'none'; return; }
             var hits = ALL.filter(function(p) { return p.nama.toLowerCase().indexOf(q) >= 0; }).slice(0, 10);
             if (!hits.length) {
                 res.innerHTML = '<div class="result-item text-muted">Produk tidak ditemukan</div>';
                 res.style.display = 'block'; return;
             }
             hits.forEach(function(p) {
                 var d = document.createElement('div');
                 d.className = 'result-item';
                 d.innerHTML = '<i class="fas fa-box-open me-2 text-primary opacity-50"></i>' + p.nama +
                     (p.variants.length ? ' <span class="badge bg-secondary ms-1" style="font-size:.68rem">' + p.variants.length + ' varian</span>' : '');
                 d.addEventListener('mousedown', function(e) { e.preventDefault(); pick(p); });
                 res.appendChild(d);
             });
             res.style.display = 'block';
         });

         inp.addEventListener('blur', function() { setTimeout(function() { res.style.display = 'none'; }, 200); });

         function pick(p) {
             inp.closest('.input-group').style.display = 'none';
             res.style.display = 'none';
             pid.value = p.id;
             bnm.textContent = p.nama;
             bdg.style.display = 'flex';
             var vs = vcol.querySelector('.extra-variant-select');
             vs.innerHTML = '';
             if (!p.variants.length) {
                 vs.innerHTML = '<option value="">— Tanpa Varian —</option>';
                 vs.disabled = true;
                 qtyInp.setAttribute('max', p.stok);
                 if (parseInt(qtyInp.value) > p.stok || parseInt(qtyInp.value) === 0) {
                     qtyInp.value = p.stok > 0 ? 1 : 0;
                 }
             } else {
                 vs.innerHTML = '<option value="">— Pilih Varian —</option>';
                 p.variants.forEach(function(v) {
                     var o = document.createElement('option');
                     o.value = v.id;
                     o.textContent = v.nama + ' (Stok: ' + v.stok + ')';
                     vs.appendChild(o);
                 });
                 vs.disabled = false;
                 qtyInp.setAttribute('max', 0);
                 qtyInp.value = 0;
             }
         }

         var vs = vcol.querySelector('.extra-variant-select');
         vs.addEventListener('change', function() {
             var val = vs.value;
             if (!val) {
                 qtyInp.setAttribute('max', 0);
                 qtyInp.value = 0;
                 return;
             }
             var product_id = parseInt(pid.value);
             var p = ALL.find(function(item) { return item.id === product_id; });
             if (p) {
                 var v = p.variants.find(function(varitem) { return varitem.id == val; });
                 if (v) {
                     qtyInp.setAttribute('max', v.stok);
                     var currentVal = parseInt(qtyInp.value);
                     if (isNaN(currentVal) || currentVal > v.stok || currentVal === 0) {
                         qtyInp.value = v.stok > 0 ? 1 : 0;
                     }
                 }
             }
         });

         qtyInp.addEventListener('input', function() {
             var max = parseInt(qtyInp.getAttribute('max')) || 0;
             var val = parseInt(qtyInp.value);
             if (!isNaN(val)) {
                 if (val > max) {
                     qtyInp.value = max;
                 } else if (val < 1 && max > 0) {
                     qtyInp.value = 1;
                 }
             }
         });

         clr.addEventListener('click', function() {
             pid.value = ''; bdg.style.display = 'none';
             inp.closest('.input-group').style.display = '';
             inp.value = '';
             var vs = vcol.querySelector('.extra-variant-select');
             vs.innerHTML = '<option value="">— Tanpa Varian —</option>'; vs.disabled = true;
             qtyInp.setAttribute('max', 0);
             qtyInp.value = 0;
             inp.focus();
         });

         del.addEventListener('click', function() { row.remove(); refreshDel(); });
     }

     function refreshDel() {
         var rows = wrap.querySelectorAll('.extra-item-row');
         rows.forEach(function(r) {
             var b = r.querySelector('.remove-extra-row');
             if (b) b.disabled = (rows.length <= 1);
         });
     }

     function addRow() {
         rowN++;
         var i = rowN;
         var r = document.createElement('div');
         r.className = 'extra-item-row row g-2 align-items-start mb-3';
         r.setAttribute('data-row', i);
         r.innerHTML =
             '<div class="col-md-5"><label class="form-label small fw-semibold text-secondary mb-1">Cari Produk</label>' +
             '<div class="product-search-wrap">' +
             '<div class="input-group input-group-sm">' +
             '<span class="input-group-text bg-light" style="border:1px solid #dee2e6;border-right:0;"><i class="fas fa-search text-muted"></i></span>' +
             '<input type="text" class="form-control form-control-sm extra-search-input" placeholder="Ketik nama produk..." autocomplete="off" style="border-left:0;">' +
             '</div>' +
             '<input type="hidden" name="extra_items[' + i + '][product_id]" class="extra-product-id">' +
             '<div class="product-search-results"></div>' +
             '<div class="selected-product-badge"><i class="fas fa-check-circle text-primary"></i><span class="selected-name fw-bold"></span><span class="clear-product" title="Ganti">&#x2715;</span></div>' +
             '</div></div>' +
             '<div class="col-md-3 extra-variant-col"><label class="form-label small fw-semibold text-secondary mb-1">Varian <span class="text-muted fw-normal">(jika ada)</span></label>' +
             '<select name="extra_items[' + i + '][variant_id]" class="form-select form-select-sm extra-variant-select" disabled><option value="">— Tanpa Varian —</option></select></div>' +
             '<div class="col-md-2"><label class="form-label small fw-semibold text-secondary mb-1">Jumlah</label>' +
             '<input type="number" name="extra_items[' + i + '][quantity]" value="0" min="1" max="0" class="form-control form-control-sm text-center fw-bold extra-qty-input"></div>' +
             '<div class="col-md-2 pt-4"><button type="button" class="btn btn-sm btn-outline-danger rounded-3 w-100 remove-extra-row"><i class="fas fa-trash me-1"></i> Hapus</button></div>';
         wrap.appendChild(r);
         initRow(r);
         refreshDel();
         r.querySelector('.extra-search-input').focus();
     }

     /* init baris pertama yang sudah ada di DOM */
     var first = wrap ? wrap.querySelector('.extra-item-row') : null;
     if (first) initRow(first);
     refreshDel();

    var addBtn = document.getElementById('btn-add-extra-row');
    if (addBtn) addBtn.addEventListener('click', addRow);

    /* ── Hapus item dari rincian invoice ── */
    document.querySelectorAll('.delete-item-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var itemId = btn.getAttribute('data-item-id');
            var row    = document.getElementById('row-' + itemId);
            var flag   = document.querySelector('.delete-flag-' + itemId);

            var isMarked = row.classList.contains('table-danger');

            if (isMarked) {
                /* UNDO: batalkan penghapusan */
                row.classList.remove('table-danger');
                row.style.opacity = '1';
                row.querySelectorAll('td').forEach(function(td) { td.style.textDecoration = ''; });
                if (flag) flag.disabled = true;
                btn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                btn.title = 'Hapus barang ini dari permintaan';
                btn.classList.replace('btn-danger', 'btn-outline-danger');
                var qtyInp = row.querySelector('.quantity-input');
                if (qtyInp) { qtyInp.disabled = false; qtyInp.required = true; }
            } else {
                /* Tandai untuk dihapus */
                row.classList.add('table-danger');
                row.style.opacity = '0.55';
                row.querySelectorAll('td').forEach(function(td) { td.style.textDecoration = 'line-through'; });
                if (flag) flag.disabled = false;
                btn.innerHTML = '<i class="fas fa-undo"></i>';
                btn.title = 'Batalkan penghapusan';
                btn.classList.replace('btn-outline-danger', 'btn-danger');
                var qtyInp = row.querySelector('.quantity-input');
                if (qtyInp) { qtyInp.disabled = true; qtyInp.required = false; }
            }

            /* Update total setelah hapus/undo */
            recalc();
        });
    });

});
</script>
@endif
@endsection
