<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Akun contoh role DESA & PERANGKAT DAERAH untuk demo modul-desa (UC-10 & UC-12).
 *
 * Password semua akun: "password" (konvensi akun seeder).
 *  - desa@wanakaya.go.id     → operator Desa Wanakaya (Kec. Haurgeulis)
 *  - desa@jatibarang.go.id   → operator Desa Jatibarang
 *  - opd@kominfo.go.id       → operator Perangkat Daerah (Dinkominfo) — isu strategis
 *  - opd@ketapang.go.id      → operator Perangkat Daerah (Dinas Ketahanan Pangan)
 */
class DesaOpdUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleDesa = DB::table('roles')->where('nama_role', 'desa')->value('id');
        $roleOpd  = DB::table('roles')->where('nama_role', 'perangkat_daerah')->value('id');

        $this->akunDesa('Wanakaya', 'Haurgeulis', 'desa@wanakaya.go.id', $roleDesa);
        $this->akunDesa('Jatibarang', 'Jatibarang', 'desa@jatibarang.go.id', $roleDesa);

        $this->akunOpd('Dinas Komunikasi dan Informatika Kab. Indramayu', 'opd@kominfo.go.id', $roleOpd);
        $this->akunOpd('Dinas Ketahanan Pangan dan Pertanian Kab. Indramayu', 'opd@ketapang.go.id', $roleOpd);
    }

    private function akunDesa(string $namaDesa, string $namaKecamatan, string $email, $roleId): void
    {
        $desa = Desa::query()
            ->whereHas('kecamatan', fn ($q) => $q->where('nama_kecamatan', $namaKecamatan))
            ->where('nama_desa', $namaDesa)
            ->first();

        if (! $desa) {
            $this->command?->warn("Desa {$namaDesa} ({$namaKecamatan}) tidak ditemukan — akun dilewati.");
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'role_id'         => $roleId,
                'nama'            => "Operator Desa {$namaDesa}",
                'password'        => Hash::make('password'),
                'status_aktif'    => true,
                'email_verified_at' => now(),
            ]
        );

        // Hubungkan user → desa (kolom user_id pada desa).
        if (! $desa->user_id || $desa->user_id !== $user->id) {
            $desa->update(['user_id' => $user->id]);
        }
    }

    private function akunOpd(string $namaOpd, string $email, $roleId): void
    {
        $opd = PerangkatDaerah::where('nama_opd', $namaOpd)->first();

        if (! $opd) {
            // OPD mungkin belum dibuat (MatchingDemoSeeder milik branch lain).
            // updateOrCreate hanya mengisi saat belum ada agar tidak menimpa
            // bidang_tugas yang sudah diisi seeder lain.
            $opd = PerangkatDaerah::updateOrCreate(
                ['nama_opd' => $namaOpd],
                ['bidang_tugas' => null]
            );
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'role_id'         => $roleId,
                'nama'            => "Operator {$namaOpd}",
                'password'        => Hash::make('password'),
                'status_aktif'    => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $opd->user_id || $opd->user_id !== $user->id) {
            $opd->update(['user_id' => $user->id]);
        }
    }
}
