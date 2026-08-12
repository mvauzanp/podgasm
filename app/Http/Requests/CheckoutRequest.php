<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Tentukan apakah user terotorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk request ini.
     */
    public function rules(): array
    {
        return [
            'nama_penerima' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\.\']+$/', // Hanya huruf, spasi, dash, dot, apostrof
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._\-@]+$/', // Validasi format email ketat
            ],
            'no_telp' => [
                'required',
                'numeric',
                'digits_between:10,15', // 10-15 digit
                'regex:/^(0|62)[0-9]{9,13}$/', // Nomor lokal atau internasional
            ],
            'alamat_pengiriman' => [
                'required',
                'string',
                'min:10',
                'max:500',
                'regex:/^[a-zA-Z0-9\s\.\,\-\(\)]+$/', // Alamat format valid
            ],
            'metode_pembayaran' => [
                'required',
                'string',
                'in:midtrans,midtrans_va,midtrans_qris,midtrans_cc,cash,transfer,e-wallet,branch_request',
            ],
            'destination_area_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kurir' => [
                'nullable',
                'string',
                'max:100',
            ],
            'layanan' => [
                'nullable',
                'string',
                'max:100',
            ],
            'ongkir' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'age_confirmation' => [
                'accepted',
            ],
        ];
    }

    /**
     * Pesan kesalahan kustom untuk validasi request ini.
     */
    public function messages(): array
    {
        return [
            'nama_penerima.required' => 'Nama penerima harus diisi',
            'nama_penerima.string' => 'Nama penerima harus berupa teks',
            'nama_penerima.max' => 'Nama penerima maksimal 255 karakter',
            'nama_penerima.regex' => 'Nama penerima hanya boleh berisi huruf, spasi, dan tanda baca',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.regex' => 'Format email tidak sesuai',
            'no_telp.required' => 'Nomor telepon harus diisi',
            'no_telp.numeric' => 'Nomor telepon harus berupa angka',
            'no_telp.digits_between' => 'Nomor telepon harus antara 10-15 digit',
            'no_telp.regex' => 'Nomor telepon harus dimulai dengan 0 atau 62',
            'alamat_pengiriman.required' => 'Alamat pengiriman harus diisi',
            'alamat_pengiriman.string' => 'Alamat pengiriman harus berupa teks',
            'alamat_pengiriman.min' => 'Alamat pengiriman minimal 10 karakter',
            'alamat_pengiriman.max' => 'Alamat pengiriman maksimal 500 karakter',
            'alamat_pengiriman.regex' => 'Alamat pengiriman berisi karakter tidak valid',
            'metode_pembayaran.required' => 'Metode pembayaran harus dipilih',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid',
            'age_confirmation.accepted' => 'Anda wajib mengonfirmasi bahwa berusia 21 tahun ke atas untuk melakukan transaksi.',
        ];
    }
}
