<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrisCheckoutRequest extends FormRequest
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
            // Bukti transfer wajib: transaksi QRIS langsung berstatus lunas,
            // jadi harus ada arsip yang bisa dicek EO saat rekonsiliasi.
            'proof_image' => ['required', 'image', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof_image.required' => 'Bukti transfer QRIS wajib diunggah sebelum transaksi disimpan.',
            'proof_image.image' => 'Bukti transfer harus berupa gambar.',
            'proof_image.max' => 'Ukuran bukti transfer maksimal 10 MB.',
        ];
    }
}
