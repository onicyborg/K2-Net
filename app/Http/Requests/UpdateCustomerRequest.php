<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers')->ignore($this->route('customer'))],
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_number_full' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:aktif,isolir,nonaktif',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'package_id.required' => 'Paket internet wajib dipilih.',
            'package_id.exists' => 'Paket internet tidak ditemukan.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
