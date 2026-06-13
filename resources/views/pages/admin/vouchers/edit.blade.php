@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-ticket-alt text-primary me-2"></i>Edit Voucher</h3>
            <p class="text-muted mb-0">Perbarui rincian program potongan harga atau masa berlaku voucher.</p>
        </div>
        <div>
            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- FORM CARD --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form action="{{ route('admin.vouchers.update', $voucher) }}" method="POST" id="voucherForm">
                    @csrf
                    @method('PUT')

                    {{-- Kode Voucher --}}
                    <div class="mb-3">
                        <label for="code" class="form-label fw-bold">Kode Voucher <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control form-control-lg text-uppercase" placeholder="Contoh: PROMOHEBAT" required value="{{ old('code', $voucher->code) }}">
                        <div class="form-text text-muted">Hanya boleh berisi huruf dan angka tanpa spasi. Otomatis dikonversi ke huruf besar.</div>
                    </div>

                    <div class="row">
                        {{-- Jenis Potongan --}}
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label fw-bold">Jenis Potongan <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select form-select-lg" required>
                                <option value="nominal" {{ old('type', $voucher->type) === 'nominal' ? 'selected' : '' }}>Nominal Rupiah (Rp)</option>
                                <option value="percentage" {{ old('type', $voucher->type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                <option value="shipping_subsidy" {{ old('type', $voucher->type) === 'shipping_subsidy' ? 'selected' : '' }}>Subsidi Ongkir (Rp)</option>
                            </select>
                        </div>

                        {{-- Nilai Potongan --}}
                        <div class="col-md-6 mb-3">
                            <label for="value_display" class="form-label fw-bold" id="value_label">Nilai Potongan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text" id="value_addon">Rp</span>
                                <input type="text" id="value_display" class="form-control form-control-lg" placeholder="Contoh: 15.000" required>
                                <input type="hidden" name="value" id="value" value="{{ old('value', $voucher->value) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Minimal Pembelian --}}
                        <div class="col-md-6 mb-3">
                            <label for="min_purchase_display" class="form-label fw-bold">Minimal Pembelian (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="min_purchase_display" class="form-control form-control-lg" placeholder="Contoh: 200.000" required>
                                <input type="hidden" name="min_purchase" id="min_purchase" value="{{ old('min_purchase', $voucher->min_purchase) }}" required>
                            </div>
                            <div class="form-text text-muted">Syarat minimal total belanja kelipatan eceran.</div>
                        </div>

                        {{-- Maksimal Potongan --}}
                        <div class="col-md-6 mb-3" id="max_discount_wrapper" style="display: none;">
                            <label for="max_discount_display" class="form-label fw-bold">Maksimal Potongan Diskon (Rp)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="max_discount_display" class="form-control form-control-lg" placeholder="Contoh: 20.000">
                                <input type="hidden" name="max_discount" id="max_discount" value="{{ old('max_discount', $voucher->max_discount) }}">
                            </div>
                            <div class="form-text text-muted">Batas maksimal potongan diskon (khusus tipe Persentase). Kosongkan jika tanpa batas.</div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Kuota --}}
                        <div class="col-md-4 mb-3">
                            <label for="quota" class="form-label fw-bold">Kuota Voucher</label>
                            <input type="number" name="quota" id="quota" class="form-control form-control-lg" placeholder="Contoh: 100" min="1" value="{{ old('quota', $voucher->quota) }}">
                            <div class="form-text text-muted">Batas maksimal klaim voucher secara keseluruhan. Kosongkan jika tidak terbatas.</div>
                        </div>

                        {{-- Tanggal Mulai --}}
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label fw-bold">Tanggal Mulai Berlaku</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-lg" value="{{ old('start_date', $voucher->start_date ? $voucher->start_date->format('Y-m-d') : '') }}">
                            <div class="form-text text-muted">Kosongkan jika aktif mulai hari ini.</div>
                        </div>

                        {{-- Tanggal Selesai --}}
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label fw-bold">Tanggal Berakhir (Expired)</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-lg" value="{{ old('end_date', $voucher->end_date ? $voucher->end_date->format('Y-m-d') : '') }}">
                            <div class="form-text text-muted">Kosongkan jika berlaku selamanya (lifetime).</div>
                        </div>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="mb-4">
                        <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                            <input class="form-check-input fs-3 m-0" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">Aktifkan Voucher Ini Sekarang</label>
                        </div>
                        <div class="form-text text-muted mt-2">Voucher nonaktif tidak akan bisa digunakan oleh pelanggan saat checkout.</div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm fw-bold">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-light rounded-pill px-4 py-3 fw-bold">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const valueLabel = document.getElementById('value_label');
        const valueAddon = document.getElementById('value_addon');
        const valueDisplay = document.getElementById('value_display');
        const valueHidden = document.getElementById('value');

        const minPurchaseDisplay = document.getElementById('min_purchase_display');
        const minPurchaseHidden = document.getElementById('min_purchase');

        const maxDiscountWrapper = document.getElementById('max_discount_wrapper');
        const maxDiscountDisplay = document.getElementById('max_discount_display');
        const maxDiscountHidden = document.getElementById('max_discount');

        // Helper: Format Number with dots
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Helper: Format and sync input
        function formatCurrencyInput(displayEl, hiddenEl, limitValue = false) {
            let rawVal = displayEl.value;
            if (limitValue) {
                // Percentage: strip everything except digits and one dot/comma
                let cleaned = rawVal.replace(/[^0-9.,]/g, '').replace(/,/g, '.');
                const parts = cleaned.split('.');
                if (parts.length > 2) {
                    cleaned = parts[0] + '.' + parts.slice(1).join('');
                }
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

        // Handle Type Changes
        function handleTypeChange() {
            const type = typeSelect.value;
            if (type === 'percentage') {
                valueLabel.textContent = 'Nilai Potongan (%) *';
                valueAddon.textContent = '%';
                valueDisplay.placeholder = 'Contoh: 10';
                maxDiscountWrapper.style.display = 'block';
                
                // Reformat existing values
                formatCurrencyInput(valueDisplay, valueHidden, true);
            } else if (type === 'shipping_subsidy') {
                valueLabel.textContent = 'Subsidi Ongkir (Rp) *';
                valueAddon.textContent = 'Rp';
                valueDisplay.placeholder = 'Contoh: 15.000';
                maxDiscountWrapper.style.display = 'none';
                maxDiscountDisplay.value = '';
                maxDiscountHidden.value = '';

                // Reformat existing values
                formatCurrencyInput(valueDisplay, valueHidden, false);
            } else {
                valueLabel.textContent = 'Nilai Potongan (Rp) *';
                valueAddon.textContent = 'Rp';
                valueDisplay.placeholder = 'Contoh: 15.000';
                maxDiscountWrapper.style.display = 'none';
                maxDiscountDisplay.value = '';
                maxDiscountHidden.value = '';

                // Reformat existing values
                formatCurrencyInput(valueDisplay, valueHidden, false);
            }
        }

        // Bind Listeners
        typeSelect.addEventListener('change', handleTypeChange);

        valueDisplay.addEventListener('input', function() {
            formatCurrencyInput(this, valueHidden, typeSelect.value === 'percentage');
        });

        minPurchaseDisplay.addEventListener('input', function() {
            formatCurrencyInput(this, minPurchaseHidden, false);
        });

        maxDiscountDisplay.addEventListener('input', function() {
            formatCurrencyInput(this, maxDiscountHidden, false);
        });

        // Initialize display values on load
        if (valueHidden.value) {
            valueDisplay.value = valueHidden.value;
            formatCurrencyInput(valueDisplay, valueHidden, typeSelect.value === 'percentage');
        }
        if (minPurchaseHidden.value) {
            minPurchaseDisplay.value = minPurchaseHidden.value;
            formatCurrencyInput(minPurchaseDisplay, minPurchaseHidden, false);
        }
        if (maxDiscountHidden.value) {
            maxDiscountDisplay.value = maxDiscountHidden.value;
            formatCurrencyInput(maxDiscountDisplay, maxDiscountHidden, false);
        }

        // Trigger on load
        handleTypeChange();
    });
</script>
@endsection
