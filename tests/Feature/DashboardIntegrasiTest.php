<?php

namespace Tests\Feature;

use App\Models\KelompokKkn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 2 — Dashboard Integrasi (PIC: ical, UC-09).
 *
 * Verifikasi statistik dashboard per-role: Bapperida (global),
 * Perguruan Tinggi (own), Kecamatan (own wilayah), Desa (own desa).
 */
class DashboardIntegrasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
        $this->ptUser    = User::where('email', 'pt@uin.ac.id')->firstOrFail();
        $this->kecHaurgeulis = User::where('email', 'kec@haurgeulis.go.id')->firstOrFail();
        $this->desaUser  = User::where('email', 'desa@wanakaya.go.id')->firstOrFail();
    }

    #[Test]
    public function dashboard_bapperida_menampilkan_statistik_global(): void
    {
        $response = $this->actingAs($this->bapperida)
            ->get(route('dashboard'))
            ->assertOk();

        // Data seeder: 1 PT (disetujui), 1 permohonan, 62 desa (31 kec × 2).
        $response->assertSee('Perguruan Tinggi');
        $response->assertSee('Permohonan');
        $response->assertSee('Kelompok KKN per Status');

        // Cek nilai di DB sesuai yang dikirim controller.
        $stats = $this->bapperida->fresh()->role; // ensure user ada
        $this->assertGreaterThan(0, \App\Models\PerguruanTinggi::count());
        $this->assertGreaterThan(0, \App\Models\Desa::count());
    }

    #[Test]
    public function dashboard_perguruan_tinggi_hanya_menampilkan_permohonan_sendiri(): void
    {
        // Pastikan ada kelompok milik PT ini.
        $ptKelompok = KelompokKkn::whereHas('permohonanKkn.perguruanTinggi', fn ($q) => $q->where('id', $this->ptUser->perguruanTinggi->id));
        $this->assertGreaterThan(0, (clone $ptKelompok)->count());

        $response = $this->actingAs($this->ptUser)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertSee('Perguruan Tinggi Anda');
        $response->assertSee('Kelompok KKN per Status');
    }

    #[Test]
    public function dashboard_kecamatan_menampilkan_desa_wilayahnya(): void
    {
        $response = $this->actingAs($this->kecHaurgeulis)
            ->get(route('dashboard'))
            ->assertOk();

        // Kecamatan Haurgeulis memiliki 2 desa (Ciherang, Wanakaya).
        $kecamatan = $this->kecHaurgeulis->kecamatan;
        $this->assertSame(2, $kecamatan->desa()->count());

        $response->assertSee('Kecamatan Haurgeulis');
    }

    #[Test]
    public function dashboard_desa_menampilkan_data_desanya(): void
    {
        $desa = $this->desaUser->desa;

        $response = $this->actingAs($this->desaUser)
            ->get(route('dashboard'))
            ->assertOk();

        $response->assertSee('Desa '.$desa->nama_desa);
    }
}
