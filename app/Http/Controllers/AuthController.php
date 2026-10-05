<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\B2BRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\SendRegistrationOtpMail;
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

            // 1. Generate 6 Digit Secure Random OTP
            $otp = (string) random_int(100000, 999999);

            // 2. Simpan Data Pendaftaran Sementara & Hash OTP ke Cache (TTL 5 Menit)
            $cacheKey = 'otp_reg_' . hash('sha256', $email);
            Cache::put($cacheKey, [
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make($request->password),
                'otp_hash'   => hash('sha256', $otp),
                'attempts'   => 0,
                'created_at' => now()->timestamp,
            ], now()->addMinutes(5));

            // 3. Kirim Email OTP via Resend / Mail Service
            try {
                Mail::to($email)->send(new SendRegistrationOtpMail($otp, $name));
            } catch (\Throwable $mailError) {
                Log::error('Resend OTP Mail Dispatch Failed: ' . $mailError->getMessage(), [
                    'email' => $email,
                    'exception' => $mailError,
                ]);

                return back()->withInput()->withErrors([
                    'email' => 'Gagal mengirim email verifikasi ke ' . $email . ': ' . $mailError->getMessage()
                ]);
            }

            // 4. Simpan email di session dan arahkan ke halaman verifikasi OTP
            session(['pending_verification_email' => $email]);

            return redirect()->route('register.verify.show')->with('success', 'Kode OTP 6 digit telah dikirim ke ' . $email);

        } catch (\Throwable $e) {
            Log::error('Register Process Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['msg' => 'Terjadi kesalahan sistem saat mendaftar: ' . $e->getMessage()]);
        }
    }

    /**
     * Tampilkan halaman verifikasi OTP
     */
    public function showVerifyOtp(Request $request)
    {
        $email = session('pending_verification_email');

        if (!$email) {
            return redirect()->route('register')->with('warning', 'Sesi verifikasi tidak ditemukan atau telah kedaluwarsa. Silakan daftar kembali.');
        }

        $cacheKey = 'otp_reg_' . hash('sha256', $email);
        if (!Cache::has($cacheKey)) {
            return redirect()->route('register')->with('warning', 'Kode OTP telah kedaluwarsa (lebih dari 5 menit). Silakan daftar ulang.');
        }

        return view('pages.auth.verify-otp', compact('email'));
    }

    /**
     * Proses verifikasi OTP dan commit user ke database
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP harus diisi',
            'otp.size'     => 'Kode OTP harus terdiri dari 6 digit angka',
        ]);

        $email = session('pending_verification_email');

        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran tidak ditemukan. Silakan daftar kembali.']);
        }

        $cacheKey = 'otp_reg_' . hash('sha256', $email);
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('register')->withErrors(['email' => 'Kode OTP telah kedaluwarsa. Silakan lakukan pendaftaran ulang.']);
        }

        // Cek batasan percobaan salah (Maksimal 5 kali percobaan)
        $attempts = (int) ($cachedData['attempts'] ?? 0);
        if ($attempts >= 5) {
            Cache::forget($cacheKey);
            session()->forget('pending_verification_email');
            return redirect()->route('register')->withErrors(['email' => 'Anda telah melebihi batas 5 kali percobaan. Silakan daftar ulang.']);
        }

        // Validasi kecocokan Hash OTP dengan timing-safe comparison
        $inputHash = hash('sha256', trim($request->otp));
        if (!hash_equals($cachedData['otp_hash'], $inputHash)) {
            $cachedData['attempts'] = $attempts + 1;
            $remainingAttempts = 5 - $cachedData['attempts'];
            Cache::put($cacheKey, $cachedData, now()->addMinutes(5));

            return back()->withErrors(['otp' => "Kode OTP salah. Sisa kesempatan: {$remainingAttempts} kali."]);
        }

        // OTP Valid -> DB Transaction untuk membuat user baru secara atomik
        try {
            $user = DB::transaction(function () use ($cachedData) {
                return User::create([
                    'name'              => $cachedData['name'],
                    'email'             => $cachedData['email'],
                    'password'          => $cachedData['password'],
                    'role'              => 'customer',
                    'email_verified_at' => now(),
                ]);
            });

            // Bersihkan data cache & session
            Cache::forget($cacheKey);
            session()->forget('pending_verification_email');

            // Otomatis login
            Auth::login($user);

            return redirect('/')->with('success', 'Selamat datang di Podgasm! Akun Anda berhasil diverifikasi dan aktif.');

        } catch (\Throwable $e) {
            Log::error('DB User Creation After OTP Verification Failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['otp' => 'Gagal menyelesaikan pendaftaran akun: ' . $e->getMessage()]);
        }
    }

    /**
     * Kirim ulang kode OTP ke email pengguna
     */
    public function resendOtp(Request $request)
    {
        $email = session('pending_verification_email');

        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran tidak ditemukan. Silakan daftar kembali.']);
        }

        $cacheKey = 'otp_reg_' . hash('sha256', $email);
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran telah kedaluwarsa. Silakan daftar kembali.']);
        }

        // Cek cooldown 60 detik
        $cooldownKey = 'resend_cooldown_' . hash('sha256', $email);
        if (Cache::has($cooldownKey)) {
            return back()->with('warning', 'Harap tunggu 60 detik sebelum meminta kode OTP baru.');
        }

        try {
            // Generate OTP baru
            $otp = (string) random_int(100000, 999999);
            $cachedData['otp_hash'] = hash('sha256', $otp);
            $cachedData['attempts'] = 0; // Reset percobaan
            $cachedData['created_at'] = now()->timestamp;

            Cache::put($cacheKey, $cachedData, now()->addMinutes(5));
            Cache::put($cooldownKey, true, now()->addSeconds(60));

            // Kirim email
            Mail::to($email)->send(new SendRegistrationOtpMail($otp, $cachedData['name']));

            return back()->with('success', 'Kode OTP baru telah berhasil dikirim ke ' . $email);

        } catch (\Throwable $mailError) {
            Log::error('Resend OTP Error: ' . $mailError->getMessage(), ['exception' => $mailError]);
            return back()->withErrors(['msg' => 'Gagal mengirim ulang OTP: ' . $mailError->getMessage()]);
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
            
            // 2. Cek kalau dia Cabang Internal atau Reseller (B2B)
            if ($user->role == 'branch') {
                // Cek status approval di B2B registrations jika terdaftar sebagai reseller
                $b2bReg = B2BRegistration::where('user_id', $user->id)->first();
                
                if ($b2bReg && $b2bReg->status == 'pending') {
                    return redirect()->intended('b2b/pending')->with('warning', 'Akun Anda sedang dalam proses review admin.');
                }
                
                if ($b2bReg && $b2bReg->status == 'rejected') {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Aplikasi B2B Anda telah ditolak. Alasan: ' . $b2bReg->rejection_reason]);
                }
                
                // Jika akun Cabang Toko Internal -> Arahkan ke Portal Cabang
                if ($user->b2b_type === 'branch') {
                    return redirect()->intended(route('branch.dashboard'));
                }

                // Jika Reseller B2B Eksternal -> Lanjut ke katalog belanja
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