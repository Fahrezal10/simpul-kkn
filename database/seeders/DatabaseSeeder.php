<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            KecamatanDesaSeeder::class,
            AdminUserSeeder::class,
            PerguruanTinggiSeeder::class,
            // Fase 2 (pemilik: ical): akun demo role desa & OPD.
            // Catatan: MatchingDemoSeeder dipanggil setelah branch
            // matching-engine di-merge ke main (file-nya belum ada di branch ini).
            DesaOpdUserSeeder::class,
        ]);
    }
}
