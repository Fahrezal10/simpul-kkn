<?php

namespace Tests\Feature;

use App\Models\KelompokKkn;
use App\Models\Logbook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 3a — Logbook Mahasiswa (UC-14) & Approval DPL (UC-16).
 */
class LogbookTest extends TestCase
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
    public function mahasiswa_dapat_mengisi_logbook_saat_kelompok_aktif(): void
    {
        // Aktifkan kelompok (precondition UC-14).
        $this->kelompok->update(['status' => 'aktif']);

        $this->actingAs($this->mahasiswa)
            ->get(route('mahasiswa.logbook.index'))
            ->assertOk();

        $tanggal = now()->subDay()->format('Y-m-d'); // tanggal valid (bukan masa depan)

        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.logbook.store'), [
                'tanggal'            => $tanggal,
                'deskripsi_kegiatan' => 'Sosialisasi digitalisasi ke warga.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('logbook', [
            'mahasiswa_id'       => $this->mahasiswa->mahasiswa->id,
            'status'             => 'menunggu',
        ]);
        // Cast tanggal ke date; cek via collection karena nilai DB menyimpan datetime.
        $this->assertTrue(
            Logbook::where('mahasiswa_id', $this->mahasiswa->mahasiswa->id)
                ->whereDate('tanggal', $tanggal)->exists(),
            'Logbook untuk tanggal tsb harus tersimpan.'
        );
    }

    #[Test]
    public function mahasiswa_tidak_bisa_logbook_saat_kelompok_belum_aktif(): void
    {
        $this->kelompok->update(['status' => 'menunggu_matching']);

        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.logbook.store'), [
                'tanggal'            => now()->subDay()->format('Y-m-d'),
                'deskripsi_kegiatan' => 'Kegiatan sebelum aktif.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('logbook', 0);
    }

    #[Test]
    public function dpl_dapat_menyetujui_logbook_bimbingan(): void
    {
        $this->kelompok->update(['status' => 'aktif']);
        $this->aktifkanDanIsiLogbook();

        $logbook = Logbook::firstOrFail();

        $this->actingAs($this->dpl)
            ->get(route('dosen.logbook.index'))
            ->assertOk();

        $this->actingAs($this->dpl)
            ->post(route('dosen.logbook.approve', $logbook))
            ->assertRedirect();

        $this->assertSame('disetujui', $logbook->fresh()->status);
        $this->assertNotNull($logbook->fresh()->approved_at);
    }

    #[Test]
    public function dpl_dapat_minta_revisi_logbook(): void
    {
        $this->kelompok->update(['status' => 'aktif']);
        $this->aktifkanDanIsiLogbook();

        $logbook = Logbook::firstOrFail();

        $this->actingAs($this->dpl)
            ->post(route('dosen.logbook.revisi', $logbook), [
                'catatan_dpl' => 'Mohon tambahkan detail kegiatan.',
            ])
            ->assertRedirect();

        $this->assertSame('revisi', $logbook->fresh()->status);
        $this->assertSame('Mohon tambahkan detail kegiatan.', $logbook->fresh()->catatan_dpl);
    }

    #[Test]
    public function mahasiswa_tidak_bisa_mengisi_logbook_ganda_per_tanggal(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $data = ['tanggal' => now()->subDay()->format('Y-m-d'), 'deskripsi_kegiatan' => 'Kegiatan hari ini.'];

        $this->actingAs($this->mahasiswa)->post(route('mahasiswa.logbook.store'), $data)->assertRedirect();
        $this->actingAs($this->mahasiswa)->post(route('mahasiswa.logbook.store'), $data)->assertRedirect();

        // Hanya 1 logbook untuk tanggal tsb (validasi aplikasi + unique DB).
        $this->assertSame(1, Logbook::where('mahasiswa_id', $this->mahasiswa->mahasiswa->id)
            ->whereDate('tanggal', $data['tanggal'])->count());
    }

    private function aktifkanDanIsiLogbook(): void
    {
        $this->actingAs($this->mahasiswa)->post(route('mahasiswa.logbook.store'), [
            'tanggal'            => now()->subDay()->format('Y-m-d'),
            'deskripsi_kegiatan' => 'Kegiatan KKN di desa.',
        ]);
    }
}
