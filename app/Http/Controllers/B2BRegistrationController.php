<?php

namespace App\Http\Controllers;

use App\Models\B2BRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class B2BRegistrationController extends Controller
{
    /**
     * Show B2B registration form
     */
    public function create()
    {
        return view('pages.public.b2b-register');
    }

    /**
     * Store B2B registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:b2b_registrations',
            'ktp_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'storefront_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        // Handle file uploads
        $ktpPath = $request->file('ktp_file')->store('b2b/ktp', 'public');
        $storefrontPath = $request->file('storefront_photo')->store('b2b/storefront', 'public');

        // Create registration record (status = pending)
        $registration = B2BRegistration::create([
            'owner_name' => $validated['owner_name'],
            'store_name' => $validated['store_name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'ktp_file' => $ktpPath,
            'storefront_photo' => $storefrontPath,
            'status' => 'pending',
        ]);

        return redirect()->route('b2b.register')->with('success', 
            'Aplikasi B2B Anda telah diterima! Admin akan melakukan review dalam 1-3 hari kerja.');
    }

    /**
     * List B2B users and registrations (Admin)
     */
    public function listPending(Request $request)
    {
        $this->authorizeAdmin();

        $activeTab = $request->query('tab', 'reseller');

        // B2B registrations applications (filtered by reseller types or null)
        $registrations = B2BRegistration::where(function($query) {
            $query->whereHas('user', function($q) {
                $q->where('b2b_type', 'reseller');
            })->orWhereNull('user_id');
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15, ['*'], 'reg_page');

        // List of active resellers (Users)
        $activeResellers = User::where('role', 'branch')
            ->where('b2b_type', 'reseller')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'reseller_page');

        // List of branch/cabang accounts (Users)
        $branchAccounts = User::where('role', 'branch')
            ->where('b2b_type', 'branch')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'branch_page');

        return view('admin.b2b-registrations', compact(
            'registrations', 
            'activeResellers', 
            'branchAccounts',
            'activeTab'
        ));
    }

    /**
     * Create branch account directly by Admin
     */
    public function storeBranch(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'unique:b2b_registrations',
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:1000',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama cabang harus diisi',
            'owner_name.required' => 'Nama penanggung jawab harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'phone.required' => 'Telepon harus diisi',
            'address.required' => 'Alamat harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        // 1. Create user with role branch, b2b_type branch
        $user = User::create([
            'name' => strip_tags(trim($request->owner_name)),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => 'branch',
            'b2b_type' => 'branch',
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // 2. Create approved B2BRegistration for consistency
        B2BRegistration::create([
            'user_id' => $user->id,
            'owner_name' => strip_tags(trim($request->owner_name)),
            'store_name' => strip_tags(trim($request->name)),
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => 'Dibuat langsung oleh Admin.',
        ]);

        return redirect()->route('admin.b2b.list', ['tab' => 'branch'])->with('success', 'Akun Cabang baru berhasil dibuat!');
    }

    /**
     * Approve B2B registration (Admin)
     */
    public function approve(B2BRegistration $registration)
    {
        $this->authorizeAdmin();

        $registration->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // TODO: Send approval email to applicant
        // TODO: Create reseller account / upgrade existing account

        return back()->with('success', 'Aplikasi B2B disetujui!');
    }

    /**
     * Reject B2B registration (Admin)
     */
    public function reject(B2BRegistration $registration, Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // TODO: Send rejection email to applicant with reason

        return back()->with('success', 'Aplikasi B2B ditolak!');
    }

    /**
     * Helper: Check if user is admin
     */
    private function authorizeAdmin()
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return true;
        }

        abort(403, 'Akses ditolak - Khusus Admin.');
    }
}
