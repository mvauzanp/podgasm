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
        $pendingPaymentCount = (clone $ordersQuery)->whereIn('status', ['unpaid', 'pending'])->count();
        $processingCount = (clone $ordersQuery)->whereIn('status', ['paid', 'processing', 'packing'])->count();
        $shippingCount = (clone $ordersQuery)->whereIn('status', ['shipped', 'in_transit'])->count();
        $completedCount = (clone $ordersQuery)->whereIn('status', ['delivered', 'completed'])->count();

        return view('pages.frontend.profile', compact(
            'user', 
            'cartCount', 
            'wishlistCount',
            'totalOrdersCount',
            'pendingPaymentCount',
            'processingCount',
            'shippingCount',
            'completedCount'
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

        return view('pages.frontend.profile-edit', compact('user', 'cartCount', 'wishlistCount'));
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
