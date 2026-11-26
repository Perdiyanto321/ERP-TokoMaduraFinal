<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerbaruiPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('pengguna')->id;

        return [
            'nama' => ['required', 'string', 'max:120'],
            'jabatan' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('users', 'username')->ignore($userId)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }
}
