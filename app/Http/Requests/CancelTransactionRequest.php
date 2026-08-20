<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('reason_category') && $this->has('cancellation_reason')) {
            $this->merge([
                'reason_category' => $this->input('cancellation_reason'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'reason_category' => ['required', 'string', 'max:255'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
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
