<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $b2bRegistration = \App\Models\B2BRegistration::where('user_id', Auth::id())->first();

        return view('pages.frontend.profile', compact('user', 'cartCount', 'wishlistCount', 'b2bRegistration'));
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
     * Update profil user
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

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');
    }
}
