<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            // Tambahkan KecamatanDesaSeeder di sini setelah Anda punya data riil
            // dari Bapperida (309 desa) — bisa di-generate dari file Excel/CSV mereka.
        ]);
    }
}
