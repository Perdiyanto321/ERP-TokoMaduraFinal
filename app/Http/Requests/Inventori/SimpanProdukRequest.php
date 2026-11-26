<?php

namespace App\Http\Requests\Inventori;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'gudang']) ?? false;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', Rule::exists('kategori', 'id')],
            'kode' => ['required', 'string', 'max:30', Rule::unique('produk', 'kode')],
            'nama' => ['required', 'string', 'max:150'],
            'satuan' => ['required', 'string', 'max:30'],
            'satuan_beli' => ['required', 'string', 'max:30'],
            'isi_per_satuan_beli' => ['required', 'integer', 'min:1'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'stok_awal' => ['nullable', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'stok_target' => ['required', 'integer', 'gte:stok_minimum'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'stok_target.gte' => 'Stok target tidak boleh lebih kecil dari stok minimum.',
            'isi_per_satuan_beli.min' => 'Isi per satuan beli minimal 1.',
        ];
    }
}
