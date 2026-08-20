<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrisWithoutProofRequest extends FormRequest
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
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            // Alasan bukti tidak terunggah, dicatat untuk EO.
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan bukti tidak terunggah wajib diisi.',
        ];
    }
}
