@extends('layouts.admin')

@section('content_admin')
<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-wrapper {
        width: 100% !important;
    }
    .ts-wrapper .ts-control {
        padding: 0.75rem 1.25rem !important;
        font-size: 1.05rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #dee2e6 !important;
        background-color: #fff !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25) !important;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        border: 1px solid #eef1f5 !important;
        padding: 0.25rem !important;
        background-color: #fff !important;
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
        <h1 class="h3 text-gray-800 fw-bold mt-2">Edit Setting Harga B2B</h1>
        <p class="text-muted small">Perbarui potongan harga khusus grosir untuk produk atau varian tertentu.</p>
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
            <form action="{{ route('admin.b2b-prices.update', $setting->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Pilih Produk --}}
                <div class="mb-4">
                    <label for="product_id" class="form-label fw-bold">Pilih Produk <span class="text-danger">*</span></label>
                    <select name="product_id" id="product_id" class="form-select form-select-lg" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-variants="{{ json_encode($p->variants) }}" {{ old('product_id', $setting->product_id) == $p->id ? 'selected' : '' }}>
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

                <div class="row">
                    {{-- TIER 1 --}}
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-tags me-2"></i>Tingkat Potongan 1 (Dasar)</h5>
                                
                                {{-- Minimal Pembelian Tingkat 1 --}}
                                <div class="mb-3">
                                    <label for="min_qty" class="form-label fw-bold">Minimal Pembelian (Unit) <span class="text-danger">*</span></label>
                                    <input type="number" name="min_qty" id="min_qty" class="form-control form-control-lg" value="{{ old('min_qty', $allSettings[0]->min_qty ?? $setting->min_qty) }}" min="1" placeholder="Contoh: 1" required>
                                    <div class="form-text text-muted">Jumlah minimal item ini di keranjang belanja untuk mengaktifkan potongan pertama.</div>
                                </div>

                                {{-- Jenis Potongan Tingkat 1 --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jenis Potongan Harga <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="discount_type" id="type_nominal" value="nominal" {{ old('discount_type', $allSettings[0]->discount_type ?? $setting->discount_type) === 'nominal' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="type_nominal">
                                                Nominal Rupiah (Rp)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="discount_type" id="type_percentage" value="percentage" {{ old('discount_type', $allSettings[0]->discount_type ?? $setting->discount_type) === 'percentage' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="type_percentage">
                                                Persentase (%)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nilai Potongan Tingkat 1 --}}
                                <div class="mb-3">
                                    <label for="discount_value_display" class="form-label fw-bold" id="value_label">Nilai Potongan (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" id="value_addon">Rp</span>
                                        <input type="text" id="discount_value_display" class="form-control form-control-lg" value="{{ old('discount_value', $allSettings[0]->discount_value ?? $setting->discount_value) }}" placeholder="Contoh: 5.000" required>
                                        <input type="hidden" name="discount_value" id="discount_value" value="{{ old('discount_value', $allSettings[0]->discount_value ?? $setting->discount_value) }}" required>
                                    </div>
                                    <div class="form-text text-muted" id="value_help">Masukkan jumlah nominal potongan harga per unit barang.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TIER 2 --}}
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="form-check form-switch mb-3 p-0 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-percentage me-2"></i>Tingkat Potongan 2 (Opsional)</h5>
                                    <input class="form-check-input fs-4 m-0" type="checkbox" name="enable_tier_2" id="enable_tier_2" value="1" {{ old('enable_tier_2', $allSettings->count() > 1) ? 'checked' : '' }} onchange="toggleTier2Section()">
                                </div>
                                <p class="text-muted small mb-4">Aktifkan untuk memberikan potongan tambahan bagi pembelian dalam jumlah yang lebih besar (misal: potongan bertingkat).</p>

                                <div id="tier_2_fields" style="display: none;">
                                    {{-- Minimal Pembelian Tingkat 2 --}}
                                    <div class="mb-3">
                                        <label for="min_qty_2" class="form-label fw-bold">Minimal Pembelian (Unit) <span class="text-danger">*</span></label>
                                        <input type="number" name="min_qty_2" id="min_qty_2" class="form-control form-control-lg" value="{{ old('min_qty_2', isset($allSettings[1]) ? $allSettings[1]->min_qty : 6) }}" min="1" placeholder="Contoh: 6">
                                        <div class="form-text text-muted">Harus lebih besar dari Kuantitas Minimal Tingkat 1.</div>
                                    </div>

                                    {{-- Jenis Potongan Tingkat 2 --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Jenis Potongan Harga <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="discount_type_2" id="type_nominal_2" value="nominal" {{ old('discount_type_2', isset($allSettings[1]) ? $allSettings[1]->discount_type : 'nominal') === 'nominal' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium" for="type_nominal_2">
                                                    Nominal Rupiah (Rp)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="discount_type_2" id="type_percentage_2" value="percentage" {{ old('discount_type_2', isset($allSettings[1]) ? $allSettings[1]->discount_type : 'nominal') === 'percentage' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium" for="type_percentage_2">
                                                    Persentase (%)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Nilai Potongan Tingkat 2 --}}
                                    <div class="mb-3">
                                        <label for="discount_value_2_display" class="form-label fw-bold" id="value_label_2">Nilai Potongan (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text" id="value_addon_2">Rp</span>
                                            <input type="text" id="discount_value_2_display" class="form-control form-control-lg" value="{{ old('discount_value_2', isset($allSettings[1]) ? $allSettings[1]->discount_value : '') }}" placeholder="Contoh: 10.000">
                                            <input type="hidden" name="discount_value_2" id="discount_value_2" value="{{ old('discount_value_2', isset($allSettings[1]) ? $allSettings[1]->discount_value : '') }}">
                                        </div>
                                        <div class="form-text text-muted" id="value_help_2">Masukkan potongan harga per unit barang untuk tingkat kedua.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold">
                        Simpan Perubahan
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
        
        // Tier 1 Elements
        const typeNominal = document.getElementById('type_nominal');
        const typePercentage = document.getElementById('type_percentage');
        const valueLabel = document.getElementById('value_label');
        const valueAddon = document.getElementById('value_addon');
        const valueHelp = document.getElementById('value_help');
        const valueInput = document.getElementById('discount_value');

        // Tier 2 Elements
        const enableTier2Checkbox = document.getElementById('enable_tier_2');
        const tier2Fields = document.getElementById('tier_2_fields');
        const minQty2Input = document.getElementById('min_qty_2');
        const typeNominal2 = document.getElementById('type_nominal_2');
        const typePercentage2 = document.getElementById('type_percentage_2');
        const valueLabel2 = document.getElementById('value_label_2');
        const valueAddon2 = document.getElementById('value_addon_2');
        const valueHelp2 = document.getElementById('value_help_2');
        const valueInput2 = document.getElementById('discount_value_2');

        // Target variant ID from backend (for preselection)
        const targetVariantId = "{{ old('product_variant_id', $setting->product_variant_id) }}";

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
                    
                    // Cek jika varian terpilih
                    if (v.id == targetVariantId) {
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
        // Trigger saat load pertama kali
        if (productSelect.value) {
            handleProductChange();
        }

        // Handler untuk toggle TIER 2
        window.toggleTier2Section = function() {
            if (enableTier2Checkbox.checked) {
                tier2Fields.style.display = 'block';
                minQty2Input.required = true;
                valueInput2.required = true;
            } else {
                tier2Fields.style.display = 'none';
                minQty2Input.required = false;
                valueInput2.required = false;
            }
        };

        // Handler untuk tipe potongan Tier 1
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function formatDiscountInput(displayEl, hiddenEl, isPercentage) {
            let rawVal = displayEl.value;
            if (isPercentage) {
                // Strip everything except digits and one dot/comma
                let cleaned = rawVal.replace(/[^0-9.,]/g, '');
                // normalize comma to dot
                cleaned = cleaned.replace(/,/g, '.');
                // split by dot to ensure only one dot
                const parts = cleaned.split('.');
                if (parts.length > 2) {
                    cleaned = parts[0] + '.' + parts.slice(1).join('');
                }
                
                // limit percentage to <= 100
                if (parseFloat(cleaned) > 100) {
                    cleaned = '100';
                }
                
                displayEl.value = cleaned;
                hiddenEl.value = cleaned;
            } else {
                // Rupiah: strip non-digits, format with dots
                let cleaned = rawVal.replace(/\D/g, '');
                hiddenEl.value = cleaned;
                displayEl.value = cleaned ? formatNumber(cleaned) : '';
            }
        }

        const displayInput1 = document.getElementById('discount_value_display');
        const displayInput2 = document.getElementById('discount_value_2_display');

        if (displayInput1 && valueInput) {
            displayInput1.addEventListener('input', function() {
                formatDiscountInput(this, valueInput, typePercentage.checked);
            });
        }

        if (displayInput2 && valueInput2) {
            displayInput2.addEventListener('input', function() {
                formatDiscountInput(this, valueInput2, typePercentage2.checked);
            });
        }

        function updateDiscountTypeUI() {
            const displayEl = document.getElementById('discount_value_display');
            if (typePercentage.checked) {
                valueLabel.textContent = 'Persentase Potongan (%) *';
                valueAddon.textContent = '%';
                valueHelp.textContent = 'Masukkan nilai persentase diskon (contoh: 10 untuk 10%). Maksimal 100%.';
                if (displayEl) displayEl.placeholder = 'Contoh: 10';
            } else {
                valueLabel.textContent = 'Nilai Potongan (Rp) *';
                valueAddon.textContent = 'Rp';
                valueHelp.textContent = 'Masukkan jumlah nominal potongan harga per unit barang.';
                if (displayEl) displayEl.placeholder = 'Contoh: 5.000';
            }
            if (displayEl && valueInput) {
                formatDiscountInput(displayEl, valueInput, typePercentage.checked);
            }
        }

        // Handler untuk tipe potongan Tier 2
        function updateDiscountTypeUI2() {
            const displayEl = document.getElementById('discount_value_2_display');
            if (typePercentage2.checked) {
                valueLabel2.textContent = 'Persentase Potongan (%) *';
                valueAddon2.textContent = '%';
                valueHelp2.textContent = 'Masukkan nilai persentase diskon (contoh: 15 untuk 15%). Maksimal 100%.';
                if (displayEl) displayEl.placeholder = 'Contoh: 15';
            } else {
                valueLabel2.textContent = 'Nilai Potongan (Rp) *';
                valueAddon2.textContent = 'Rp';
                valueHelp2.textContent = 'Masukkan jumlah nominal potongan harga per unit barang untuk tingkat kedua.';
                if (displayEl) displayEl.placeholder = 'Contoh: 10.000';
            }
            if (displayEl && valueInput2) {
                formatDiscountInput(displayEl, valueInput2, typePercentage2.checked);
            }
        }

        typeNominal.addEventListener('change', updateDiscountTypeUI);
        typePercentage.addEventListener('change', updateDiscountTypeUI);
        
        typeNominal2.addEventListener('change', updateDiscountTypeUI2);
        typePercentage2.addEventListener('change', updateDiscountTypeUI2);
        
        // Initial load format
        if (valueInput && valueInput.value && displayInput1) {
            displayInput1.value = valueInput.value;
        }
        if (valueInput2 && valueInput2.value && displayInput2) {
            displayInput2.value = valueInput2.value;
        }

        // Trigger saat load pertama kali
        updateDiscountTypeUI();
        updateDiscountTypeUI2();
        window.toggleTier2Section();
    });
</script>
@endsection
