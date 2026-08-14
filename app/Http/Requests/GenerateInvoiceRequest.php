<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'          => 'required|uuid|exists:customers,id',
            'billing_period_start' => 'required|date|before_or_equal:billing_period_end',
            'billing_period_end'   => 'required|date|after_or_equal:billing_period_start',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'           => 'Pelanggan wajib dipilih.',
            'customer_id.uuid'               => 'ID pelanggan tidak valid.',
            'customer_id.exists'             => 'Pelanggan tidak ditemukan.',
            'billing_period_start.required'  => 'Periode awal wajib diisi.',
            'billing_period_start.before_or_equal' => 'Periode awal tidak boleh lebih besar dari periode akhir.',
            'billing_period_end.required'    => 'Periode akhir wajib diisi.',
            'billing_period_end.after_or_equal'   => 'Periode akhir tidak boleh lebih kecil dari periode awal.',
        ];
    }
}
