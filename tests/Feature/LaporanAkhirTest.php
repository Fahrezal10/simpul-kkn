<?php

namespace Tests\Feature;

use App\Models\KelompokKkn;
use App\Models\LaporanAkhir;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 3b — Laporan Akhir (UC-15): upload mahasiswa & verifikasi DPL.
 */
class LaporanAkhirTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->mahasiswa = User::where('email', 'andi@uin.ac.id')->firstOrFail();
        $this->dpl       = User::where('email', 'siti@uin.ac.id')->firstOrFail();
        $this->kelompok  = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->firstOrFail();
    }

    #[Test]
    public function mahasiswa_dapat_upload_laporan_saat_kelompok_aktif(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $this->actingAs($this->mahasiswa)
            ->get(route('mahasiswa.laporan-akhir.index'))
            ->assertOk();

        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.laporan-akhir.store'), [
                'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('laporan_akhir', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'status'          => 'menunggu',
        ]);
    }

    #[Test]
    public function mahasiswa_tidak_bisa_upload_saat_kelompok_belum_aktif(): void
    {
        $this->kelompok->update(['status' => 'menunggu_matching']);

        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.laporan-akhir.store'), [
                'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('laporan_akhir', 0);
    }

    #[Test]
    public function dpl_dapat_melihat_dan_menyetujui_laporan_bimbingan(): void
    {
        $this->kelompok->update(['status' => 'aktif']);
        $this->uploadLaporan();

        $laporan = LaporanAkhir::firstOrFail();

        $this->actingAs($this->dpl)
            ->get(route('dosen.laporan-akhir.index'))
            ->assertOk();

        $this->actingAs($this->dpl)
            ->post(route('dosen.laporan-akhir.approve', $laporan))
            ->assertRedirect();

        $this->assertSame('disetujui', $laporan->fresh()->status);
        $this->assertNotNull($laporan->fresh()->verified_at);
    }

    #[Test]
    public function dpl_dapat_minta_revisi_laporan(): void
    {
        $this->kelompok->update(['status' => 'aktif']);
        $this->uploadLaporan();

        $laporan = LaporanAkhir::firstOrFail();

        $this->actingAs($this->dpl)
            ->post(route('dosen.laporan-akhir.revisi', $laporan), [
                'catatan_verifikasi' => 'Laporan perlu dilengkapi lampiran.',
            ])
            ->assertRedirect();

        $this->assertSame('revisi', $laporan->fresh()->status);
        $this->assertSame('Laporan perlu dilengkapi lampiran.', $laporan->fresh()->catatan_verifikasi);
    }

    #[Test]
    public function laporan_revisi_dapat_diupload_ulang(): void
    {
        $this->kelompok->update(['status' => 'aktif']);
        $this->uploadLaporan();

        $laporan = LaporanAkhir::firstOrFail();

        // DPL minta revisi.
        $this->actingAs($this->dpl)
            ->post(route('dosen.laporan-akhir.revisi', $laporan), ['catatan_verifikasi' => 'Lengkapi.'])
            ->assertRedirect();
        $this->assertSame('revisi', $laporan->fresh()->status);

        // Mahasiswa upload ulang → update ke menunggu (bukan baris baru).
        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.laporan-akhir.store'), [
                'file_laporan' => UploadedFile::fake()->create('laporan-v2.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertSame('menunggu', $laporan->fresh()->status);
        $this->assertSame(1, LaporanAkhir::where('kelompok_kkn_id', $this->kelompok->id)->count());
    }

    private function uploadLaporan(): void
    {
        $this->actingAs($this->mahasiswa)->post(route('mahasiswa.laporan-akhir.store'), [
            'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
        ]);
    }
}
