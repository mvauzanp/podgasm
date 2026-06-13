<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(10);
        return view('pages.admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('pages.admin.vouchers.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'code' => strtoupper(trim($request->code))
        ]);

        $request->validate([
            'code'         => 'required|string|max:50|unique:vouchers,code',
            'type'         => 'required|in:nominal,percentage,shipping_subsidy',
            'value'        => 'required|integer|min:1',
            'min_purchase' => 'required|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'quota'        => 'nullable|integer|min:1',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ], [
            'code.required' => 'Kode voucher harus diisi.',
            'code.unique'   => 'Kode voucher sudah digunakan.',
            'code.max'      => 'Kode voucher maksimal 50 karakter.',
            'value.required' => 'Nilai potongan harus diisi.',
            'value.min'      => 'Nilai potongan minimal 1.',
            'min_purchase.required' => 'Minimal pembelian harus diisi.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama dengan atau setelah tanggal mulai.',
        ]);

        // Clean values from dots (currency separator)
        $value = ($request->type === 'nominal' || $request->type === 'shipping_subsidy') ? str_replace('.', '', $request->value) : $request->value;
        $minPurchase = str_replace('.', '', $request->min_purchase);
        $maxDiscount = $request->max_discount ? str_replace('.', '', $request->max_discount) : null;

        Voucher::create([
            'code'         => $request->code,
            'type'         => $request->type,
            'value'        => $value,
            'min_purchase' => $minPurchase,
            'max_discount' => $maxDiscount,
            'quota'        => $request->quota,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function edit(Voucher $voucher)
    {
        return view('pages.admin.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $request->merge([
            'code' => strtoupper(trim($request->code))
        ]);

        $request->validate([
            'code'         => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'type'         => 'required|in:nominal,percentage,shipping_subsidy',
            'value'        => 'required|integer|min:1',
            'min_purchase' => 'required|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'quota'        => 'nullable|integer|min:1',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ], [
            'code.required' => 'Kode voucher harus diisi.',
            'code.unique'   => 'Kode voucher sudah digunakan.',
            'value.required' => 'Nilai potongan harus diisi.',
            'value.min'      => 'Nilai potongan minimal 1.',
            'min_purchase.required' => 'Minimal pembelian harus diisi.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama dengan atau setelah tanggal mulai.',
        ]);

        // Clean values from dots (currency separator)
        $value = ($request->type === 'nominal' || $request->type === 'shipping_subsidy') ? str_replace('.', '', $request->value) : $request->value;
        $minPurchase = str_replace('.', '', $request->min_purchase);
        $maxDiscount = $request->max_discount ? str_replace('.', '', $request->max_discount) : null;

        $voucher->update([
            'code'         => $request->code,
            'type'         => $request->type,
            'value'        => $value,
            'min_purchase' => $minPurchase,
            'max_discount' => $maxDiscount,
            'quota'        => $request->quota,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }
}
