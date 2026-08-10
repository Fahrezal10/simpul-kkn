<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 4b — Dashboard GIS (UC-09): peta Leaflet & endpoint data desa.
 */
class GisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
    }

    #[Test]
    public function halaman_gis_dapat_diakses_role_login(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('dashboard.gis'))
            ->assertOk()
            ->assertSee('Dashboard GIS')
            ->assertSee('petaDesa');
    }

    #[Test]
    public function endpoint_data_gis_mengembalikan_semua_desa(): void
    {
        $response = $this->actingAs($this->bapperida)
            ->getJson(route('dashboard.gis.data'))
            ->assertOk()
            ->assertJsonPath('meta.total_desa', \App\Models\Desa::count());

        // FeatureCollection valid: setiap feature punya geometry Point.
        $json = $response->json();
        $this->assertSame('FeatureCollection', $json['type']);
        $this->assertGreaterThan(0, count($json['features']));
        $this->assertSame('Point', $json['features'][0]['geometry']['type']);
        $this->assertArrayHasKey('nama_desa', $json['features'][0]['properties']);
    }

    #[Test]
    public function data_gis_mengikutkan_kelompok_aktif_per_desa(): void
    {
        // MonitoringDemoSeeder mengaktifkan kelompok 01 di Wanakaya.
        $response = $this->actingAs($this->bapperida)
            ->getJson(route('dashboard.gis.data'))
            ->assertOk();

        $features = collect($response->json('features'));
        $wanakaya = $features->first(fn ($f) => $f['properties']['nama_desa'] === 'Wanakaya');

        $this->assertNotNull($wanakaya, 'Wanakaya harus ada di data GIS');
        $this->assertGreaterThan(0, count($wanakaya['properties']['kelompok']), 'Wanakaya harus punya kelompok aktif');
    }
}
