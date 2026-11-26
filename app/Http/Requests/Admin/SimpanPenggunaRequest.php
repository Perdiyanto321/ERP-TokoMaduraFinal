<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:120'],
            'jabatan' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('users', 'username')],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }
}
