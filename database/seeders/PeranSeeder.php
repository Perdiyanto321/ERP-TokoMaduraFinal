<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $daftarPeran = ['admin', 'kasir', 'gudang', 'keuangan'];
        $roles = [];

        foreach ($daftarPeran as $nama) {
            $roles[$nama] = Role::firstOrCreate(['nama' => $nama]);
        }

        $akunDemo = [
            [
                'nama' => 'H. Abdul Ghofur',
                'jabatan' => 'Pemilik & Admin Utama',
                'username' => 'admin',
                'role' => 'admin',
            ],
            [
                'nama' => 'Siti Aminah',
                'jabatan' => 'Kasir Utama',
                'username' => 'kasir',
                'role' => 'kasir',
            ],
            [
                'nama' => 'Moh. Syafi\'i',
                'jabatan' => 'Kepala Gudang',
                'username' => 'gudang',
                'role' => 'gudang',
            ],
            [
                'nama' => 'Nurul Hidayah',
                'jabatan' => 'Staf Keuangan',
                'username' => 'keuangan',
                'role' => 'keuangan',
            ],
        ];

        foreach ($akunDemo as $akun) {
            $karyawan = Karyawan::firstOrCreate(
                ['nama' => $akun['nama']],
                ['jabatan' => $akun['jabatan'], 'status' => 'aktif']
            );

            User::firstOrCreate(
                ['username' => $akun['username']],
                [
                    'role_id' => $roles[$akun['role']]->id,
                    'karyawan_id' => $karyawan->id,
                    'password' => 'password123',
                    'status' => 'aktif',
                ]
            );
        }
    }
}
