<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat 1 akun awal Bapperida (superadmin) agar bisa langsung login
     * setelah migrate & seed. Password mengikuti konvensi akun contoh:
     * "password" (semua akun seeder).
     */
    public function run(): void
    {
        $roleId = DB::table('roles')->where('nama_role', 'bapperida')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@bapperida-indramayu.go.id'],
            [
                'role_id' => $roleId,
                'nama' => 'Admin Bapperida',
                'password' => Hash::make('password'),
                'status_aktif' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
