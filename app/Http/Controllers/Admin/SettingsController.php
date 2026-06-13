<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * ✅ Menampilkan halaman pengaturan admin
     */
    public function index()
    {
        return view('pages.admin.settings');
    }

    /**
     * ✅ Update pengaturan umum sistem
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
        ]);

        // Simpan ke config atau environment
        foreach ($validated as $key => $value) {
            config([
                'settings.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan umum berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan inventory/stok
     */
    public function updateInventory(Request $request)
    {
        $validated = $request->validate([
            'safety_stock_percentage' => 'required|numeric|min:5|max:50',
            'low_stock_alert_days' => 'required|numeric|min:1|max:90',
            'expire_warning_days' => 'required|numeric|min:1|max:365',
            'auto_reorder_enabled' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            config([
                'inventory.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan inventory berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan penjualan
     */
    public function updateSales(Request $request)
    {
        $validated = $request->validate([
            'min_order_amount' => 'required|numeric|min:0',
            'max_order_amount' => 'required|numeric|min:1000',
            'discount_max_percentage' => 'required|numeric|min:0|max:100',
            'enable_promo' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            config([
                'sales.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan penjualan berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan pembayaran
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'payment_timeout_hours' => 'required|numeric|min:1|max:72',
            'auto_confirm_payment' => 'boolean',
            'payment_methods' => 'required|array|min:1',
        ]);

        foreach ($validated as $key => $value) {
            config([
                'payment.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan pembayaran berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan pengiriman
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'enable_shipping_integration' => 'boolean',
            'default_shipping_provider' => 'required|string|in:jne,tiki,pos,fedex',
            'free_shipping_amount' => 'required|numeric|min:0',
            'default_shipping_cost' => 'required|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            config([
                'shipping.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan pengiriman berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan notifikasi
     */
    public function updateNotification(Request $request)
    {
        $validated = $request->validate([
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
            'notify_low_stock' => 'boolean',
            'notify_pending_orders' => 'boolean',
            'notify_payment_confirmed' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            config([
                'notification.' . $key => $value
            ]);
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan notifikasi berhasil diupdate!');
    }
}
