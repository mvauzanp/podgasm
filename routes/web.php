<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SafetyStockController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PromoBannerController;
use App\Http\Controllers\Admin\B2BPriceSettingController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Branch\BranchController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/category/{slug}', [PublicController::class, 'categoryIndex'])->name('public.category');
Route::get('/product/{id}/stock', [PublicController::class, 'getStock'])->whereNumber('id')->middleware('throttle:120,1')->name('public.product.stock');
Route::get('/product/{slug}', [PublicController::class, 'show'])->name('public.product.show'); // ✅ PERBAIKAN #6
Route::get('/search', [PublicController::class, 'search'])->middleware('throttle:60,1')->name('public.search'); // ✅ PERBAIKAN #6
Route::get('/search/live', [PublicController::class, 'liveSearch'])->middleware('throttle:60,1')->name('public.search.live');

// ✅ MIDTRANS WEBHOOK NOTIFICATION - Public
Route::post('/api/midtrans/notification', [\App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('register.verify.show');
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('register.verify.process');
Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1')->name('register.resend.otp');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



/*
|--------------------------------------------------------------------------
| CART & CHECKOUT - PROTECTED WITH AUTH
|--------------------------------------------------------------------------
*/

Route::get('/cart/offcanvas', [CartController::class, 'offcanvas'])->name('cart.offcanvas');

Route::middleware(['auth'])
    ->prefix('cart')
    ->name('cart.')
    ->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add/{id}', [CartController::class, 'addToCart'])->name('add');
        Route::patch('/update', [CartController::class, 'updateCart'])->name('update');
        Route::delete('/remove', [CartController::class, 'removeFromCart'])->name('remove');
    });

