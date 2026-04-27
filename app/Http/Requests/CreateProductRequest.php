<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'seller';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk harus diisi',
            'price.required' => 'Harga harus diisi',
            'price.min' => 'Harga harus lebih dari 0',
            'stock.required' => 'Stok harus diisi',
            'stock.min' => 'Stok tidak boleh negatif',
        ];
    }
}
