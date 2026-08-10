<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\KelompokKkn;
use App\Models\User;
use App\Services\MatchingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 2 — Verifikasi Kecamatan & Approval Final (PIC: ical).
 *
 * Alur end-to-end UC-11 & UC-07:
 *   matching → Bapperida pilih desa → Kecamatan verifikasi kesiapan
 *   → Bapperida approve → status kelompok "aktif".
 */
class VerifikasiKecamatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed penuh (DatabaseSeeder) — sudah memuat MatchingDemoSeeder &
        // DesaOpdUserSeeder (akun desa/OPD/kecamatan).
        $this->seed(DatabaseSeeder::class);

        $this->bapperida   = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
        $this->kecHaurgeulis = User::where('email', 'kec@haurgeulis.go.id')->firstOrFail();

        // Kelompok 01 cocok untuk desa Wanakaya (Kec. Haurgeulis).
        $this->kelompok = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->firstOrFail();
    }

    /** Membawa kelompok ke status "menunggu_verifikasi_kecamatan" + desa dipilih. */
    private function siapkanMenungguVerifikasi(): void
    {
        $this->kelompok->update(['status' => 'menunggu_matching', 'desa_id' => null]);
        $this->kelompok->riwayatMatching()->delete();

        (new MatchingService())->run($this->kelompok, $this->bapperida->id);

        // Pilih desa Wanakaya (Kec. Haurgeulis) — konsisten dgn kecamatan penguji.
        $desa = Desa::where('nama_desa', 'Wanakaya')->firstOrFail();
        $this->kelompok->fresh()->riwayatMatching()->where('desa_id', $desa->id)->update(['status' => 'dipilih']);
        $this->kelompok->fresh()->update(['desa_id' => $desa->id]);
    }

    #[Test]
    public function operator_kecamatan_dapat_melihat_dan_memverifikasi_desa(): void
    {
        $this->siapkanMenungguVerifikasi();

        // Kecamatan melihat daftar kelompok menunggu verifikasi.
        $this->actingAs($this->kecHaurgeulis)
            ->get(route('kecamatan.verifikasi.index'))
            ->assertOk();

        $this->actingAs($this->kecHaurgeulis)
            ->get(route('kecamatan.verifikasi.show', $this->kelompok))
            ->assertOk()
            ->assertSee($this->kelompok->kode_kelompok);

        // Verifikasi "siap" → status menunggu_persetujuan.
        $this->actingAs($this->kecHaurgeulis)
            ->post(route('kecamatan.verifikasi.store', $this->kelompok), [
                'status'  => 'siap',
                'catatan' => 'Desa siap menerima KKN.',
            ])
            ->assertRedirect();

        $this->assertSame('menunggu_persetujuan', $this->kelompok->fresh()->status);
        $this->assertDatabaseHas('verifikasi_kecamatan', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'status'          => 'siap',
        ]);
    }

    #[Test]
    public function bapperida_dapat_menyetujui_dan_mengaktifkan_kelompok(): void
    {
        $this->siapkanMenungguVerifikasi();

        // Kecamatan verifikasi siap.
        $this->actingAs($this->kecHaurgeulis)
            ->post(route('kecamatan.verifikasi.store', $this->kelompok), ['status' => 'siap'])
            ->assertRedirect();

        // Bapperida review & approve.
        $this->actingAs($this->bapperida)
            ->get(route('bapperida.approval-final.show', $this->kelompok))
            ->assertOk();

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.approval-final.approve', $this->kelompok))
            ->assertRedirect();

        $this->assertSame('aktif', $this->kelompok->fresh()->status);
        $this->assertNotNull($this->kelompok->fresh()->desa_id);
    }

    #[Test]
    public function verifikasi_tidak_siap_mengembalikan_ke_matching(): void
    {
        $this->siapkanMenungguVerifikasi();
        $desaId = $this->kelompok->fresh()->desa_id;

        $this->actingAs($this->kecHaurgeulis)
            ->post(route('kecamatan.verifikasi.store', $this->kelompok), [
                'status'  => 'tidak_siap',
                'catatan' => 'Desa dalam masa pembangunan.',
            ])
            ->assertRedirect();

        // Status kembali menunggu_matching, desa terpilih dibatalkan.
        $this->assertSame('menunggu_matching', $this->kelompok->fresh()->status);
        $this->assertDatabaseHas('verifikasi_kecamatan', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'status'          => 'tidak_siap',
        ]);
    }

    #[Test]
    public function bapperida_dapat_menolak_dan_kembali_ke_matching(): void
    {
        $this->siapkanMenungguVerifikasi();

        $this->actingAs($this->kecHaurgeulis)
            ->post(route('kecamatan.verifikasi.store', $this->kelompok), ['status' => 'siap'])
            ->assertRedirect();

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.approval-final.tolak', $this->kelompok))
            ->assertRedirect();

        $this->assertSame('menunggu_matching', $this->kelompok->fresh()->status);
        $this->assertNull($this->kelompok->fresh()->desa_id);
    }

    #[Test]
    public function operator_kecamatan_lain_tidak_bisa_mengakses_desa_di_kecamatan_beda(): void
    {
        $this->siapkanMenungguVerifikasi();

        // Kecamatan Jatibarang mencoba akses kelompok desa Haurgeulis.
        $kecJatibarang = User::where('email', 'kec@jatibarang.go.id')->firstOrFail();

        $this->actingAs($kecJatibarang)
            ->get(route('kecamatan.verifikasi.show', $this->kelompok))
            ->assertForbidden();
    }

    #[Test]
    public function operator_desa_tidak_bisa_mengakses_halaman_kecamatan(): void
    {
        $desaUser = User::where('email', 'desa@wanakaya.go.id')->firstOrFail();

        $this->actingAs($desaUser)
            ->get(route('kecamatan.verifikasi.index'))
            ->assertForbidden();
    }

    #[Test]
    public function desa_yang_ditolak_tidak_muncul_lagi_sebagai_kandidat(): void
    {
        // Bawa ke menunggu_persetujuan (desa Wanakaya dipilih, kecamatan verifikasi siap).
        $this->siapkanMenungguVerifikasi();
        $this->actingAs($this->kecHaurgeulis)
            ->post(route('kecamatan.verifikasi.store', $this->kelompok), ['status' => 'siap'])
            ->assertRedirect();

        // Bapperida tolak lokasi → status ditolak pada riwayat.
        $this->actingAs($this->bapperida)
            ->post(route('bapperida.approval-final.tolak', $this->kelompok))
            ->assertRedirect();

        $desaTolak = Desa::where('nama_desa', 'Wanakaya')->firstOrFail();
        $this->assertDatabaseHas('riwayat_matching', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'desa_id'         => $desaTolak->id,
            'status'          => 'ditolak',
        ]);

        // Jalankan matching ulang — desa ditolak tidak boleh jadi kandidat,
        // dan jejak ditolak tetap dipertahankan.
        $this->kelompok->update(['status' => 'menunggu_matching']);
        (new MatchingService())->run($this->kelompok, $this->bapperida->id);

        $this->assertDatabaseMissing('riwayat_matching', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'desa_id'         => $desaTolak->id,
            'status'          => 'kandidat',
        ]);
        $this->assertDatabaseHas('riwayat_matching', [
            'kelompok_kkn_id' => $this->kelompok->id,
            'desa_id'         => $desaTolak->id,
            'status'          => 'ditolak',
        ]);
    }
}
