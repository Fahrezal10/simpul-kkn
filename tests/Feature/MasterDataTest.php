<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 4c — Master Data generik (UC-08): CRUD kecamatan & perangkat daerah
 * via satu controller generik (`MasterDataController`).
 */
class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
    }

    #[Test]
    public function bapperida_dapat_melihat_pemilih_jenis_master_data(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('master-data.index'))
            ->assertOk()
            ->assertSee('Kecamatan')
            ->assertSee('Perangkat Daerah');
    }

    #[Test]
    public function halaman_crud_kecamatan_dapat_diakses(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('master-data.list', 'kecamatan'))
            ->assertOk()
            ->assertSee('Master Data Kecamatan')
            ->assertSee('tabelMaster');
    }

    #[Test]
    public function endpoint_data_kecamatan_mengembalikan_pagination(): void
    {
        $this->actingAs($this->bapperida)
            ->getJson(route('master-data.data', 'kecamatan'))
            ->assertOk()
            ->assertJsonPath('total', Kecamatan::count())
            ->assertJsonStructure(['data', 'from', 'per_page', 'total', 'current_page', 'last_page']);
    }

    #[Test]
    public function bapperida_dapat_menambah_kecamatan_baru(): void
    {
        $this->actingAs($this->bapperida)
            ->post(route('master-data.store', 'kecamatan'), [
                'nama_kecamatan' => 'Kecamatan Uji',
                'kode_wilayah'   => '9999999',
            ])
            ->assertRedirect(route('master-data.list', 'kecamatan'));

        $this->assertDatabaseHas('kecamatan', [
            'nama_kecamatan' => 'Kecamatan Uji',
            'kode_wilayah'   => '9999999',
        ]);
    }

    #[Test]
    public function kode_wilayah_kecamatan_harus_unik(): void
    {
        $kode = Kecamatan::first()->kode_wilayah;

        $this->actingAs($this->bapperida)
            ->from(route('master-data.list', 'kecamatan'))
            ->post(route('master-data.store', 'kecamatan'), [
                'nama_kecamatan' => 'Kecamatan Duplikat',
                'kode_wilayah'   => $kode,
            ])
            ->assertSessionHasErrors('kode_wilayah');
    }

    #[Test]
    public function bapperida_dapat_mengubah_kecamatan(): void
    {
        $kec = Kecamatan::first();

        $this->actingAs($this->bapperida)
            ->put(route('master-data.update', ['kecamatan', $kec->id]), [
                'nama_kecamatan' => $kec->nama_kecamatan.' Updated',
                'kode_wilayah'   => $kec->kode_wilayah,
            ])
            ->assertRedirect(route('master-data.list', 'kecamatan'));

        $this->assertDatabaseHas('kecamatan', [
            'id'             => $kec->id,
            'nama_kecamatan' => $kec->nama_kecamatan.' Updated',
        ]);
    }

    #[Test]
    public function kecamatan_punya_desa_tidak_bisa_dihapus(): void
    {
        $kec = Kecamatan::whereHas('desa')->firstOrFail();

        $this->actingAs($this->bapperida)
            ->from(route('master-data.list', 'kecamatan'))
            ->delete(route('master-data.destroy', ['kecamatan', $kec->id]))
            ->assertRedirect(route('master-data.list', 'kecamatan'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kecamatan', ['id' => $kec->id]);
    }

    #[Test]
    public function kecamatan_tanpa_desa_bisa_dihapus(): void
    {
        $kec = Kecamatan::create(['nama_kecamatan' => 'Kec Kosong', 'kode_wilayah' => '8888888']);

        $this->actingAs($this->bapperida)
            ->delete(route('master-data.destroy', ['kecamatan', $kec->id]))
            ->assertRedirect(route('master-data.list', 'kecamatan'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kecamatan', ['id' => $kec->id]);
    }

    #[Test]
    public function jenis_master_data_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('master-data.list', 'jenis-tidak-ada'))
            ->assertNotFound();
    }

    #[Test]
    public function role_non_bapperida_tidak_bisa_akses(): void
    {
        $mahasiswa = User::whereHas('role', fn ($q) => $q->where('nama_role', 'mahasiswa'))->firstOrFail();

        $this->actingAs($mahasiswa)
            ->get(route('master-data.index'))
            ->assertForbidden();
    }
}