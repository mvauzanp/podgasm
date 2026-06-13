<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StockApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tgl_estimasi' => 'required|date|after_or_equal:today',
            'jumlah' => 'required|integer|min:1',
            'keterangan_admin' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_estimasi.required' => 'Tanggal estimasi pengiriman harus diisi.',
            'tgl_estimasi.date' => 'Format tanggal estimasi tidak valid.',
            'tgl_estimasi.after_or_equal' => 'Tanggal estimasi tidak boleh kurang dari hari ini.',
            'jumlah.required' => 'Jumlah barang yang disetujui harus diisi.',
            'jumlah.integer' => 'Jumlah barang harus berupa angka bulat.',
            'jumlah.min' => 'Jumlah barang minimal 1 unit.',
        ];
    }
}
