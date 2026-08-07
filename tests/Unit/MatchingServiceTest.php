<?php

namespace Tests\Unit;

use App\Models\KelompokKkn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MatchingDemoSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * UC-06 — Matching Engine: unit test inti algoritma & persistensi.
 *
 * Berjalan pada MySQL development (mirip environment kerja). Fokus pada
 * perilaku MatchingService + state yang diubah, bukan melalui HTTP
 * (menghindari kompleksitas middleware/session di harness test).
 */
class MatchingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.host', '127.0.0.1');
        config()->set('database.connections.mysql.port', '3306');
        config()->set('database.connections.mysql.database', 'simpul_kkn');
        config()->set('database.connections.mysql.username', 'root');
        config()->set('database.connections.mysql.password', '');
        \Illuminate\Support\Facades\DB::purge('mysql');

        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        (new MatchingDemoSeeder())->run();
    }

    private function kelompok(): KelompokKkn
    {
        return KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->firstOrFail();
    }

    public function test_rank_menghasilkan_total_dan_urutan(): void
    {
        $hasil = (new \App\Services\MatchingService())->rank($this->kelompok());

        $this->assertNotEmpty($hasil);
        // Terurut descending berdasarkan skor_total.
        $skor = array_column($hasil, 'skor_total');
        $sorted = $skor;
        rsort($sorted);
        $this->assertSame($sorted, $skor);
        // Setiap baris punya komponen & flag.
        foreach (array_slice($hasil, 0, 3) as $h) {
            $this->assertArrayHasKey('skor_tema', $h);
            $this->assertArrayHasKey('skor_bidang', $h);
            $this->assertArrayHasKey('skor_total', $h);
            $this->assertArrayHasKey('flag_tumpang_tindih', $h);
        }
    }

    public function test_run_menyimpan_riwayat_dan_mengubah_status(): void
    {
        $k = $this->kelompok();
        $k->riwayatMatching()->delete();
        $k->update(['status' => 'menunggu_matching', 'desa_id' => null]);

        $n = (new \App\Services\MatchingService())->run($k, 1);

        $this->assertGreaterThan(0, $n);
        $this->assertSame($n, $k->fresh()->riwayatMatching()->count());
        $this->assertSame('menunggu_verifikasi_kecamatan', $k->fresh()->status);
    }

    public function test_override_menandai_satu_dipilih(): void
    {
        $k = $this->kelompok();
        $k->riwayatMatching()->delete();
        $k->update(['status' => 'menunggu_matching', 'desa_id' => null]);
        (new \App\Services\MatchingService())->run($k, 1);

        $desa = $k->fresh()->riwayatMatching()->orderBy('skor_total', 'desc')->first()->desa_id;

        $k->fresh()->riwayatMatching()->update(['status' => 'kandidat']);
        $terpilih = $k->fresh()->riwayatMatching()->where('desa_id', $desa)->first();
        $terpilih->update(['status' => 'dipilih']);
        $k->fresh()->update(['desa_id' => $desa]);

        $this->assertSame($desa, $k->fresh()->desa_id);
        $this->assertDatabaseHas('riwayat_matching', [
            'kelompok_kkn_id' => $k->id,
            'desa_id'         => $desa,
            'status'          => 'dipilih',
        ]);
    }
}