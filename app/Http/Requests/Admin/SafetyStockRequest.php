<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SafetyStockRequest extends FormRequest
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
            'lead_time' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'lead_time.required' => 'Lead time harus diisi.',
            'lead_time.numeric' => 'Lead time harus berupa angka.',
            'lead_time.min' => 'Lead time tidak boleh negatif.',
        ];
    }
}
