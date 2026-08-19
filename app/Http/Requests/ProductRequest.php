<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isNegotiable = $this->boolean('is_negotiable');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            // Produk tawar-menawar memakai rentang harga, bukan harga tunggal.
            'price' => [Rule::requiredIf(!$isNegotiable), 'nullable', 'numeric', 'min:0'],
            'is_negotiable' => ['nullable', 'boolean'],
            'min_price' => [Rule::requiredIf($isNegotiable), 'nullable', 'numeric', 'min:0'],
            'max_price' => [Rule::requiredIf($isNegotiable), 'nullable', 'numeric', 'min:0', 'gte:min_price'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'stock_badge' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($this->hasFile('photo')) {
            $rules['photo'] = ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'];
        } else {
            $rules['photo'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Atribut harga yang siap disimpan.
     * Produk tawar-menawar memakai harga tertinggi sebagai harga acuan yang
     * tampil di katalog, sedangkan rentangnya dipakai kasir saat nego.
     */
    public function priceAttributes(): array
    {
        if (!$this->boolean('is_negotiable')) {
            return [
                'price' => $this->input('price'),
                'is_negotiable' => false,
                'min_price' => null,
                'max_price' => null,
            ];
        }

        return [
            'price' => $this->input('max_price'),
            'is_negotiable' => true,
            'min_price' => $this->input('min_price'),
            'max_price' => $this->input('max_price'),
        ];
    }

    public function messages(): array
    {
        return [
            'min_price.required' => 'Harga terendah wajib diisi untuk produk yang bisa ditawar.',
            'max_price.required' => 'Harga tertinggi wajib diisi untuk produk yang bisa ditawar.',
            'max_price.gte' => 'Harga tertinggi tidak boleh lebih kecil dari harga terendah.',
        ];
    }
}
