@extends('layouts.frontend')

@section('content')
@php
    $isB2B = auth()->check() && auth()->user()->role === 'branch';
@endphp
<div class="container py-5">

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cart.processCheckout') }}" method="POST">
        @csrf

        <div class="row g-4">

            {{-- 🔥 KIRI: FORM --}}
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4">Informasi Pengiriman</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nama Lengkap</label>
                            <input type="text" 
                                   name="nama_penerima" 
                                   class="form-control"
                                   value="{{ auth()->user()->name }}" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control"
                                   value="{{ auth()->user()->email }}" 
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">No. Telepon</label>
                        <input type="text" 
                               name="no_telp" 
                               class="form-control" 
                               value="{{ auth()->user()->phone }}"
                               placeholder="08xxxx" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Alamat Lengkap</label>
                        <textarea name="alamat_pengiriman" 
                                  class="form-control" 
                                  rows="3" 
                                  required>{{ auth()->user()->address }}</textarea>
                    </div>

                    @if(!$isB2B)
                        <!-- Autocomplete Area Biteship -->
                        <div class="mb-3 position-relative">
                            <label class="form-label small fw-bold"><i class="fas fa-map-marker-alt text-primary me-1"></i> Kota / Kecamatan Tujuan</label>
                            <input type="text" id="area_search_input" class="form-control rounded-3" placeholder="Ketik minimal 3 huruf kecamatan atau kota..." autocomplete="off" required>
                            <div id="area_suggestions" class="list-group position-absolute w-100 shadow rounded-3 d-none" style="z-index: 1050; max-height: 200px; overflow-y: auto; background: white;"></div>
                            <input type="hidden" name="destination_area_id" id="destination_area_id">
                        </div>

                        <!-- Opsi Kurir & Ongkir -->
                        <div class="mb-4 d-none" id="shipping_options_container">
                            <label class="form-label small fw-bold"><i class="fas fa-shipping-fast text-primary me-1"></i> Pilih Layanan Pengiriman</label>
                            <div id="shipping_rates_list" class="row g-2">
                                <!-- Diisi dinamis via JS -->
                            </div>
                            <input type="hidden" name="kurir" id="shipping_kurir">
                            <input type="hidden" name="layanan" id="shipping_service">
                            <input type="hidden" name="ongkir" id="shipping_cost" value="0">
                        </div>
                    @endif



                    @if(!$isB2B)
                        {{-- 🔥 METODE PEMBAYARAN --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                            <h5 class="fw-bold mb-0">Metode Pembayaran</h5>
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill small border"><i class="fas fa-shield-alt text-success me-1"></i> Otomatis & Terenkripsi</span>
                        </div>

                        <div class="row g-3">
                            {{-- SEMUA PEMBAYARAN ONLINE --}}
                            <div class="col-12">
                                <input type="radio" class="btn-check payment-radio" name="metode_pembayaran" id="midtrans" value="midtrans" checked>
                                <label class="payment-card border rounded-4 p-3 d-block cursor-pointer position-relative transition-all" for="midtrans">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-primary text-white rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                <i class="fas fa-bolt fa-lg"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <strong class="h6 mb-0 text-dark">Semua Pembayaran Online</strong>
                                                    <span class="badge bg-success bg-gradient text-white rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                                        <i class="fas fa-check-circle me-1"></i>REKOMENDASI
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block mt-1">Pilih bebas Virtual Account, Scan QRIS, E-Wallet, atau Kartu Kredit</small>
                                            </div>
                                        </div>
                                        <div class="radio-check-icon text-primary h5 mb-0">
                                            <i class="far fa-circle text-muted uncheck"></i>
                                            <i class="fas fa-check-circle text-primary checked d-none"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2 pt-2 border-top">
                                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;"><i class="fas fa-university me-1 text-info"></i>Virtual Account</span>
                                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;"><i class="fas fa-qrcode me-1 text-danger"></i>QRIS</span>
                                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;"><i class="fas fa-wallet me-1 text-warning"></i>E-Wallet</span>
                                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.7rem;"><i class="fas fa-credit-card me-1 text-primary"></i>Kartu Kredit</span>
                                    </div>
                                </label>
                            </div>

                            {{-- VIRTUAL ACCOUNT BANK --}}
                            <div class="col-md-6">
                                <input type="radio" class="btn-check payment-radio" name="metode_pembayaran" id="midtrans_va" value="midtrans_va">
                                <label class="payment-card border rounded-4 p-3 d-block cursor-pointer position-relative transition-all h-100" for="midtrans_va">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark small">Virtual Account Bank</strong>
                                                <small class="text-muted" style="font-size: 0.75rem;">BCA, Mandiri, BRI, BNI, Permata (Otomatis)</small>
                                            </div>
                                        </div>
                                        <div class="radio-check-icon text-primary small">
                                            <i class="far fa-circle text-muted uncheck"></i>
                                            <i class="fas fa-check-circle text-primary checked d-none"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {{-- QRIS & E-WALLET --}}
                            <div class="col-md-6">
                                <input type="radio" class="btn-check payment-radio" name="metode_pembayaran" id="midtrans_qris" value="midtrans_qris">
                                <label class="payment-card border rounded-4 p-3 d-block cursor-pointer position-relative transition-all h-100" for="midtrans_qris">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-qrcode"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark small">QRIS & E-Wallet Instant</strong>
                                                <small class="text-muted" style="font-size: 0.75rem;">Scan QRIS / GoPay / ShopeePay / Dana</small>
                                            </div>
                                        </div>
                                        <div class="radio-check-icon text-primary small">
                                            <i class="far fa-circle text-muted uncheck"></i>
                                            <i class="fas fa-check-circle text-primary checked d-none"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {{-- KARTU KREDIT --}}
                            <div class="col-12">
                                <input type="radio" class="btn-check payment-radio" name="metode_pembayaran" id="midtrans_cc" value="midtrans_cc">
                                <label class="payment-card border rounded-4 p-3 d-block cursor-pointer position-relative transition-all" for="midtrans_cc">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark small">Kartu Kredit / Debit</strong>
                                                <small class="text-muted" style="font-size: 0.75rem;">Visa, Mastercard, JCB (3D Secure Safe)</small>
                                            </div>
                                        </div>
                                        <div class="radio-check-icon text-primary small">
                                            <i class="far fa-circle text-muted uncheck"></i>
                                            <i class="fas fa-check-circle text-primary checked d-none"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <style>
                            .payment-card {
                                background-color: #ffffff;
                                transition: all 0.25s ease-in-out;
                                cursor: pointer;
                            }
                            .payment-card:hover {
                                border-color: #0d6efd !important;
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(13, 110, 253, 0.08);
                            }
                            .payment-radio:checked + .payment-card {
                                border-color: #0d6efd !important;
                                border-width: 2px !important;
                                background-color: #f8faff;
                                box-shadow: 0 4px 15px rgba(13, 110, 253, 0.12);
                            }
                            .payment-radio:checked + .payment-card .checked {
                                display: inline-block !important;
                            }
                            .payment-radio:checked + .payment-card .uncheck {
                                display: none !important;
                            }
                        </style>

                        <div class="card border border-warning bg-warning bg-opacity-10 rounded-4 p-3 mt-4 mb-3">
                            <div class="form-check d-flex align-items-start">
                                <input class="form-check-input me-2 mt-1" type="checkbox" name="age_confirmation" id="age_confirmation" value="1" required style="cursor: pointer; min-width: 18px; height: 18px;">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer mb-0" for="age_confirmation">
                                    <i class="fas fa-shield-alt text-danger me-1"></i>
                                    Saya mengonfirmasi berusia <strong>21 tahun atau lebih</strong>, memberikan data diri yang jujur, serta menyetujui Syarat & Ketentuan pembelian produk bernikotin.
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-light border rounded-4 small p-3 d-flex align-items-center text-muted">
                            <i class="fas fa-info-circle text-primary fa-lg me-3"></i>
                            <div>
                                Pembayaran diproses secara otomatis dan aman. Silakan klik tombol di bawah untuk melanjutkan.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-lg mt-2" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                            <i class="fas fa-shield-alt me-2"></i> Buat Pesanan Sekarang
                        </button>
                    @else
                        <input type="hidden" name="metode_pembayaran" value="branch_request">
                        
                        <div class="alert alert-success border-0 small mt-4 p-3 rounded-3 bg-opacity-10" style="background-color: #e6f7ef; color: #1b8a5a;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Info Pengajuan B2B:</strong> Sebagai akun cabang, pengajuan barang ini tidak memerlukan pembayaran. Setelah dikirim, Admin akan segera memverifikasi dan memproses pengiriman barang ke cabang Anda.
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan Barang
                        </button>
                    @endif
                </div>

            </div>


            {{-- 🔥 KANAN: RINGKASAN --}}
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>

                    @foreach($items as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-truncate" style="max-width: 70%;" title="{{ $item->product->nama_barang }}{{ $item->variant ? ' (' . $item->variant->nama_varian . ')' : '' }}">
                                {{ $item->product->nama_barang }}
                                @if($item->variant)
                                    <small class="text-muted d-block">Varian: {{ $item->variant->nama_varian }}</small>
                                @endif
                                (x{{ $item->quantity }})
                            </span>
                            <span class="small">
                                Rp {{ number_format($item->getSubtotal(), 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach

                    <hr>

                    @if(!$isB2B)
                        {{-- Voucher Code Box --}}
                        <div class="mb-3 mt-2">
                            <label class="form-label small fw-bold"><i class="fas fa-ticket-alt me-1 text-primary"></i>Mempunyai Kode Voucher?</label>
                            <div class="input-group">
                                <input type="text" id="voucher_code_input" class="form-control text-uppercase" placeholder="Masukkan Kode Voucher" value="{{ $voucher ? $voucher->code : '' }}" {{ $voucher ? 'disabled' : '' }}>
                                <button class="btn {{ $voucher ? 'btn-danger' : 'btn-primary' }}" type="button" id="btn_apply_voucher">
                                    {{ $voucher ? 'Batalkan' : 'Terapkan' }}
                                </button>
                            </div>
                            <div id="voucher_message" class="small mt-1 {{ $voucherError ? 'text-danger' : ($voucher ? 'text-success' : '') }}">
                                @if($voucher)
                                    Voucher {{ $voucher->code }} berhasil diterapkan! (Potongan Rp {{ number_format($discount, 0, ',', '.') }})
                                @elseif($voucherError)
                                    {{ $voucherError }}
                                @endif
                            </div>
                        </div>
                        <hr>
                    @endif

                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal Belanja</span>
                        <span>Rp {{ number_format($cart->total_price, 0, ',', '.') }}</span>
                    </div>

                    @if(!$isB2B)
                        <div class="d-flex justify-content-between mb-2 small text-muted d-none" id="shipping_cost_row">
                            <span>Ongkos Kirim</span>
                            <span id="shipping_cost_val">Rp 0</span>
                        </div>
                    @endif

                    @if(!$isB2B)
                        <div class="d-flex justify-content-between mb-2 text-success small" id="voucher_discount_row" style="display: {{ $voucher ? 'flex' : 'none' }} !important;">
                            <span id="voucher_discount_label">{{ $voucher && $voucher->type === 'shipping_subsidy' ? 'Subsidi Ongkir' : 'Potongan Voucher' }}</span>
                            <span id="voucher_discount_val">-Rp {{ number_format($discount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-2">
                        <span class="fw-bold">Total Tagihan</span>
                        <h5 class="text-primary fw-bold mb-0" id="total_tagihan_val">
                            Rp {{ number_format($cart->total_price - $discount, 0, ',', '.') }}
                        </h5>
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>

@if(!$isB2B)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnApply = document.getElementById('btn_apply_voucher');
        const inputVoucher = document.getElementById('voucher_code_input');
        const msgVoucher = document.getElementById('voucher_message');
        const discountRow = document.getElementById('voucher_discount_row');
        const discountVal = document.getElementById('voucher_discount_val');
        const totalTagihanVal = document.getElementById('total_tagihan_val');

        // Shipping & Autocomplete Elements
        const areaSearchInput = document.getElementById('area_search_input');
        const areaSuggestions = document.getElementById('area_suggestions');
        const destinationAreaId = document.getElementById('destination_area_id');
        const shippingOptionsContainer = document.getElementById('shipping_options_container');
        const shippingRatesList = document.getElementById('shipping_rates_list');
        const shippingCostRow = document.getElementById('shipping_cost_row');
        const shippingCostVal = document.getElementById('shipping_cost_val');
        const shippingCostInput = document.getElementById('shipping_cost');
        const shippingKurir = document.getElementById('shipping_kurir');
        const shippingService = document.getElementById('shipping_service');

        // State variables
        let subtotal = {{ $cart->total_price }};
        let discount = {{ $discount }};
        let ongkir = 0;

        function updateTotalTagihan() {
            let total = Math.max(0, subtotal - discount) + ongkir;
            totalTagihanVal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // --- VOUCHER LOGIC ---
        if (btnApply) {
            btnApply.addEventListener('click', function() {
                const isApplied = btnApply.classList.contains('btn-danger');
                
                if (isApplied) {
                    // Remove Voucher
                    fetch("{{ route('cart.removeVoucher') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            btnApply.textContent = 'Terapkan';
                            btnApply.className = 'btn btn-primary';
                            inputVoucher.value = '';
                            inputVoucher.disabled = false;
                            msgVoucher.textContent = '';
                            msgVoucher.className = 'small mt-1';
                            discountRow.style.setProperty('display', 'none', 'important');
                            
                            discount = 0;
                            updateTotalTagihan();
                        }
                    });
                } else {
                    // Apply Voucher
                    const code = inputVoucher.value.trim();
                    if (!code) {
                        alert('Silakan masukkan kode voucher terlebih dahulu!');
                        return;
                    }

                    btnApply.disabled = true;
                    fetch("{{ route('cart.applyVoucher') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ code: code })
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnApply.disabled = false;
                        if (data.success) {
                            btnApply.textContent = 'Batalkan';
                            btnApply.className = 'btn btn-danger';
                            inputVoucher.disabled = true;
                            
                            // Update label dynamically
                            document.getElementById('voucher_discount_label').textContent = data.label;
                            msgVoucher.textContent = data.message + ' (' + data.label + ' ' + data.discount_formatted + ')';
                            msgVoucher.className = 'small mt-1 text-success';
                            
                            discountVal.textContent = '-' + data.discount_formatted;
                            discountRow.style.setProperty('display', 'flex', 'important');
                            
                            discount = parseInt(data.discount);
                            updateTotalTagihan();
                        } else {
                            msgVoucher.textContent = data.message;
                            msgVoucher.className = 'small mt-1 text-danger';
                        }
                    })
                    .catch(err => {
                        btnApply.disabled = false;
                        alert('Gagal menerapkan voucher.');
                    });
                }
            });
        }

        // --- SHIPMENT AUTOCOMPLETE LOGIC ---
        let searchTimeout = null;

        areaSearchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = areaSearchInput.value.trim();
            
            if (query.length < 3) {
                areaSuggestions.classList.add('d-none');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch(`{{ route('shipping.searchAreas') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(areas => {
                        areaSuggestions.innerHTML = '';
                        if (areas.length > 0) {
                            areaSuggestions.classList.remove('d-none');
                            areas.forEach(area => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action text-start small py-2 border-0';
                                btn.innerHTML = `<i class="fas fa-map-marker-alt text-primary me-2"></i> ${area.name}`;
                                btn.addEventListener('click', () => {
                                    areaSearchInput.value = area.name;
                                    destinationAreaId.value = area.id;
                                    areaSuggestions.classList.add('d-none');
                                    fetchShippingRates(area.id);
                                });
                                areaSuggestions.appendChild(btn);
                            });
                        } else {
                            areaSuggestions.classList.add('d-none');
                        }
                    });
            }, 400);
        });

        // Close suggestions on click outside
        document.addEventListener('click', function(e) {
            if (e.target !== areaSearchInput && e.target !== areaSuggestions) {
                areaSuggestions.classList.add('d-none');
            }
        });

        function fetchShippingRates(areaId) {
            shippingOptionsContainer.classList.add('d-none');
            shippingRatesList.innerHTML = '<div class="col-12 py-3 text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Mengambil tarif pengiriman...</div>';
            shippingOptionsContainer.classList.remove('d-none');
            
            fetch("{{ route('shipping.rates') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ destination_area_id: areaId })
            })
            .then(res => res.json())
            .then(rates => {
                shippingRatesList.innerHTML = '';
                if (rates && rates.length > 0) {
                    rates.forEach((rate, index) => {
                        const checked = index === 0 ? 'checked' : '';
                        const card = document.createElement('div');
                        card.className = 'col-md-6 mb-2';
                        card.innerHTML = `
                            <input type="radio" class="btn-check" name="shipping_rate_select" id="rate_${index}" 
                                   value="${rate.price}" data-courier="${rate.company}" data-service="${rate.type}" ${checked}>
                            <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start h-100 d-flex flex-column justify-content-between" for="rate_${index}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-uppercase text-dark">${rate.company} - ${rate.type}</strong>
                                    <span class="text-primary fw-bold">Rp ${rate.price.toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>Layanan: ${rate.name}</span>
                                    <span>Est: ${rate.estimated_arrival}</span>
                                </div>
                            </label>
                        `;
                        shippingRatesList.appendChild(card);
                        
                        // Add event listener to input
                        card.querySelector('input').addEventListener('change', function() {
                            selectShippingRate(rate.price, rate.company, rate.type);
                        });
                    });
                    
                    // Select first rate by default
                    selectShippingRate(rates[0].price, rates[0].company, rates[0].type);
                } else {
                    shippingRatesList.innerHTML = '<div class="col-12 py-3 text-center text-danger"><i class="fas fa-exclamation-circle me-2"></i> Tidak ada layanan kurir yang tersedia untuk wilayah ini.</div>';
                }
            })
            .catch(err => {
                shippingRatesList.innerHTML = '<div class="col-12 py-3 text-center text-danger"><i class="fas fa-exclamation-circle me-2"></i> Gagal memuat tarif pengiriman. Silakan coba lagi.</div>';
            });
        }

        function selectShippingRate(price, courier, service) {
            ongkir = parseInt(price);
            shippingCostInput.value = ongkir;
            shippingKurir.value = courier;
            shippingService.value = service;
            
            shippingCostVal.textContent = 'Rp ' + ongkir.toLocaleString('id-ID');
            shippingCostRow.classList.remove('d-none');
            
            updateTotalTagihan();
        }
    });
</script>
@endif
@endsection