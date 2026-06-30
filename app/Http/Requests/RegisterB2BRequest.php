<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterB2BRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
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
        ];
    }
}
