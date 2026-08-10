<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\KelompokKkn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 4d — Penutupan Periode KKN: kelompok aktif → selesai.
 */
class PenutupanPeriodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class); // MonitoringDemoSeeder mengaktifkan kelompok 01

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
    }

    #[Test]
    public function halaman_penutupan_periode_menampilkan_kelompok_aktif(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('bapperida.penutupan-periode.index'))
            ->assertOk()
            ->assertSee('Penutupan Periode')
            ->assertSee('KKN-2026-001-01');
    }

    #[Test]
    public function menutup_periode_mengubah_semua_kelompok_aktif_menjadi_selesai(): void
    {
        $aktifSebelum = KelompokKkn::where('status', 'aktif')->count();
        $this->assertGreaterThan(0, $aktifSebelum, 'Harus ada kelompok aktif dari seeder demo');

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.penutupan-periode.store'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, KelompokKkn::where('status', 'aktif')->count());
        $this->assertSame($aktifSebelum, KelompokKkn::where('status', 'selesai')->count());
    }

    #[Test]
    public function penutupan_mencatat_activity_log(): void
    {
        $this->actingAs($this->bapperida)
            ->post(route('bapperida.penutupan-periode.store'))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'user_id' => $this->bapperida->id,
            'aksi'    => 'tutup_periode',
        ]);
    }

    #[Test]
    public function tanpa_kelompok_aktif_tidak_menutup_apa_apa(): void
    {
        KelompokKkn::where('status', 'aktif')->update(['status' => 'selesai']);

        $this->actingAs($this->bapperida)
            ->from(route('bapperida.penutupan-periode.index'))
            ->post(route('bapperida.penutupan-periode.store'))
            ->assertRedirect(route('bapperida.penutupan-periode.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('activity_log', ['aksi' => 'tutup_periode']);
    }

    #[Test]
    public function role_non_bapperida_tidak_bisa_menutup_periode(): void
    {
        $mahasiswa = User::whereHas('role', fn ($q) => $q->where('nama_role', 'mahasiswa'))->firstOrFail();

        $this->actingAs($mahasiswa)
            ->get(route('bapperida.penutupan-periode.index'))
            ->assertForbidden();
    }
}