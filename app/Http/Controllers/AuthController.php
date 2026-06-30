<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\B2BRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterB2BRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('pages.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        try {
            $name = strip_tags(trim($request->name));
            $email = strtolower(trim($request->email));

            // Buat user dengan role customer
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($request->password),
                'role'     => 'customer',
            ]);

            // Otomatis login
            Auth::login($user);

            return redirect('/')->with('success', 'Selamat datang di Podgasm!');

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal mendaftar: ' . $e->getMessage()]);
        }
    }

    /**
     * Register B2B / Reseller
     */
    public function registerB2B(RegisterB2BRequest $request)
    {
        try {
            // Upload files
            $ktpPath = $request->file('ktp_file')->store('b2b/ktp', 'public');
            $storefrontPath = $request->file('storefront_photo')->store('b2b/storefront', 'public');

            $email = strtolower(trim($request->email));
            $ownerName = strip_tags(trim($request->owner_name));
            $storeName = strip_tags(trim($request->store_name));

            // 1. Buat user dengan role branch
            $user = User::create([
                'name'     => $ownerName,
                'email'    => $email,
                'password' => Hash::make($request->password),
                'role'     => 'branch',
                'b2b_type' => 'reseller',
                'phone'    => $request->phone,
                'address'  => $request->address,
            ]);

            // 2. Buat B2B registration dengan status pending
            B2BRegistration::create([
                'user_id'           => $user->id,
                'owner_name'        => $ownerName,
                'store_name'        => $storeName,
                'email'             => $email,
                'phone'             => $request->phone,
                'address'           => $request->address,
                'ktp_file'          => $ktpPath,
                'storefront_photo'  => $storefrontPath,
                'status'            => 'pending',
            ]);

            // 3. Otomatis login
            Auth::login($user);

            return redirect('/b2b/pending')->with('info', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan admin. Anda akan menerima email notifikasi setelah admin melakukan review.');

        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal mendaftar: ' . $e->getMessage()]);
        }
    }

    public function showLogin()
    {
        return view('pages.auth.login');
    }

    public function login(LoginRequest $request)
    {
        // ✅ Ambil input terverifikasi
        $credentials = $request->validated();

        // ✅ Normalisasi email input
        $credentials['email'] = strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user(); // Ambil data user yang login

            // 1. Cek kalau dia Admin
            if ($user->role == 'admin') {
                return redirect()->intended('admin/dashboard');
            } 
            
            // 2. Cek kalau dia Reseller (B2B)
            if ($user->role == 'branch') {
                // Cek status approval di B2B registrations
                $b2bReg = B2BRegistration::where('user_id', $user->id)->first();
                
                if ($b2bReg && $b2bReg->status == 'pending') {
                    return redirect()->intended('b2b/pending')->with('warning', 'Akun Anda sedang dalam proses review admin.');
                }
                
                if ($b2bReg && $b2bReg->status == 'rejected') {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Aplikasi B2B Anda telah ditolak. Alasan: ' . $b2bReg->rejection_reason]);
                }
                
                // Jika approved, bisa lanjut
                return redirect()->intended('/');
            }
            
            // 3. Sisanya (Customer) dilempar ke Home
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}