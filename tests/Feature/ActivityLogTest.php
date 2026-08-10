<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 4d — Halaman Aktivitas Sistem (SYS-02 audit trail).
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
    }

    #[Test]
    public function halaman_activity_log_dapat_diakses_bapperida(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('activity-log.index'))
            ->assertOk()
            ->assertSee('Aktivitas Sistem');
    }

    #[Test]
    public function endpoint_data_mengembalikan_log(): void
    {
        $this->actingAs($this->bapperida)
            ->getJson(route('activity-log.data'))
            ->assertOk()
            ->assertJsonStructure(['data', 'from', 'per_page', 'total', 'current_page', 'last_page']);
    }

    #[Test]
    public function role_non_bapperida_tidak_bisa_melihat_log(): void
    {
        $desaUser = User::where('email', 'desa@wanakaya.go.id')->firstOrFail();

        $this->actingAs($desaUser)
            ->get(route('activity-log.index'))
            ->assertForbidden();
    }
}