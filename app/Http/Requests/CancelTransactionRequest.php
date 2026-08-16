<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason_category' => ['required', 'string', 'max:255'],
            'custom_note' => ['nullable', 'string', 'max:1000'],
            'refund_ack_confirmed' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason_category.required' => 'Kategori alasan pembatalan wajib dipilih.',
            'refund_ack_confirmed.accepted' => 'Anda wajib mencentang konfirmasi kesepakatan refund manual sebelum melanjutkan pembatalan.',
        ];
    }
}
