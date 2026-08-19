<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            // Hanya dipakai untuk produk tawar-menawar; rentangnya diverifikasi ulang di CheckoutService.
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ];
    }
}
