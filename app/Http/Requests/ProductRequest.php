<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
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
}
