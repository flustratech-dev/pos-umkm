<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
            //
            // Aturan 'image' bawaan Laravel hanya menerima jpg/jpeg/png/gif/bmp/webp,
            // sehingga foto HEIC/HEIF bawaan iPhone selalu ditolak. Formatnya
            // diterima di sini supaya bukti tetap tersimpan walau browser tenant
            // gagal mengubahnya jadi JPEG.
            'proof_image' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,bmp,webp,heic,heif', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof_image.required' => 'Bukti transfer QRIS wajib diunggah sebelum transaksi disimpan.',
            'proof_image.mimes' => 'Format bukti transfer tidak didukung. Gunakan screenshot (JPG/PNG) dari aplikasi pembayaran.',
            'proof_image.max' => 'Ukuran bukti transfer maksimal 10 MB. Gunakan screenshot, bukan foto layar.',
        ];
    }

    /**
     * Catat detail berkas yang ditolak, supaya penyebab kegagalan di lapangan
     * bisa dilacak dari log produksi (format? ukuran? tidak terkirim sama sekali?).
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('proof_image')) {
            $file = $this->file('proof_image');

            Log::warning('Bukti QRIS ditolak', [
                'store_id' => $this->user()?->store_id,
                'cashier_id' => $this->user()?->id,
                'errors' => $validator->errors()->get('proof_image'),
                'file_ada' => $file !== null,
                'nama' => $file?->getClientOriginalName(),
                'mime_klien' => $file?->getClientMimeType(),
                'mime_terdeteksi' => $file !== null && $file->isValid() ? $file->getMimeType() : null,
                'ukuran_kb' => $file !== null && $file->isValid() ? round($file->getSize() / 1024) : null,
                'upload_error' => $file?->getError(),
                'content_length' => $this->header('Content-Length'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ]);
        }

        parent::failedValidation($validator);
    }
}
