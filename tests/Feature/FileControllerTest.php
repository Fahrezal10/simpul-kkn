<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\KelompokKkn;
use App\Models\LaporanAkhir;
use App\Models\Logbook;
use App\Models\PerguruanTinggi;
use App\Models\PermohonanKkn;
use App\Models\User;
use App\Services\MatchingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * H1 — Proteksi file: dokumen internal (surat/proposal permohonan, legalitas PT,
 * laporan akhir, foto logbook) disimpan di disk private & hanya bisa diunduh
 * lewat route file.download dengan otorisasi sesuai jenis file.
 *
 * Review Fase 4 (fix/fase4-review-ical) — menutup celah: konfigurasi disk
 * 'private' + otorisasi lintas role pada FileController.
 */
class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->bapperida  = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();
        $this->ptOwner    = User::where('email', 'pt@uin.ac.id')->firstOrFail();
        $this->ptLain     = User::where('email', 'pt-menunggu@uin.ac.id')->firstOrFail();
        $this->mahasiswa  = User::where('email', 'andi@uin.ac.id')->firstOrFail();
        $this->dpl        = User::where('email', 'siti@uin.ac.id')->firstOrFail();

        $this->pt     = PerguruanTinggi::where('user_id', $this->ptOwner->id)->firstOrFail();
        $this->permohonan = PermohonanKkn::where('perguruan_tinggi_id', $this->pt->id)->firstOrFail();
        $this->kelompok   = KelompokKkn::where('kode_kelompok', 'KKN-2026-001-01')->firstOrFail();

        // Gunakan disk terpisah agar tidak mencemari storage nyata.
        // Upload memakai default disk 'local' (FILESYSTEM_DISK), download via disk 'private'.
        Storage::fake('local');
        Storage::fake('private');
    }

    /* ====================================================================
     * 1. File tersimpan di disk PRIVATE (bukan publik)
     * ==================================================================== */

    #[Test]
    public function upload_logbook_tersimpan_di_disk_private_bukan_publik(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $foto = UploadedFile::fake()->image('kegiatan.jpg', 100, 100);

        $this->actingAs($this->mahasiswa)
            ->post(route('mahasiswa.logbook.store'), [
                'tanggal'            => now()->subDay()->format('Y-m-d'),
                'deskripsi_kegiatan' => 'Dokumentasi kegiatan hari ini.',
                'foto'               => $foto,
            ])
            ->assertRedirect();

        $logbook = Logbook::firstOrFail();

        // Path disimpan (tanpa disk public) → tersedia di default disk 'local'.
        Storage::disk('local')->assertExists($logbook->foto);
        // Dan TIDAK tersedia di disk publik (tidak bocor lewat storage symlink).
        Storage::disk('public')->assertMissing($logbook->foto);
    }

    /* ====================================================================
     * 2. Otorisasi permohonan: pemilik PT & Bapperida boleh; PT lain dilarang
     * ==================================================================== */

    #[Test]
    public function bapperida_dan_pemilik_pt_bisa_unduh_permohonan(): void
    {
        Storage::disk('private')->put($this->permohonan->file_surat_permohonan, 'PDF-DEMO');

        // Bapperida (super role).
        $this->actingAs($this->bapperida)
            ->get(route('file.download', ['jenis' => 'permohonan', 'path' => $this->permohonan->file_surat_permohonan]))
            ->assertOk()
            ->assertDownload('demo-surat.pdf');

        // PT pemilik.
        $this->actingAs($this->ptOwner)
            ->get(route('file.download', ['jenis' => 'permohonan', 'path' => $this->permohonan->file_surat_permohonan]))
            ->assertOk();
    }

    #[Test]
    public function pt_lain_tidak_bisa_unduh_permohonan_bukan_miliknya(): void
    {
        Storage::disk('private')->put($this->permohonan->file_surat_permohonan, 'PDF-DEMO');

        $this->actingAs($this->ptLain)
            ->get(route('file.download', ['jenis' => 'permohonan', 'path' => $this->permohonan->file_surat_permohonan]))
            ->assertForbidden();
    }

    /* ====================================================================
     * 3. Otorisasi legalitas PT: hanya pemilik PT
     * ==================================================================== */

    #[Test]
    public function legalitas_pt_hanya_bisa_diunduh_oleh_pt_pemilik(): void
    {
        $this->pt->update(['dokumen_legalitas' => 'perguruan-tinggi/legalitas/sk-pt.pdf']);
        Storage::disk('private')->put($this->pt->dokumen_legalitas, 'PDF-SK');

        // Pemilik boleh.
        $this->actingAs($this->ptOwner)
            ->get(route('file.download', ['jenis' => 'legalitas', 'path' => $this->pt->dokumen_legalitas]))
            ->assertOk();

        // PT lain dilarang.
        $this->actingAs($this->ptLain)
            ->get(route('file.download', ['jenis' => 'legalitas', 'path' => $this->pt->dokumen_legalitas]))
            ->assertForbidden();
    }

    /* ====================================================================
     * 4. Otorisasi laporan akhir & logbook: mahasiswa/DPL kelompok terkait
     * ==================================================================== */

    #[Test]
    public function laporan_akhir_diunduh_mahasiswa_dan_dpl_kelompok_sendiri(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $laporan = LaporanAkhir::create([
            'kelompok_kkn_id' => $this->kelompok->id,
            'file_laporan'    => 'laporan-akhir/laporan-001.pdf',
            'uploaded_by'     => $this->mahasiswa->id,
            'status'          => 'menunggu',
        ]);

        Storage::disk('private')->put($laporan->file_laporan, 'PDF-LAPORAN');

        // Mahasiswa kelompok terkait.
        $this->actingAs($this->mahasiswa)
            ->get(route('file.download', ['jenis' => 'laporan-akhir', 'path' => $laporan->file_laporan]))
            ->assertOk();

        // DPL pembimbing kelompok terkait.
        $this->actingAs($this->dpl)
            ->get(route('file.download', ['jenis' => 'laporan-akhir', 'path' => $laporan->file_laporan]))
            ->assertOk();
    }

    #[Test]
    public function logbook_diunduh_mahasiswa_pemilik_dan_dpl_pembimbing(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $logbook = Logbook::create([
            'kelompok_kkn_id'    => $this->kelompok->id,
            'mahasiswa_id'       => $this->mahasiswa->mahasiswa->id,
            'tanggal'            => now()->subDay()->toDateString(),
            'deskripsi_kegiatan' => 'Kegiatan.',
            'foto'               => 'logbook/foto-001.jpg',
            'status'             => 'menunggu',
        ]);

        Storage::disk('private')->put($logbook->foto, 'JPG-FOTO');

        // Mahasiswa pemilik.
        $this->actingAs($this->mahasiswa)
            ->get(route('file.download', ['jenis' => 'logbook', 'path' => $logbook->foto]))
            ->assertOk();

        // DPL pembimbing kelompok terkait.
        $this->actingAs($this->dpl)
            ->get(route('file.download', ['jenis' => 'logbook', 'path' => $logbook->foto]))
            ->assertOk();
    }

    #[Test]
    public function laporan_dan_logbook_tidak_bisa_diunduh_role_lain(): void
    {
        $this->kelompok->update(['status' => 'aktif']);

        $laporan = LaporanAkhir::create([
            'kelompok_kkn_id' => $this->kelompok->id,
            'file_laporan'    => 'laporan-akhir/laporan-001.pdf',
            'uploaded_by'     => $this->mahasiswa->id,
            'status'          => 'menunggu',
        ]);
        Storage::disk('private')->put($laporan->file_laporan, 'PDF-LAPORAN');

        // PT (bukan pemilik dokumen) tidak boleh unduh laporan.
        $this->actingAs($this->ptOwner)
            ->get(route('file.download', ['jenis' => 'laporan-akhir', 'path' => $laporan->file_laporan]))
            ->assertForbidden();
    }

    /* ====================================================================
     * 5. File tak ditemukan / jenis tak dikenal
     * ==================================================================== */

    #[Test]
    public function file_tak_ada_menghasilkan_404(): void
    {
        $this->actingAs($this->bapperida)
            ->get(route('file.download', ['jenis' => 'permohonan', 'path' => 'permohonan/tidak-ada.pdf']))
            ->assertNotFound();
    }

    #[Test]
    public function jenis_file_tak_dikenal_ditolak_untuk_non_admin(): void
    {
        Storage::disk('private')->put('rahasia/notes.txt', 'isi');

        // PT tidak punya akses default → jenis tak dikenal harus 403.
        $this->actingAs($this->ptLain)
            ->get(route('file.download', ['jenis' => 'rahasia', 'path' => 'rahasia/notes.txt']))
            ->assertForbidden();
    }

    #[Test]
    public function jenis_file_tak_dikenal_ditolak_untuk_admin(): void
    {
        Storage::disk('private')->put('rahasia/notes.txt', 'isi');

        // Admin sekalipun tidak boleh menyajikan jenis file yang tidak terdaftar
        // (jenis tak dikenal → tidak ada otorisasi yang relevan → 403).
        $this->actingAs($this->bapperida)
            ->get(route('file.download', ['jenis' => 'rahasia', 'path' => 'rahasia/notes.txt']))
            ->assertForbidden();
    }

    /* ====================================================================
     * 6. Guard matching H3 — override & batalPilih saat status tidak boleh
     * ==================================================================== */

    private function siapkanMenungguVerifikasi(): void
    {
        $this->kelompok->update(['status' => 'menunggu_matching', 'desa_id' => null]);
        $this->kelompok->riwayatMatching()->delete();

        (new MatchingService())->run($this->kelompok, $this->bapperida->id);

        $desa = Desa::where('nama_desa', 'Wanakaya')->firstOrFail();
        $this->kelompok->fresh()->riwayatMatching()->where('desa_id', $desa->id)->update(['status' => 'dipilih']);
        $this->kelompok->fresh()->update(['desa_id' => $desa->id, 'status' => 'menunggu_verifikasi_kecamatan']);
    }

    #[Test]
    public function override_ditolak_saat_kelompok_aktif_atau_selesai(): void
    {
        $this->siapkanMenungguVerifikasi();
        $this->kelompok->fresh()->update(['status' => 'aktif']);

        $desaLain = Desa::where('nama_desa', '!=', 'Wanakaya')->firstOrFail();

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.matching.override', $this->kelompok), ['desa_id' => $desaLain->id])
            ->assertSessionHas('error');

        // Lokasi & status tidak berubah.
        $this->assertSame('aktif', $this->kelompok->fresh()->status);
        $this->assertNotSame($desaLain->id, $this->kelompok->fresh()->desa_id);
    }

    #[Test]
    public function batal_pilih_ditolak_saat_kelompok_aktif(): void
    {
        $this->siapkanMenungguVerifikasi();
        $this->kelompok->update(['status' => 'aktif']);

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.matching.batal-pilih', $this->kelompok))
            ->assertSessionHas('error');

        $this->assertSame('aktif', $this->kelompok->fresh()->status);
        $this->assertNotNull($this->kelompok->fresh()->desa_id);
    }

    #[Test]
    public function batal_pilih_mengembalikan_ke_menunggu_matching(): void
    {
        $this->siapkanMenungguVerifikasi();

        $this->actingAs($this->bapperida)
            ->post(route('bapperida.matching.batal-pilih', $this->kelompok))
            ->assertSessionHas('success');

        $this->assertSame('menunggu_matching', $this->kelompok->fresh()->status);
        $this->assertNull($this->kelompok->fresh()->desa_id);
    }
}
