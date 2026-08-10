<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\EvaluasiDesa;
use App\Models\EvaluasiDpl;
use App\Models\KelompokKkn;
use App\Models\Desa;
use Illuminate\Database\Seeder;

/**
 * Data demo Dashboard Monitoring (UC-09) — Fase 4.
 *
 * Mengaktifkan 1-2 kelompok & mengisi evaluasi desa/DPL sehingga dashboard
 * monitoring/evaluasi menampilkan angka nyata. Jalankan lewat DatabaseSeeder
 * (pemilik seeder Fase 4: ical).
 */
class MonitoringDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Aktifkan kelompok 01 (tema digitalisasi) dengan desa Wanakaya.
        $kelompok = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->first();
        $desa = Desa::where('nama_desa', 'Wanakaya')->first();

        if ($kelompok && $desa) {
            $kelompok->update(['status' => 'aktif', 'desa_id' => $desa->id]);

            // Evaluasi desa (skala 1-5).
            EvaluasiDesa::updateOrCreate(
                ['kelompok_kkn_id' => $kelompok->id, 'desa_id' => $desa->id],
                [
                    'skor_kualitas_program' => 4,
                    'skor_manfaat'          => 5,
                    'skor_kedisiplinan'     => 4,
                    'catatan'               => 'Program digitalisasi bermanfaat bagi warga.',
                ]
            );

            // Evaluasi DPL (nilai 0-100) oleh DPL kelompok 01.
            $dpl = Dosen::where('nama', 'Dr. Siti Rahmawati')->first();
            if ($dpl) {
                EvaluasiDpl::updateOrCreate(
                    ['kelompok_kkn_id' => $kelompok->id, 'dosen_id' => $dpl->id],
                    ['nilai' => 85, 'catatan' => 'Kinerja kelompok sangat baik.']
                );
            }
        }

        $this->command?->info('Monitoring demo data siap (kelompok aktif + evaluasi).');
    }
}
