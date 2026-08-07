<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\DesaKebutuhan;
use App\Models\DesaPotensi;
use App\Models\IsuStrategis;
use App\Models\PermohonanKkn;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo UC-06 (Matching Engine) — TIDAK dipanggil dari DatabaseSeeder.
 *
 * Modul-desa (UI CRUD desa/isu) dipegang anti di Fase 2 dan belum tersedia,
 * sehingga seeder ini mengisi data desa/kebutuhan/potensi/isu yang dipakai
 * sebagai INPUT matching. Jalankan hanya saat dev/demo:
 *
 *     php artisan migrate:fresh --seed
 *     php artisan db:seed --class=MatchingDemoSeeder
 *
 * Efek:
 *  1. Mengisi beberapa desa yang telah ada (dari KecamatanDesaSeeder) dengan
 *     kebutuhan & potensi yang cocok dengan tema kelompok contoh.
 *  2. Membuat Perangkat Daerah + Isu Strategis (prioritas daerah).
 *  3. Menandai permohonan contoh dari PT UIN Indramayu menjadi "terverifikasi"
 *     (sehingga kelompoknya tampil & bisa dijalankan matching).
 *  4. Menyiapkan akun Bapperida demo (password: password).
 */
class MatchingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPerangkatDaerahDanIsu();
        $this->seedKebutuhanPotensiDesa();
        $this->verifikasiPermohonanContoh();
        $this->pastikanAkunBapperida();

        $this->command?->info('Matching demo data siap. Login Bapperida: admin@bapperida-indramayu.go.id / password');
    }

    /* ------------------------------------------------------------------ */

    private function seedPerangkatDaerahDanIsu(): void
    {
        $opd = PerangkatDaerah::updateOrCreate(
            ['nama_opd' => 'Dinas Komunikasi dan Informatika Kab. Indramayu'],
            ['bidang_tugas' => 'Digitalisasi layanan & informasi publik']
        );
        IsuStrategis::updateOrCreate(
            ['perangkat_daerah_id' => $opd->id, 'kategori_isu' => 'digitalisasi'],
            [
                'deskripsi'         => 'Mendorong transformasi digital desa: layanan daring, literasi digital.',
                'wilayah_terdampak' => 'Haurgeulis, Gabuswetan, Indramayu',
                'rekomendasi_tema'  => 'Sosialisasi Digitalisasi Desa',
            ]
        );

        $opdPangan = PerangkatDaerah::updateOrCreate(
            ['nama_opd' => 'Dinas Ketahanan Pangan dan Pertanian Kab. Indramayu'],
            ['bidang_tugas' => 'Ketahanan pangan & penurunan stunting']
        );
        IsuStrategis::updateOrCreate(
            ['perangkat_daerah_id' => $opdPangan->id, 'kategori_isu' => 'stunting'],
            [
                'deskripsi'         => 'Perbaikan gizi, pendampingan ibu hamil, pemanfaatan pekarangan.',
                'wilayah_terdampak' => 'Kroya, Jatibarang, Sukra',
                'rekomendasi_tema'  => 'Ketahanan Pangan & Stunting',
            ]
        );
    }

    private function seedKebutuhanPotensiDesa(): void
    {
        // Desa yang cocok untuk tema Digitalisasi (kelompok 01).
        $this->petaDesa('Haurgeulis', 'Wanakaya', [
            'kebutuhan' => ['digitalisasi' => 'tinggi'],
            'potensi'   => ['Teknologi Informasi', 'UMKM digital'],
        ]);
        $this->petaDesa('Gabuswetan', 'Gabuskulon', [
            'kebutuhan' => ['digitalisasi' => 'sedang'],
            'potensi'   => ['Teknologi Informasi'],
        ]);

        // Desa yang cocok untuk tema Ketahanan Pangan / Stunting (kelompok 02).
        $this->petaDesa('Kroya', 'Tanjungkerta', [
            'kebutuhan' => ['stunting' => 'tinggi', 'pangan' => 'tinggi'],
            'potensi'   => ['Pertanian', 'Ilmu Gizi'],
        ]);
        $this->petaDesa('Jatibarang', 'Jatibarang', [
            'kebutuhan' => ['stunting' => 'sedang'],
            'potensi'   => ['Pertanian'],
        ]);
    }

    private function petaDesa(string $kecamatan, string $namaDesa, array $data): void
    {
        $desa = Desa::query()
            ->whereHas('kecamatan', fn ($q) => $q->where('nama_kecamatan', $kecamatan))
            ->where('nama_desa', $namaDesa)
            ->first();

        if (! $desa) {
            $this->command?->warn("Desa {$namaDesa} ({$kecamatan}) tidak ditemukan — dilewati.");
            return;
        }

        foreach ($data['kebutuhan'] as $kategori => $prioritas) {
            DesaKebutuhan::updateOrCreate(
                ['desa_id' => $desa->id, 'kategori' => $kategori],
                ['deskripsi' => "Kebutuhan {$kategori} di desa {$namaDesa}.", 'prioritas' => $prioritas]
            );
        }
        foreach ($data['potensi'] as $kategori) {
            DesaPotensi::updateOrCreate(
                ['desa_id' => $desa->id, 'kategori' => $kategori],
                ['deskripsi' => "Potensi {$kategori} di desa {$namaDesa}."]
            );
        }
    }

    private function verifikasiPermohonanContoh(): void
    {
        // Permohonan contoh dari PT UIN Indramayu dibuat status "diajukan"
        // di PerguruanTinggiSeeder; tandai jadi terverifikasi agar bisa di-match.
        $permohonan = PermohonanKkn::where('periode', 'Ganjil 2026/2027')->first();
        if ($permohonan && $permohonan->status !== 'terverifikasi') {
            $permohonan->update([
                'status'             => 'terverifikasi',
                'verified_at'        => now(),
                'verified_by'        => $this->bapperidaUserId() ?? null,
                'catatan_verifikasi' => 'Permohonan lengkap. (auto dari MatchingDemoSeeder)',
            ]);
        }
    }

    private function pastikanAkunBapperida(): void
    {
        $roleId = \Illuminate\Support\Facades\DB::table('roles')
            ->where('nama_role', 'bapperida')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@bapperida-indramayu.go.id'],
            [
                'role_id'         => $roleId,
                'nama'            => 'Admin Bapperida',
                'password'        => Hash::make('password'),
                'status_aktif'    => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function bapperidaUserId(): ?int
    {
        return User::where('email', 'admin@bapperida-indramayu.go.id')->value('id');
    }
}
