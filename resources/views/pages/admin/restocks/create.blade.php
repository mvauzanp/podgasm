@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-dark">
            <i class="fas fa-file-import me-2 text-primary"></i>
            Tambah Barang Masuk
        </h4>
        <a href="{{ route('admin.restocks.index') }}" class="btn btn-outline-secondary px-4 rounded-pill shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.restocks.store') }}" method="POST" id="restockForm">
        @csrf
        <div class="row g-4">

            {{-- INDUK DATA --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-info-circle me-1 text-primary"></i> Informasi Pengirim &amp; Supplier</h6>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label fw-bold text-muted small text-uppercase">Nama Supplier / Vendor <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_name" id="supplier_name" 
                                   class="form-control fw-semibold" placeholder="Masukkan nama vendor..." required value="{{ old('supplier_name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold text-muted small text-uppercase">Catatan Transaksi</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" 
                                      placeholder="Masukkan nomor surat jalan, nomor invoice supplier, atau catatan pengadaan lainnya...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Jenis Item</span>
                                <span class="fw-bold text-dark" id="summaryTotalItems">0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-bold">Total Pembelian</span>
                                <span class="fw-extrabold text-success fs-5" id="summaryTotalCost">Rp 0</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm">
                            <i class="fas fa-save me-1"></i> Simpan Barang Masuk
                        </button>

                    </div>
                </div>
            </div>

            {{-- DETAIL ITEM --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list me-1 text-primary"></i> Daftar Item Barang Masuk</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" id="btnAddItem">
                            <i class="fas fa-plus me-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="tableItems">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Pilih Produk</th>
                                        <th style="min-width: 150px;">Varian</th>
                                        <th style="width: 100px;">Jumlah</th>
                                        <th style="width: 160px;">Harga Beli (Satuan)</th>
                                        <th class="text-end" style="width: 150px;">Subtotal</th>
                                        <th class="text-center" style="width: 60px;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    {{-- Baris dinamis akan di-render di sini --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- EMPTY STATE IF NO ITEMS --}}
                        <div class="text-center py-5 d-none" id="emptyState">
                            <i class="fas fa-boxes-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted m-0 fw-semibold">Belum ada item ditambahkan.</p>
                            <p class="text-xs text-muted">Klik "Tambah Baris" untuk memasukkan item pengadaan.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- CLIENT-SIDE JAVASCRIPT --}}
<script>
    // Data produk dan variannya dari controller PHP
    const products = @json($products);
    let rowIndex = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Tambahkan baris pertama secara otomatis
        addRow();

        // Tombol Tambah Baris
        document.getElementById('btnAddItem').addEventListener('click', addRow);

        // Validasi Form sebelum submit
        document.getElementById('restockForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Peringatan: Silakan tambahkan minimal 1 item barang masuk!');
            }
        });
    });

    function addRow() {
        document.getElementById('emptyState').classList.add('d-none');
        document.getElementById('tableItems').classList.remove('d-none');

        const tbody = document.getElementById('tableBody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.id = `row_${rowIndex}`;

        // Build HTML
        row.innerHTML = `
            <td class="ps-4">
                <select name="items[${rowIndex}][product_id]" class="form-select product-select select2-enable" data-row="${rowIndex}" required>
                    <option value="" disabled selected>-- Pilih Produk --</option>
                    ${products.map(p => `<option value="${p.id}">${p.nama_barang}</option>`).join('')}
                </select>
            </td>
            <td>
                <select name="items[${rowIndex}][product_variant_id]" class="form-select variant-select" data-row="${rowIndex}" disabled>
                    <option value="">-- Tanpa Varian --</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity-input text-center" 
                       data-row="${rowIndex}" value="1" min="1" required>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted small" style="font-size: 0.8rem;">Rp</span>
                    <input type="text" class="form-control purchase-price-display" 
                           data-row="${rowIndex}" placeholder="0" required>
                    <input type="hidden" name="items[${rowIndex}][purchase_price]" class="purchase-price-input" 
                           data-row="${rowIndex}" value="0">
                </div>
            </td>
            <td class="text-end fw-bold text-dark subtotal-cell" id="subtotal_${rowIndex}">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btn-remove" data-row="${rowIndex}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);

        // Bind event listeners
        bindRowEvents(rowIndex);
        rowIndex++;
        updateTotals();
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function bindRowEvents(idx) {
        const rowNode = document.getElementById(`row_${idx}`);

        // Event: Ganti Produk -> Populasi Varian
        rowNode.querySelector('.product-select').addEventListener('change', function() {
            const prodId = this.value;
            const rowId = this.getAttribute('data-row');
            const variantSelect = rowNode.querySelector('.variant-select');

            const selectedProduct = products.find(p => p.id == prodId);
            
            // Bersihkan dropdown varian
            variantSelect.innerHTML = '';

            if (selectedProduct && selectedProduct.variants && selectedProduct.variants.length > 0) {
                variantSelect.disabled = false;
                variantSelect.required = true;
                variantSelect.innerHTML = `<option value="" disabled selected>-- Pilih Varian --</option>`;
                selectedProduct.variants.forEach(v => {
                    variantSelect.innerHTML += `<option value="${v.id}">${v.nama_varian} (Stok: ${v.stok_aktual})</option>`;
                });
            } else {
                variantSelect.disabled = true;
                variantSelect.required = false;
                variantSelect.innerHTML = `<option value="">-- Tanpa Varian --</option>`;
            }
            calculateSubtotal(rowId);
        });

        // Event: Ubah Qty & Harga Beli -> Update Subtotal
        rowNode.querySelector('.quantity-input').addEventListener('input', () => calculateSubtotal(idx));

        const displayInp = rowNode.querySelector('.purchase-price-display');
        const hiddenInp = rowNode.querySelector('.purchase-price-input');
        
        if (displayInp && hiddenInp) {
            displayInp.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value) {
                    hiddenInp.value = value;
                    this.value = formatNumber(value);
                } else {
                    hiddenInp.value = '';
                    this.value = '';
                }
                calculateSubtotal(idx);
            });
            
            // Format initial if needed
            if (hiddenInp.value) {
                displayInp.value = formatNumber(hiddenInp.value);
            }
        }

        // Event: Klik Hapus Baris
        rowNode.querySelector('.btn-remove').addEventListener('click', function() {
            rowNode.remove();
            checkEmptyState();
            updateTotals();
        });
    }

    function calculateSubtotal(idx) {
        const rowNode = document.getElementById(`row_${idx}`);
        if (!rowNode) return;

        const qty = parseFloat(rowNode.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(rowNode.querySelector('.purchase-price-input').value) || 0;
        const subtotal = qty * price;

        rowNode.querySelector('.subtotal-cell').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        updateTotals();
    }

    function updateTotals() {
        const rows = document.querySelectorAll('.item-row');
        let totalItems = rows.length;
        let totalCost = 0;

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.purchase-price-input').value) || 0;
            totalCost += (qty * price);
        });

        document.getElementById('summaryTotalItems').textContent = totalItems;
        document.getElementById('summaryTotalCost').textContent = `Rp ${totalCost.toLocaleString('id-ID')}`;
    }

    function checkEmptyState() {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 0) {
            document.getElementById('tableItems').classList.add('d-none');
            document.getElementById('emptyState').classList.remove('d-none');
        }
    }
</script>
@endsection
