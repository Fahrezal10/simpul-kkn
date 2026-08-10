<?php

namespace Tests\Feature;

use App\Models\EvaluasiDesa;
use App\Models\EvaluasiDpl;
use App\Models\KelompokKkn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 4a — Dashboard Monitoring & Evaluasi (UC-09).
 */
class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class); // sudah memuat MonitoringDemoSeeder

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
    }

    #[Test]
    public function bapperida_dapat_melihat_dashboard_monitoring(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('bapperida.monitoring.index'))
            ->assertOk()
            ->assertSee('Dashboard Monitoring')
            ->assertSee('Kelompok Aktif');
    }

    #[Test]
    public function monitoring_menampilkan_data_demo_kelompok_aktif_dan_evaluasi(): void
    {
        // MonitoringDemoSeeder mengaktifkan kelompok 01 + evaluasi.
        $kelompok = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->first();
        $this->assertSame('aktif', $kelompok->fresh()->status);
        $this->assertGreaterThan(0, EvaluasiDesa::count());
        $this->assertGreaterThan(0, EvaluasiDpl::count());

        $this->actingAs($this->bapperida)
            ->get(route('bapperida.monitoring.index'))
            ->assertSee('Kelompok Aktif');
    }

    #[Test]
    public function monitoring_tidak_dapat_diakses_role_non_bapperida(): void
    {
        $desaUser = User::where('email', 'desa@wanakaya.go.id')->firstOrFail();

        $this->actingAs($desaUser)
            ->get(route('bapperida.monitoring.index'))
            ->assertForbidden();
    }
}