/*
|--------------------------------------------------------------------------
| CHECKOUT - PROTECTED WITH AUTH
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('cart.checkout');
    Route::post('/checkout/apply-voucher', [CheckoutController::class, 'applyVoucher'])->middleware('throttle:20,1')->name('cart.applyVoucher');
    Route::post('/checkout/remove-voucher', [CheckoutController::class, 'removeVoucher'])->name('cart.removeVoucher');
    Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->middleware('throttle:10,1')->name('cart.processCheckout');
    Route::get('/shipping/search-areas', [ShippingController::class, 'searchShippingAreas'])->middleware('throttle:60,1')->name('shipping.searchAreas');
    Route::get('/shipping/search-places', [ShippingController::class, 'searchPlaces'])->middleware('throttle:60,1')->name('shipping.searchPlaces');
    Route::get('/shipping/reverse-geocode', [ShippingController::class, 'reverseGeocode'])->middleware('throttle:60,1')->name('shipping.reverseGeocode');
    Route::post('/shipping/rates', [ShippingController::class, 'getShippingRates'])->middleware('throttle:30,1')->name('shipping.rates');
    Route::get('/history', [OrderController::class, 'history'])->name('order.history');
    Route::get('/history/{id}', [OrderController::class, 'show'])->name('order.show');
    
    // ✅ PERBAIKAN #4: Payment confirmation & order cancellation routes
    Route::post('/history/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('order.confirmPayment');
    Route::post('/history/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    
    // ✅ MIDTRANS PAYMENT GATEWAY
    Route::get('/history/{order}/snap-token', [\App\Http\Controllers\MidtransController::class, 'getSnapToken'])->name('order.snapToken');
    Route::post('/history/{order}/reset-payment', [\App\Http\Controllers\MidtransController::class, 'resetSnapToken'])->name('order.resetPayment');
    
    // ✅ FITUR TRACKING
    Route::get('/history/{id}/track', [OrderController::class, 'track'])->name('order.track');

    // ✅ USER PROFILE ROUTES
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // ✅ BUKU ALAMAT PENGIRIMAN MULTI-ADDRESS (BITESHIP INTEGRATED)
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.addresses.destroy');
    Route::post('/profile/addresses/{id}/primary', [ProfileController::class, 'setPrimaryAddress'])->name('profile.addresses.setPrimary');
});

// ✅ BITESHIP & MIDTRANS WEBHOOKS (CSRF EXEMPTED)
Route::post('/api/biteship/webhook', [\App\Http\Controllers\BiteshipWebhookController::class, 'handle'])->name('biteship.webhook');
Route::post('/webhook/biteship', [\App\Http\Controllers\BiteshipWebhookController::class, 'handle'])->name('biteship.webhook.alt');
Route::post('/api/midtrans/notification', [\App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification');
Route::post('/midtrans/notification', [\App\Http\Controllers\MidtransController::class, 'notification'])->name('midtrans.notification.alt');

/*
|--------------------------------------------------------------------------
| WISHLIST - PROTECTED WITH AUTH
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('wishlist')
    ->name('wishlist.')
    ->group(function () {
        Route::get('/', [WishlistController::class, 'wishlist'])->name('index');
        Route::post('/add/{id}', [WishlistController::class, 'addToWishlist'])->name('add');
        Route::get('/add/{id}', [WishlistController::class, 'addToWishlist']); // backward compatibility
        Route::post('/remove/{id}', [WishlistController::class, 'removeFromWishlist'])->name('remove');
        Route::delete('/remove/{id}', [WishlistController::class, 'removeFromWishlist']);
    });

/*
|--------------------------------------------------------------------------
| REVIEWS - PROTECTED WITH AUTH
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->middleware('throttle:10,1')->name('review.store');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.') // INI KUNCINYA! Biar semua route di bawah otomatis punya prefix 'admin.'
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Manajemen Kategori (Sinkron Olsera)
        Route::resource('categories', CategoryController::class);

        // Manajemen Produk & Upload Foto HD
        Route::resource('products', ProductController::class);

        // Manajemen Banner Promo
        Route::resource('banners', PromoBannerController::class);

        // Setting Harga Grosir / Tier Diskon (Sementara)
        Route::resource('b2b-prices', B2BPriceSettingController::class)->except(['show']);

        // Manajemen Voucher B2C
        Route::resource('vouchers', VoucherController::class)->except(['show']);

        // Algoritma Safety Stock
        Route::controller(SafetyStockController::class)->group(function () {
            Route::get('/safety-stock', 'index')->name('ss.index');
            Route::post('/safety-stock/calculate', 'calculate')->name('ss.calculate');
            Route::get('/safety-stock/history/{type}/{id}', 'history')->name('ss.history');
            Route::post('/safety-stock/import-olsera', 'importOlseraReport')->name('ss.import-olsera');
        });

        // Laporan
        Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');

        // Manajemen Pesanan (Orders) - NEW
        Route::controller(AdminOrderController::class)->group(function () {
            Route::get('/orders', 'index')->name('orders.index');
            Route::get('/orders/{id}', 'show')->name('orders.show');
            Route::post('/orders/{id}/status', 'updateStatus')->name('orders.updateStatus');
            Route::post('/orders/{id}/ship', 'shipWithBiteship')->name('orders.ship');
            Route::get('/orders/export/csv', 'exportCSV')->name('orders.exportCSV');
        });

        // ✅ FITUR BARU #7: Pengaturan Admin
        Route::controller(SettingsController::class)->group(function () {
            Route::get('/settings', 'index')->name('settings.index');
            Route::post('/settings/general', 'updateGeneral')->name('settings.updateGeneral');
            Route::post('/settings/inventory', 'updateInventory')->name('settings.updateInventory');
            Route::post('/settings/sales', 'updateSales')->name('settings.updateSales');
            Route::post('/settings/payment', 'updatePayment')->name('settings.updatePayment');
            Route::post('/settings/shipping', 'updateShipping')->name('settings.updateShipping');
            Route::post('/settings/notification', 'updateNotification')->name('settings.updateNotification');
        });

        // ✅ INTEGRASI OLSERA POS & SINKRONISASI STOK
        Route::controller(\App\Http\Controllers\Admin\OlseraSyncController::class)->group(function () {
            Route::get('/olsera', 'index')->name('olsera.index');
            Route::post('/olsera/sync', 'sync')->name('olsera.sync');
            Route::post('/olsera/test-connection', 'testConnection')->name('olsera.test-connection');
            Route::post('/olsera/update-credentials', 'updateCredentials')->name('olsera.update-credentials');
            Route::post('/olsera/refresh-token', 'refreshToken')->name('olsera.refresh-token');
        });

        // ✅ MONITORING BACKGROUND QUEUE & FAILED JOBS
        Route::controller(\App\Http\Controllers\Admin\FailedJobController::class)->group(function () {
            Route::get('/failed-jobs', 'index')->name('failed-jobs.index');
            Route::post('/failed-jobs/{id}/retry', 'retry')->name('failed-jobs.retry');
            Route::post('/failed-jobs/retry-all', 'retryAll')->name('failed-jobs.retry-all');
            Route::delete('/failed-jobs/{id}', 'destroy')->name('failed-jobs.destroy');
            Route::post('/failed-jobs/flush', 'flush')->name('failed-jobs.flush');
        });

    }
);

// ✅ OLSERA POS REAL-TIME WEBHOOK INGESTION (Solusi 1: Instan < 1 Detik)
Route::match(['get', 'post'], '/api/olsera/webhook', [\App\Http\Controllers\OlseraWebhookController::class, 'handle'])
    ->middleware('olsera.webhook')
    ->name('api.olsera.webhook');

Route::match(['get', 'post'], '/api/webhook/olsera', [\App\Http\Controllers\OlseraWebhookController::class, 'handle'])
    ->middleware('olsera.webhook')
    ->name('api.webhook.olsera');

Route::match(['get', 'post'], '/webhook/olsera', [\App\Http\Controllers\OlseraWebhookController::class, 'handle'])
    ->middleware('olsera.webhook')
    ->name('webhook.olsera');

/*
|--------------------------------------------------------------------------
| BRANCH / CABANG ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:branch']) // Pastikan user login & rolenya branch
    ->prefix('branch')
    ->as('branch.')
    ->group(function () {
        
        // Halaman Dashboard Utama Cabang
        Route::get('/dashboard', [BranchController::class, 'dashboard'])->name('dashboard');

        // Fitur Request Stock (Form & Simpan)
        Route::get('/request-stock', [BranchController::class, 'index'])->name('request');
        Route::post('/request-stock', [BranchController::class, 'storeRequest'])->name('request.store');

        // Fitur Monitoring / Tracking
        Route::get('/tracking', [BranchController::class, 'tracking'])->name('tracking');
        Route::post('/tracking/{id}/confirm-received', [BranchController::class, 'confirmReceived'])->name('confirm-received');

    });