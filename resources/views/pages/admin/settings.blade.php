@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0 text-dark">Pengaturan Admin</h3>
        <a href="/admin/dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6 class="alert-heading fw-bold">Error!</h6>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- TAB NAVIGATION -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-selected="true">
                <i class="fas fa-cog me-2"></i> Umum
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-selected="false">
                <i class="fas fa-warehouse me-2"></i> Inventory
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-selected="false">
                <i class="fas fa-shopping-cart me-2"></i> Penjualan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-selected="false">
                <i class="fas fa-credit-card me-2"></i> Pembayaran
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-selected="false">
                <i class="fas fa-truck me-2"></i> Pengiriman
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab" aria-selected="false">
                <i class="fas fa-bell me-2"></i> Notifikasi
            </button>
        </li>
    </ul>

    <!-- TAB CONTENT -->
    <div class="tab-content">
        <!-- 1. PENGATURAN UMUM -->
        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Umum Sistem</h5>
                    <form action="{{ route('admin.settings.updateGeneral') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="app_name" class="form-label fw-semibold">Nama Aplikasi</label>
                            <input type="text" class="form-control" id="app_name" name="app_name" 
                                   value="{{ config('app.name', 'Podgasm Warehouse') }}" required>
                            <small class="text-muted">Nama yang akan ditampilkan di aplikasi</small>
                        </div>

                        <div class="mb-3">
                            <label for="app_description" class="form-label fw-semibold">Deskripsi Aplikasi</label>
                            <textarea class="form-control" id="app_description" name="app_description" rows="3">{{ config('app.description', '') }}</textarea>
                            <small class="text-muted">Deskripsi singkat tentang bisnis Anda</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Nomor Telepon Utama</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="{{ config('settings.phone', '+62') }}" required>
                            <small class="text-muted">Nomor yang dapat dihubungi customer</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Utama</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ config('settings.email', 'admin@podgasm.com') }}" required>
                            <small class="text-muted">Email untuk sistem dan notifikasi</small>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label fw-semibold">Alamat Kantor Pusat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required>{{ config('settings.address', '') }}</textarea>
                            <small class="text-muted">Alamat lengkap lokasi gudang pusat</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Umum
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. PENGATURAN INVENTORY -->
        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Inventory & Stok</h5>
                    <form action="{{ route('admin.settings.updateInventory') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="safety_stock_percentage" class="form-label fw-semibold">Safety Stock Percentage (%)</label>
                            <input type="number" class="form-control" id="safety_stock_percentage" name="safety_stock_percentage" 
                                   min="5" max="50" step="0.5" value="{{ config('inventory.safety_stock_percentage', 20) }}" required>
                            <small class="text-muted">Persentase stok minimal dari rata-rata penjualan (5-50%)</small>
                        </div>

                        <div class="mb-3">
                            <label for="low_stock_alert_days" class="form-label fw-semibold">Peringatan Stok Rendah (Hari)</label>
                            <input type="number" class="form-control" id="low_stock_alert_days" name="low_stock_alert_days" 
                                   min="1" max="90" value="{{ config('inventory.low_stock_alert_days', 7) }}" required>
                            <small class="text-muted">Berapa hari sebelumnya admin akan diperingatkan stok rendah</small>
                        </div>

                        <div class="mb-3">
                            <label for="expire_warning_days" class="form-label fw-semibold">Peringatan Kadaluarsa (Hari)</label>
                            <input type="number" class="form-control" id="expire_warning_days" name="expire_warning_days" 
                                   min="1" max="365" value="{{ config('inventory.expire_warning_days', 30) }}" required>
                            <small class="text-muted">Berapa hari sebelum kadaluarsa untuk ditampilkan warning</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_reorder_enabled" name="auto_reorder_enabled"
                                       {{ config('inventory.auto_reorder_enabled', false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="auto_reorder_enabled">
                                    Aktifkan Auto Reorder
                                </label>
                                <small class="text-muted d-block">Sistem akan otomatis membuat reorder ketika stok mencapai minimum</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Inventory
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. PENGATURAN PENJUALAN -->
        <div class="tab-pane fade" id="sales" role="tabpanel" aria-labelledby="sales-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Penjualan</h5>
                    <form action="{{ route('admin.settings.updateSales') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="min_order_amount" class="form-label fw-semibold">Minimal Order (Rp)</label>
                            <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                   min="0" step="1000" value="{{ config('sales.min_order_amount', 0) }}" required>
                            <small class="text-muted">Minimal jumlah pembelian dalam satu order</small>
                        </div>

                        <div class="mb-3">
                            <label for="max_order_amount" class="form-label fw-semibold">Maksimal Order (Rp)</label>
                            <input type="number" class="form-control" id="max_order_amount" name="max_order_amount" 
                                   min="1000" step="1000" value="{{ config('sales.max_order_amount', 999999999) }}" required>
                            <small class="text-muted">Maksimal jumlah pembelian dalam satu order</small>
                        </div>

                        <div class="mb-3">
                            <label for="discount_max_percentage" class="form-label fw-semibold">Diskon Maksimal (%)</label>
                            <input type="number" class="form-control" id="discount_max_percentage" name="discount_max_percentage" 
                                   min="0" max="100" step="1" value="{{ config('sales.discount_max_percentage', 30) }}" required>
                            <small class="text-muted">Persentase diskon maksimal yang diperbolehkan (0-100%)</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_promo" name="enable_promo"
                                       {{ config('sales.enable_promo', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="enable_promo">
                                    Aktifkan Promo/Diskon
                                </label>
                                <small class="text-muted d-block">Izinkan admin untuk membuat promo dan diskon</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Penjualan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 4. PENGATURAN PEMBAYARAN -->
        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Pembayaran</h5>
                    <form action="{{ route('admin.settings.updatePayment') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="payment_timeout_hours" class="form-label fw-semibold">Timeout Pembayaran (Jam)</label>
                            <input type="number" class="form-control" id="payment_timeout_hours" name="payment_timeout_hours" 
                                   min="1" max="72" value="{{ config('payment.payment_timeout_hours', 24) }}" required>
                            <small class="text-muted">Berapa jam customer memiliki waktu untuk membayar setelah order</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_confirm_payment" name="auto_confirm_payment"
                                       {{ config('payment.auto_confirm_payment', false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="auto_confirm_payment">
                                    Auto Confirm Payment
                                </label>
                                <small class="text-muted d-block">Otomatis konfirmasi pembayaran tanpa verifikasi manual</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Metode Pembayaran Aktif</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="payment_cash" name="payment_methods[]" value="cash" 
                                       {{ in_array('cash', config('payment.payment_methods', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_cash">
                                    <i class="fas fa-money-bill-wave me-2"></i> Transfer Bank
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="payment_transfer" name="payment_methods[]" value="transfer"
                                       {{ in_array('transfer', config('payment.payment_methods', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_transfer">
                                    <i class="fas fa-exchange-alt me-2"></i> E-Wallet
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="payment_ewallet" name="payment_methods[]" value="e-wallet"
                                       {{ in_array('e-wallet', config('payment.payment_methods', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="payment_ewallet">
                                    <i class="fas fa-wallet me-2"></i> Tunai / COD
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Pilih metode pembayaran apa saja yang akan tersedia</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 5. PENGATURAN PENGIRIMAN -->
        <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Pengiriman</h5>
                    <form action="{{ route('admin.settings.updateShipping') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_shipping_integration" name="enable_shipping_integration"
                                       {{ config('shipping.enable_shipping_integration', false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="enable_shipping_integration">
                                    Aktifkan Integrasi Pengiriman
                                </label>
                                <small class="text-muted d-block">Hubungkan dengan API kurir (Biteship, JNE, Tiki, dll)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="default_shipping_provider" class="form-label fw-semibold">Kurir Default</label>
                            <select class="form-select" id="default_shipping_provider" name="default_shipping_provider" required>
                                <option value="jne" {{ config('shipping.default_shipping_provider') === 'jne' ? 'selected' : '' }}>JNE</option>
                                <option value="tiki" {{ config('shipping.default_shipping_provider') === 'tiki' ? 'selected' : '' }}>TIKI</option>
                                <option value="pos" {{ config('shipping.default_shipping_provider') === 'pos' ? 'selected' : '' }}>Pos Indonesia</option>
                                <option value="fedex" {{ config('shipping.default_shipping_provider') === 'fedex' ? 'selected' : '' }}>FedEx</option>
                            </select>
                            <small class="text-muted">Kurir yang akan dipilih secara default untuk pengiriman</small>
                        </div>

                        <div class="mb-3">
                            <label for="free_shipping_amount" class="form-label fw-semibold">Free Shipping Untuk Order > (Rp)</label>
                            <input type="number" class="form-control" id="free_shipping_amount" name="free_shipping_amount" 
                                   min="0" step="1000" value="{{ config('shipping.free_shipping_amount', 0) }}" required>
                            <small class="text-muted">Minimal pembelian untuk mendapatkan gratis ongkir (0 = disabled)</small>
                        </div>

                        <div class="mb-4">
                            <label for="default_shipping_cost" class="form-label fw-semibold">Ongkir Default (Rp)</label>
                            <input type="number" class="form-control" id="default_shipping_cost" name="default_shipping_cost" 
                                   min="0" step="1000" value="{{ config('shipping.default_shipping_cost', 10000) }}" required>
                            <small class="text-muted">Biaya pengiriman standar jika tidak ada integrasi kurir</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Pengiriman
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 6. PENGATURAN NOTIFIKASI -->
        <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pengaturan Notifikasi</h5>
                    <form action="{{ route('admin.settings.updateNotification') }}" method="POST">
                        @csrf

                        <h6 class="fw-bold mb-3">Tipe Notifikasi</h6>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled"
                                       {{ config('notification.email_notifications_enabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="email_notifications_enabled">
                                    <i class="fas fa-envelope me-2"></i> Email Notification
                                </label>
                                <small class="text-muted d-block">Kirim notifikasi via email ke admin</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sms_notifications_enabled" name="sms_notifications_enabled"
                                       {{ config('notification.sms_notifications_enabled', false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="sms_notifications_enabled">
                                    <i class="fas fa-sms me-2"></i> SMS Notification
                                </label>
                                <small class="text-muted d-block">Kirim notifikasi via SMS ke nomor yang terdaftar</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Trigger Notifikasi</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_low_stock" name="notify_low_stock"
                                       {{ config('notification.notify_low_stock', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_low_stock">
                                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Notifikasi Stok Rendah
                                </label>
                                <small class="text-muted d-block">Beritahu admin ketika stok produk mencapai safety stock</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_pending_orders" name="notify_pending_orders"
                                       {{ config('notification.notify_pending_orders', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_pending_orders">
                                    <i class="fas fa-hourglass-half me-2 text-info"></i> Notifikasi Pending Orders
                                </label>
                                <small class="text-muted d-block">Beritahu admin ketika ada order yang menunggu pembayaran</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notify_payment_confirmed" name="notify_payment_confirmed"
                                       {{ config('notification.notify_payment_confirmed', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_payment_confirmed">
                                    <i class="fas fa-check-circle me-2 text-success"></i> Notifikasi Payment Confirmed
                                </label>
                                <small class="text-muted d-block">Beritahu admin ketika pembayaran dari customer dikonfirmasi</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Pengaturan Notifikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
