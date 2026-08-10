<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\IsuStrategis;
use App\Models\Kecamatan;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Database\Seeders\DesaOpdUserSeeder;
use Database\Seeders\KecamatanDesaSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase 2 — Modul Desa (PIC: ical).
 *
 * UC-10 (isu strategis OPD) & UC-12 (profil/potensi desa) + CRUD master desa
 * oleh Bapperida. Dipakai sebagai smoke test alur role: Bapperida, Desa, OPD.
 */
class ModulDesaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(KecamatanDesaSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->seed(DesaOpdUserSeeder::class);

        $this->bapperida = User::where('email', 'admin@bapperida-indramayu.go.id')->first();
        $this->desaUser  = User::where('email', 'desa@wanakaya.go.id')->first();
        $this->opdUser   = User::where('email', 'opd@kominfo.go.id')->first();
    }

    #[Test]
    public function bapperida_dapat_melihat_dan_menambah_desa(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('bapperida.desa.index'))
            ->assertOk();

        $kecamatan = Kecamatan::first();

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.desa.store'), [
                'kecamatan_id'    => $kecamatan->id,
                'kode_wilayah'    => '9999999999',
                'nama_desa'       => 'Desa Uji Smoke',
                'jumlah_penduduk' => 1234,
                'luas_wilayah'    => 2.5,
                'latitude'        => -6.32,
                'longitude'       => 108.32,
                'profil_umum'     => 'Desa untuk pengujian.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('desa', ['nama_desa' => 'Desa Uji Smoke']);
    }

    #[Test]
    public function operator_desa_hanya_mengelola_desanya_sendiri(): void
    {
        $desa = Desa::where('nama_desa', 'Wanakaya')->firstOrFail();

        $this->actingAs($this->desaUser)
            ->get(route('desa.profil.index'))
            ->assertOk()
            ->assertSee($desa->nama_desa);

        // Tambah kebutuhan berprioritas tinggi (parameter matching).
        $this->actingAs($this->desaUser)
            ->post(route('desa.profil.kebutuhan.store'), [
                'kategori'  => 'digitalisasi',
                'deskripsi' => 'Kebutuhan layanan digital desa.',
                'prioritas' => 'tinggi',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('desa_kebutuhan', ['desa_id' => $desa->id, 'kategori' => 'digitalisasi']);
    }

    #[Test]
    public function operator_opd_dapat_mengisi_isu_strategis(): void
    {
        $opd = PerangkatDaerah::where('user_id', $this->opdUser->id)->firstOrFail();

        $this->actingAs($this->opdUser)
            ->get(route('perangkat-daerah.isu-strategis.index'))
            ->assertOk()
            ->assertSee($opd->nama_opd);

        $this->actingAs($this->opdUser)
            ->post(route('perangkat-daerah.isu-strategis.store'), [
                'kategori_isu'     => 'digitalisasi',
                'deskripsi'        => 'Transformasi digital desa.',
                'wilayah_terdampak'=> 'Wanakaya',
                'rekomendasi_tema' => 'Sosialisasi Digitalisasi Desa',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('isu_strategis', [
            'perangkat_daerah_id' => $opd->id,
            'kategori_isu'        => 'digitalisasi',
        ]);
    }

    #[Test]
    public function role_berbeda_tidak_bisa_mengakses_halaman_modul_desa(): void
    {
        // Operator desa tidak boleh buka CRUD master desa Bapperida.
        $this->actingAs($this->desaUser)
            ->get(route('bapperida.desa.index'))
            ->assertForbidden();

        // Bapperida tidak boleh buka halaman profil desa milik operator.
        $this->actingAs($this->bapperida)
            ->get(route('desa.profil.index'))
            ->assertForbidden();
    }
}
