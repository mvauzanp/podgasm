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

        $this->saveSettings('settings', $validated);

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
        ]);

        $validated['auto_reorder_enabled'] = $request->has('auto_reorder_enabled');

        $this->saveSettings('inventory', $validated);

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
        ]);

        $validated['enable_promo'] = $request->has('enable_promo');

        $this->saveSettings('sales', $validated);

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
            'payment_methods' => 'required|array|min:1',
        ]);

        $validated['auto_confirm_payment'] = $request->has('auto_confirm_payment');

        $this->saveSettings('payment', $validated);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan pembayaran berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan pengiriman
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'default_shipping_provider' => 'required|string|in:jne,tiki,pos,fedex',
            'free_shipping_amount' => 'required|numeric|min:0',
            'default_shipping_cost' => 'required|numeric|min:0',
        ]);

        $validated['enable_shipping_integration'] = $request->has('enable_shipping_integration');

        $this->saveSettings('shipping', $validated);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan pengiriman berhasil diupdate!');
    }

    /**
     * ✅ Update pengaturan notifikasi
     */
    public function updateNotification(Request $request)
    {
        $validated = [];
        $validated['email_notifications_enabled'] = $request->has('email_notifications_enabled');
        $validated['sms_notifications_enabled'] = $request->has('sms_notifications_enabled');
        $validated['notify_low_stock'] = $request->has('notify_low_stock');
        $validated['notify_pending_orders'] = $request->has('notify_pending_orders');
        $validated['notify_payment_confirmed'] = $request->has('notify_payment_confirmed');

        $this->saveSettings('notification', $validated);

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Pengaturan notifikasi berhasil diupdate!');
    }

    /**
     * Helper untuk menyimpan pengaturan secara persisten ke JSON file
     */
    private function saveSettings(string $group, array $values)
    {
        $settingsFile = storage_path('app/settings.json');
        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        // Khusus untuk general setting, jika ada app_name kita juga simpan ke grup 'app'
        if ($group === 'settings') {
            if (isset($values['app_name'])) {
                $settings['app']['name'] = $values['app_name'];
            }
            if (isset($values['app_description'])) {
                $settings['app']['description'] = $values['app_description'];
            }
        }

        $settings[$group] = array_merge($settings[$group] ?? [], $values);

        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        // Terapkan langsung ke config runtime
        foreach ($settings as $g => $valArray) {
            if (is_array($valArray)) {
                foreach ($valArray as $k => $v) {
                    config([$g . '.' . $k => $v]);
                }
            }
        }
    }
}
