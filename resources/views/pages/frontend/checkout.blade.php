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
                        <h5 class="fw-bold mb-3">Metode Pembayaran</h5>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="cash" value="cash" checked>
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="cash">
                                    <i class="fas fa-money-bill me-2"></i> 
                                    <strong>Tunai</strong>
                                    <small class="d-block text-muted">Pembayaran di tempat</small>
                                </label>
                            </div>

                            <div class="col-md-6 mb-2">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="transfer" value="transfer">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="transfer">
                                    <i class="fas fa-university me-2"></i> 
                                    <strong>Transfer Bank</strong>
                                    <small class="d-block text-muted">BCA / Mandiri</small>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="metode_pembayaran" id="ewallet" value="e-wallet">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-3 text-start" for="ewallet">
                                    <i class="fas fa-wallet me-2"></i> 
                                    <strong>E-Wallet</strong>
                                    <small class="d-block text-muted">GoPay, OVO, Dana</small>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Setelah klik "Buat Pesanan", silakan lakukan pembayaran sesuai metode yang dipilih.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                            Buat Pesanan Sekarang
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