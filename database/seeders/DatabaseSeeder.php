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
            // Fase 2 (pemilik: ical): data demo matching + akun role desa & OPD.
            // MatchingDemoSeeder setelah matching-engine di-merge ke main.
            MatchingDemoSeeder::class,
            DesaOpdUserSeeder::class,
            // Fase 4 (pemilik: ical): data demo monitoring & evaluasi.
            MonitoringDemoSeeder::class,
        ]);
    }
}
