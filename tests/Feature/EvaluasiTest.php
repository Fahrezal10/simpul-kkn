<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\KelompokKkn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 3c — Evaluasi Desa (UC-13) & DPL (UC-17).
 */
class EvaluasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->desaUser = User::where('email', 'desa@wanakaya.go.id')->firstOrFail();
        $this->dpl      = User::where('email', 'siti@uin.ac.id')->firstOrFail();
        $this->kelompok = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->firstOrFail();

        // Kelompok bertugas di Wanakaya (desa penguji) & aktif.
        $desa = Desa::where('nama_desa', 'Wanakaya')->firstOrFail();
        $this->kelompok->update(['status' => 'aktif', 'desa_id' => $desa->id]);
    }

    #[Test]
    public function desa_dapat_mengisi_evaluasi_kelompok_di_desanya(): void
    {
        $this->actingAs($this->desaUser)
            ->get(route('desa.evaluasi.index'))
            ->assertOk();

        $this->actingAs($this->desaUser)
            ->post(route('desa.evaluasi.store', $this->kelompok), [
                'skor_kualitas_program' => 4,
                'skor_manfaat'          => 5,
                'skor_kedisiplinan'     => 4,
                'catatan'               => 'Program bermanfaat.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluasi_desa', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'skor_kualitas_program' => 4,
            'skor_manfaat' => 5,
        ]);
    }

    #[Test]
    public function desa_tidak_bisa_mengevaluasi_kelompok_di_desa_lain(): void
    {
        // Pindahkan kelompok ke desa lain (bukan Wanakaya).
        $desaLain = Desa::where('nama_desa', 'Ciherang')->firstOrFail();
        $this->kelompok->update(['desa_id' => $desaLain->id]);

        $this->actingAs($this->desaUser)
            ->post(route('desa.evaluasi.store', $this->kelompok), [
                'skor_kualitas_program' => 4,
                'skor_manfaat'          => 4,
                'skor_kedisiplinan'     => 4,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('evaluasi_desa', 0);
    }

    #[Test]
    public function dpl_dapat_mengisi_evaluasi_kelompok_bimbingan(): void
    {
        $this->actingAs($this->dpl)
            ->get(route('dosen.evaluasi.index'))
            ->assertOk();

        $this->actingAs($this->dpl)
            ->post(route('dosen.evaluasi.store', $this->kelompok), [
                'nilai'   => 85,
                'catatan' => 'Kinerja baik.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluasi_dpl', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'nilai'           => 85,
        ]);
    }

    #[Test]
    public function dpl_lain_tidak_bisa_mengevaluasi_kelompok_bukan_bimbingan(): void
    {
        // DPL lain (ahmad) membimbing kelompok 02, bukan kelompok 01.
        $dplLain = User::where('email', 'ahmad@uin.ac.id')->firstOrFail();

        $this->actingAs($dplLain)
            ->post(route('dosen.evaluasi.store', $this->kelompok), [
                'nilai'   => 80,
            ])
            ->assertForbidden();
    }
}
