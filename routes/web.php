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
use App\Http\Controllers\B2BRegistrationController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SafetyStockController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\StockRequestController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\PromoBannerController;
use App\Http\Controllers\Admin\B2BPriceSettingController;
use App\Http\Controllers\Admin\RestockController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\CsChatController;
use App\Http\Controllers\Branch\BranchController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/category/{slug}', [PublicController::class, 'categoryIndex'])->name('public.category');
Route::get('/product/{slug}', [PublicController::class, 'show'])->name('public.product.show'); // ✅ PERBAIKAN #6
Route::get('/search', [PublicController::class, 'search'])->name('public.search'); // ✅ PERBAIKAN #6

// ✅ B2B REGISTRATION - Public
Route::get('/b2b/register', [B2BRegistrationController::class, 'create'])->name('b2b.register');
Route::post('/b2b/register', [B2BRegistrationController::class, 'store'])->name('b2b.store');
Route::get('/b2b/pending', function() { return view('pages.public.b2b-pending'); })->name('b2b.pending')->middleware('auth');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/b2b', [AuthController::class, 'registerB2B'])->name('register.b2b');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| CART & CHECKOUT - PROTECTED WITH AUTH
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('cart')
    ->name('cart.')
    ->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::get('/offcanvas', [CartController::class, 'offcanvas'])->name('offcanvas');
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
    Route::post('/checkout/apply-voucher', [CheckoutController::class, 'applyVoucher'])->name('cart.applyVoucher');
    Route::post('/checkout/remove-voucher', [CheckoutController::class, 'removeVoucher'])->name('cart.removeVoucher');
    Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('cart.processCheckout');
    Route::get('/shipping/search-areas', [ShippingController::class, 'searchShippingAreas'])->name('shipping.searchAreas');
    Route::post('/shipping/rates', [ShippingController::class, 'getShippingRates'])->name('shipping.rates');
    Route::get('/history', [OrderController::class, 'history'])->name('order.history');
    Route::get('/history/{id}', [OrderController::class, 'show'])->name('order.show');
    
    // ✅ PERBAIKAN #4: Payment confirmation & order cancellation routes
    Route::post('/history/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('order.confirmPayment');
    Route::post('/history/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    
    // ✅ FITUR TRACKING
    Route::get('/history/{id}/track', [OrderController::class, 'track'])->name('order.track');
    
    // ✅ PERBAIKAN #6.3: User profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ✅ FITUR LIVE CHAT CS (B2C & B2B Reseller)
    Route::get('/chat/messages', [CsChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::post('/chat/messages', [CsChatController::class, 'sendMessage'])->name('chat.send');
});

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
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('review.store');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.') // INI KUNCINYA! Biar semua route di bawah otomatis punya prefix 'admin.'
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Manajemen Kategori
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Manajemen Produk
        Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::resource('products', ProductController::class)->except(['show']);

        // Manajemen Barang Masuk (Restock)
        Route::resource('restocks', RestockController::class);

        // Manajemen Banner Promo
        Route::resource('banners', PromoBannerController::class);

        // Setting Harga B2B
        Route::resource('b2b-prices', B2BPriceSettingController::class)->except(['show']);

        // Manajemen Voucher B2C
        Route::resource('vouchers', VoucherController::class)->except(['show']);

        // Fitur Live Chat CS (Admin Backoffice)
        Route::controller(CsChatController::class)->group(function () {
            Route::get('/cs-chats', 'adminIndex')->name('cs-chats.index');
            Route::get('/cs-chats/threads', 'adminFetchThreads')->name('cs-chats.threads');
            Route::get('/cs-chats/messages/{userId}', 'adminFetchMessages')->name('cs-chats.messages');
            Route::post('/cs-chats/messages/{userId}', 'adminSendMessage')->name('cs-chats.send');
        });

        // Algoritma Safety Stock
        Route::controller(SafetyStockController::class)->group(function () {
            Route::get('/safety-stock', 'index')->name('ss.index');
            Route::post('/safety-stock/calculate', 'calculate')->name('ss.calculate');
        });

        // Manajemen Permintaan Stok Cabang (Approval)
        Route::controller(StockRequestController::class)->group(function () {
            Route::get('/stock-requests', 'index')->name('stock-requests.index');
            Route::post('/stock-requests/{id}/approve', 'approve')->name('stock-requests.approve');
            Route::post('/stock-requests/{id}/reject', 'reject')->name('stock-requests.reject');
            Route::get('/stock-requests/order/{id}', 'showOrder')->name('stock-requests.show-order');
            Route::post('/stock-requests/order/{id}/update', 'updateOrder')->name('stock-requests.update-order');
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

        // ✅ B2B REGISTRATION APPROVAL - Admin only
        Route::controller(B2BRegistrationController::class)->group(function () {
            Route::get('/b2b-registrations', 'listPending')->name('b2b.list');
            Route::post('/b2b-registrations/{registration}/approve', 'approve')->name('b2b.approve');
            Route::post('/b2b-registrations/{registration}/reject', 'reject')->name('b2b.reject');
            Route::post('/b2b-registrations/branch', 'storeBranch')->name('b2b.storeBranch');
        });

    }
);

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

    });