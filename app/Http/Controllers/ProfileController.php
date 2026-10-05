<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;

class ProfileController extends Controller
{
    /**
     * Tampilkan profil user yang sedang login
     */
    public function show()
    {
        $user = Auth::user();
        
        // Hitung cart count dan wishlist count untuk navbar
        $cart = \App\Models\Cart::where('user_id', Auth::id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;

        // Hitung statistik status pesanan user real-time
        $ordersQuery = Order::where('user_id', Auth::id());
        $totalOrdersCount = (clone $ordersQuery)->count();
        $pendingPaymentCount = (clone $ordersQuery)->whereIn('status', [\App\Enums\OrderStatus::PENDING_PAYMENT->value, \App\Enums\OrderStatus::PENDING->value, 'unpaid'])->count();
        $processingCount = (clone $ordersQuery)->whereIn('status', [\App\Enums\OrderStatus::PAID->value, 'processing', 'packing'])->count();
        $shippingCount = (clone $ordersQuery)->whereIn('status', [\App\Enums\OrderStatus::SHIPPED->value, 'in_transit'])->count();
        $completedCount = (clone $ordersQuery)->whereIn('status', [\App\Enums\OrderStatus::COMPLETED->value, 'delivered'])->count();

        // Ambil daftar multi-alamat user
        $addresses = $user->addresses()->orderByDesc('is_primary')->latest()->get();

        return view('pages.frontend.profile', compact(
            'user', 
            'cartCount', 
            'wishlistCount',
            'totalOrdersCount',
            'pendingPaymentCount',
            'processingCount',
            'shippingCount',
            'completedCount',
            'addresses'
        ));
    }

    /**
     * Tampilkan form edit profil
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Hitung cart count dan wishlist count untuk navbar
        $cart = \App\Models\Cart::where('user_id', Auth::id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        $wishlistCount = session()->get('wishlist') ? count(session()->get('wishlist')) : 0;
        $addresses = $user->addresses()->orderByDesc('is_primary')->latest()->get();

        return view('pages.frontend.profile-edit', compact('user', 'cartCount', 'wishlistCount', 'addresses'));
    }

    /**
     * Update data profil user (Nama, Email, HP, Alamat)
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('profile.show')->with('success', 'Profil Anda berhasil diperbarui!');
    }

    /**
     * Simpan alamat pengiriman baru terintegrasi Biteship
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'biteship_area_id' => 'required|string|max:100',
            'biteship_area_name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_primary' => 'nullable',
        ], [
            'label.required' => 'Label alamat (misal: Rumah / Kantor) wajib diisi.',
            'recipient_name.required' => 'Nama penerima paket wajib diisi.',
            'phone.required' => 'Nomor telepon penerima wajib diisi.',
            'address.required' => 'Alamat jalan / nomor rumah lengkap wajib diisi.',
            'biteship_area_id.required' => 'Kota/Kecamatan wajib dipilih dari daftar pencarian.',
        ]);

        $user = Auth::user();
        $hasAddresses = $user->addresses()->exists();
        $isPrimary = $request->boolean('is_primary') || !$hasAddresses;

        if ($isPrimary) {
            $user->addresses()->update(['is_primary' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'biteship_area_id' => $request->biteship_area_id,
            'biteship_area_name' => $request->biteship_area_name,
            'postal_code' => $request->postal_code,
            'latitude' => $request->filled('latitude') ? (float) $request->latitude : null,
            'longitude' => $request->filled('longitude') ? (float) $request->longitude : null,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $user->update([
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        }

        return redirect()->back()->with('success', "Alamat '{$address->label}' berhasil ditambahkan ke buku alamat Anda!");
    }

    /**
     * Update alamat pengiriman yang sudah ada
     */
    public function updateAddress(Request $request, $id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'biteship_area_id' => 'required|string|max:100',
            'biteship_area_name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_primary' => 'nullable',
        ]);

        $isPrimary = $request->boolean('is_primary');
        if ($isPrimary && !$address->is_primary) {
            Auth::user()->addresses()->where('id', '!=', $id)->update(['is_primary' => false]);
        }

        $address->update([
            'label' => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'biteship_area_id' => $request->biteship_area_id,
            'biteship_area_name' => $request->biteship_area_name,
            'postal_code' => $request->postal_code,
            'latitude' => $request->filled('latitude') ? (float) $request->latitude : $address->latitude,
            'longitude' => $request->filled('longitude') ? (float) $request->longitude : $address->longitude,
            'is_primary' => $isPrimary || $address->is_primary,
        ]);

        if ($address->is_primary) {
            Auth::user()->update([
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
        }

        return redirect()->back()->with('success', "Alamat '{$address->label}' berhasil diperbarui!");
    }

    /**
     * Hapus alamat dari buku alamat
     */
    public function deleteAddress($id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        $wasPrimary = $address->is_primary;
        $label = $address->label;
        $address->delete();

        if ($wasPrimary) {
            $nextPrimary = Auth::user()->addresses()->first();
            if ($nextPrimary) {
                $nextPrimary->update(['is_primary' => true]);
                Auth::user()->update([
                    'phone' => $nextPrimary->phone,
                    'address' => $nextPrimary->address,
                ]);
            }
        }

        return redirect()->back()->with('success', "Alamat '{$label}' berhasil dihapus dari buku alamat.");
    }

    /**
     * Jadikan alamat sebagai alamat utama (Primary)
     */
    public function setPrimaryAddress($id)
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        Auth::user()->addresses()->update(['is_primary' => false]);
        $address->update(['is_primary' => true]);

        Auth::user()->update([
            'phone' => $address->phone,
            'address' => $address->address,
        ]);

        return redirect()->back()->with('success', "Alamat '{$address->label}' sekarang menjadi alamat pengiriman utama.");
    }

    /**
     * Update password user mandiri
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('profile.show')->with('success', 'Password Anda berhasil diperbarui! Gunakan password baru untuk login berikutnya.');
    }
}
