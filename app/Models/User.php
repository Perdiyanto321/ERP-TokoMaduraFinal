<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'karyawan_id',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }

    public function hasRole(string|array $roles): bool
    {
        $namaRole = $this->role?->nama;
        if (! $namaRole) {
            return false;
        }

        return is_array($roles)
            ? in_array($namaRole, $roles, true)
            : $namaRole === $roles;
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
