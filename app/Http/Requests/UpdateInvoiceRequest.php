<?php

namespace App\Http\Requests;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'           => 'sometimes|integer|min:0|max:999999999999',
            'due_date'         => 'sometimes|date|after_or_equal:today',
            'status'           => ['sometimes', Rule::in(array_column(InvoiceStatus::cases(), 'value'))],
            'rejection_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.integer'        => 'Jumlah tagihan harus berupa angka.',
            'amount.min'            => 'Jumlah tagihan tidak boleh kurang dari 0.',
            'due_date.date'         => 'Tanggal jatuh tempo tidak valid.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum hari ini.',
            'status.in'             => 'Status tidak valid.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
