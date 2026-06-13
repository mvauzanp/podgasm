<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\B2BRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('pages.auth.register');
    }

    public function register(Request $request)
    {
        // ✅ Validasi Input untuk B2C (Customer)
        $request->validate([
            'name'     => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
            ],
            'email'    => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._\-+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            ],
        ], [
            'name.required' => 'Nama harus diisi',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, dan tanda baca',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar di sistem',
            'email.regex' => 'Format email tidak sesuai',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
        ]);

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
    public function registerB2B(Request $request)
    {
        // ✅ Validasi Input untuk B2B (Reseller)
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'email'      => [
                'required',
                'email',
                'max:255',
                'unique:users',
                'unique:b2b_registrations',
            ],
            'phone'      => 'required|string|max:20',
            'address'    => 'required|string|max:1000',
            'password'   => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            ],
            'ktp_file'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'storefront_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'owner_name.required' => 'Nama pemilik harus diisi',
            'store_name.required' => 'Nama toko harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar di sistem',
            'phone.required' => 'Telepon harus diisi',
            'address.required' => 'Alamat harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
            'ktp_file.required' => 'File KTP harus diunggah',
            'ktp_file.mimes' => 'Format KTP harus PDF, JPG, atau PNG',
            'ktp_file.max' => 'Ukuran KTP maksimal 5MB',
            'storefront_photo.required' => 'Foto toko harus diunggah',
            'storefront_photo.mimes' => 'Format foto harus JPG atau PNG',
            'storefront_photo.max' => 'Ukuran foto maksimal 5MB',
        ]);

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

    public function login(Request $request)
    {
        // ✅ Validasi input dengan rules yang komprehensif
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._\-+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.regex' => 'Format email tidak sesuai',
            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 8 karakter',
            'password.max' => 'Password maksimal 255 karakter',
        ]);

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