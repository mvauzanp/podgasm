<?php

namespace App\Http\Controllers;

use App\Models\B2BRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * List pending B2B registrations (Admin)
     */
    public function listPending()
    {
        $this->authorizeAdmin();

        $registrations = B2BRegistration::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.b2b-registrations', ['registrations' => $registrations]);
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
