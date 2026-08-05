<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seeder untuk tabel roles.
     * Jalankan pertama kali sebelum seeder lain karena users bergantung pada role_id.
     */
    public function run(): void
    {
        $roles = [
            ['nama_role' => 'superadmin'],
            ['nama_role' => 'bapperida'],
            ['nama_role' => 'perguruan_tinggi'],
            ['nama_role' => 'mahasiswa'],
            ['nama_role' => 'dosen'],
            ['nama_role' => 'perangkat_daerah'],
            ['nama_role' => 'kecamatan'],
            ['nama_role' => 'desa'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['nama_role' => $role['nama_role']],
                array_merge($role, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
