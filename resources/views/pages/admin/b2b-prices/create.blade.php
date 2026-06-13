@extends('layouts.admin')

@section('content_admin')
<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-wrapper.form-select-lg .ts-control {
        padding: 0.75rem 1.25rem !important;
        font-size: 1.05rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #dee2e6 !important;
        background-color: #fff !important;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        border: 1px solid #eef1f5 !important;
        padding: 0.25rem !important;
    }
    .ts-dropdown .option {
        padding: 0.6rem 1rem !important;
        border-radius: 0.25rem !important;
    }
    .ts-dropdown .active {
        background-color: #2563eb !important;
        color: #fff !important;
    }
    .no-results {
        padding: 0.6rem 1rem !important;
        color: #6b7280;
    }
</style>

<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('admin.b2b-prices.index') }}" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
        </a>
        <h1 class="h3 text-gray-800 fw-bold mt-2">Tambah Setting Harga B2B</h1>
        <p class="text-muted small">Buat potongan harga khusus grosir untuk produk atau varian tertentu.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.b2b-prices.store') }}" method="POST">
                @csrf

                {{-- Pilih Produk --}}
                <div class="mb-4">
                    <label for="product_id" class="form-label fw-bold">Pilih Produk <span class="text-danger">*</span></label>
                    <select name="product_id" id="product_id" class="form-select form-select-lg" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-variants="{{ json_encode($p->variants) }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_barang }} (Eceran: Rp {{ number_format($p->harga_jual, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Varian (Dinamis via JS) --}}
                <div class="mb-4" id="variant_container" style="display: none;">
                    <label for="product_variant_id" class="form-label fw-bold">Pilih Varian (Opsional)</label>
                    <select name="product_variant_id" id="product_variant_id" class="form-select form-select-lg">
                        <option value="">Semua Varian</option>
                    </select>
                    <div class="form-text text-muted">Biarkan "Semua Varian" jika diskon berlaku untuk seluruh varian produk ini.</div>
                </div>

                {{-- Minimal Pembelian --}}
                <div class="mb-4">
                    <label for="min_qty" class="form-label fw-bold">Minimal Pembelian (Unit) <span class="text-danger">*</span></label>
                    <input type="number" name="min_qty" id="min_qty" class="form-control form-control-lg" value="{{ old('min_qty', 1) }}" min="1" placeholder="Contoh: 6" required>
                    <div class="form-text text-muted">Jumlah minimal item ini di keranjang belanja untuk mengaktifkan diskon.</div>
                </div>

                {{-- Jenis Potongan --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Jenis Potongan Harga <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="discount_type" id="type_nominal" value="nominal" {{ old('discount_type', 'nominal') === 'nominal' ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="type_nominal">
                                Nominal Rupiah (Rp)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="discount_type" id="type_percentage" value="percentage" {{ old('discount_type') === 'percentage' ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="type_percentage">
                                Persentase (%)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Nilai Potongan --}}
                <div class="mb-5">
                    <label for="discount_value" class="form-label fw-bold" id="value_label">Nilai Potongan (Rp) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text" id="value_addon">Rp</span>
                        <input type="number" name="discount_value" id="discount_value" class="form-control form-control-lg" value="{{ old('discount_value') }}" min="0" step="any" placeholder="Contoh: 5000" required>
                    </div>
                    <div class="form-text text-muted" id="value_help">Masukkan jumlah nominal potongan harga per unit barang.</div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold">
                        Simpan Setting
                    </button>
                    <a href="{{ route('admin.b2b-prices.index') }}" class="btn btn-light rounded-pill px-4 py-3 fw-bold">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product_id');
        const variantContainer = document.getElementById('variant_container');
        const variantSelect = document.getElementById('product_variant_id');
        const typeNominal = document.getElementById('type_nominal');
        const typePercentage = document.getElementById('type_percentage');
        const valueLabel = document.getElementById('value_label');
        const valueAddon = document.getElementById('value_addon');
        const valueHelp = document.getElementById('value_help');
        const valueInput = document.getElementById('discount_value');

        // Inisialisasi Tom Select
        const tomSelectInstance = new TomSelect(productSelect, {
            create: false,
            placeholder: "-- Pilih Produk --",
            controlInput: '<input autofocus>',
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results">Produk tidak ditemukan "' + escape(data.query) + '"</div>';
                }
            }
        });

        // Handler untuk perubahan produk
        function handleProductChange() {
            const selectedVal = productSelect.value;
            if (!selectedVal) {
                variantContainer.style.display = 'none';
                return;
            }

            const selectedOption = productSelect.querySelector('option[value="' + selectedVal + '"]');
            if (!selectedOption) {
                variantContainer.style.display = 'none';
                return;
            }

            const variantsData = selectedOption.getAttribute('data-variants');
            const variants = variantsData ? JSON.parse(variantsData) : [];

            if (variants.length > 0) {
                variantContainer.style.display = 'block';
                // Reset select varian
                variantSelect.innerHTML = '<option value="">Semua Varian</option>';
                
                // Tambahkan varian ke select
                variants.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.id;
                    option.textContent = `${v.nama_varian} (Eceran: Rp ${v.harga_jual ? parseInt(v.harga_jual).toLocaleString('id-ID') : 'Sama dengan induk'})`;
                    // Old selection handling
                    if (v.id == "{{ old('product_variant_id') }}") {
                        option.selected = true;
                    }
                    variantSelect.appendChild(option);
                });
            } else {
                variantContainer.style.display = 'none';
                variantSelect.innerHTML = '<option value="">Semua Varian</option>';
            }
        }

        productSelect.addEventListener('change', handleProductChange);
        // Trigger saat load pertama kali (jika ada value old)
        if (productSelect.value) {
            handleProductChange();
        }

        // Handler untuk tipe potongan
        function updateDiscountTypeUI() {
            if (typePercentage.checked) {
                valueLabel.textContent = 'Persentase Potongan (%) *';
                valueAddon.textContent = '%';
                valueHelp.textContent = 'Masukkan nilai persentase diskon (contoh: 10 untuk 10%). Maksimal 100%.';
                valueInput.placeholder = 'Contoh: 10';
            } else {
                valueLabel.textContent = 'Nilai Potongan (Rp) *';
                valueAddon.textContent = 'Rp';
                valueHelp.textContent = 'Masukkan jumlah nominal potongan harga per unit barang.';
                valueInput.placeholder = 'Contoh: 5000';
            }
        }

        typeNominal.addEventListener('change', updateDiscountTypeUI);
        typePercentage.addEventListener('change', updateDiscountTypeUI);
        // Trigger saat load pertama kali
        updateDiscountTypeUI();
    });
</script>
