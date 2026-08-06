<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PermohonanKkn;
use App\Models\PerguruanTinggi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerguruanTinggiSeeder extends Seeder
{
    /**
     * Data contoh untuk menguji alur Fase 1 (UC-01 s.d. UC-05).
     *
     * Dibuat:
     *  1. PT "disetujui" + 1 permohonan status "diajukan" berisi 2 kelompok
     *     × 3 mahasiswa (untuk demo verifikasi Bapperida).
     *  2. PT "menunggu" approval (untuk demo persetujuan akun).
     *
     * Akun login (password semua: password):
     *  - pt@uin.ac.id                (PT disetujui)
     *  - pt-menunggu@uin.ac.id       (PT menunggu approval)
     */
    public function run(): void
    {
        $rolePt = DB::table('roles')->where('nama_role', 'perguruan_tinggi')->value('id');

        /* ===== PT 1: Disetujui ===== */
        $userPt = User::updateOrCreate(
            ['email' => 'pt@uin.ac.id'],
            [
                'role_id'         => $rolePt,
                'nama'            => 'Universitas Indramayu',
                'password'        => Hash::make('password'),
                'status_aktif'    => true,
                'email_verified_at' => now(),
            ]
        );

        $pt = PerguruanTinggi::updateOrCreate(
            ['user_id' => $userPt->id],
            [
                'nama_pt'         => 'Universitas Indramayu',
                'alamat'          => 'Jl. Raya Indramayu No. 1, Kab. Indramayu',
                'pic_nama'        => 'Dr. Bambang Sutrisno',
                'pic_email'       => 'bambang@uin.ac.id',
                'pic_telp'        => '081234567890',
                'status_approval' => 'disetujui',
            ]
        );

        $dpl1 = Dosen::updateOrCreate(
            ['perguruan_tinggi_id' => $pt->id, 'nip_niy' => '198501012010011001'],
            ['nama' => 'Dr. Siti Rahmawati', 'no_hp' => '081111222333', 'email' => 'siti@uin.ac.id']
        );
        $dpl2 = Dosen::updateOrCreate(
            ['perguruan_tinggi_id' => $pt->id, 'nip_niy' => '199002102015042002'],
            ['nama' => 'Ahmad Fauzi, M.Pd.', 'no_hp' => '082222333444', 'email' => 'ahmad@uin.ac.id']
        );

        // Permohonan contoh (status diajukan) — agar Bapperida punya antrean verifikasi.
        $permohonan = PermohonanKkn::updateOrCreate(
            [
                'perguruan_tinggi_id' => $pt->id,
                'periode'             => 'Ganjil 2026/2027',
            ],
            [
                'tanggal_mulai'   => '2026-09-01',
                'tanggal_selesai' => '2026-12-20',
                'status'          => 'diajukan',
                // file dummy (tidak benar-benar ada — hanya path untuk demo tampilan).
                'file_surat_permohonan' => 'permohonan/demo-surat.pdf',
                'file_proposal'         => 'permohonan/demo-proposal.pdf',
            ]
        );

        $kelompok1 = KelompokKkn::updateOrCreate(
            ['permohonan_kkn_id' => $permohonan->id, 'dosen_id' => $dpl1->id, 'kode_kelompok' => 'KKN-2026-001-01'],
            ['tema' => 'Sosialisasi Digitalisasi Desa', 'bidang_keilmuan' => 'Teknologi Informasi', 'status' => 'menunggu_matching', 'jumlah_mahasiswa' => 3]
        );
        $kelompok2 = KelompokKkn::updateOrCreate(
            ['permohonan_kkn_id' => $permohonan->id, 'dosen_id' => $dpl2->id, 'kode_kelompok' => 'KKN-2026-001-02'],
            ['tema' => 'Ketahanan Pangan & Stunting', 'bidang_keilmuan' => 'Pertanian/Kesehatan', 'status' => 'menunggu_matching', 'jumlah_mahasiswa' => 3]
        );

        $peserta = [
            [$kelompok1->id, '2024-01001', 'Andi Pratama',  'Teknik Informatika', '081300000001'],
            [$kelompok1->id, '2024-01002', 'Bella Safitri', 'Teknik Informatika', '081300000002'],
            [$kelompok1->id, '2024-01003', 'Candra Wijaya','Ilmu Komunikasi',    '081300000003'],
            [$kelompok2->id, '2024-02001', 'Dewi Lestari',  'Agribisnis',         '081300000004'],
            [$kelompok2->id, '2024-02002', 'Eko Prasetyo',  'Agribisnis',         '081300000005'],
            [$kelompok2->id, '2024-02003', 'Fitri Handayani','Ilmu Gizi',         '081300000006'],
        ];

        foreach ($peserta as [$kelompokId, $nim, $nama, $prodi, $noHp]) {
            Mahasiswa::updateOrCreate(
                ['kelompok_kkn_id' => $kelompokId, 'nim' => $nim],
                ['nama' => $nama, 'prodi' => $prodi, 'no_hp' => $noHp]
            );
        }

        /* ===== Akun login mahasiswa contoh (2 orang, 1 per kelompok) ===== */
        $roleMhs = DB::table('roles')->where('nama_role', 'mahasiswa')->value('id');
        $akunMahasiswa = [
            // [nim, email, password] — password: password
            ['2024-01001', 'andi@uin.ac.id'],
            ['2024-02001', 'dewi@uin.ac.id'],
        ];
        foreach ($akunMahasiswa as [$nim, $email]) {
            $mhs = Mahasiswa::where('nim', $nim)->first();
            if (! $mhs) continue;

            $userMhs = User::updateOrCreate(
                ['email' => $email],
                [
                    'role_id'         => $roleMhs,
                    'nama'            => $mhs->nama,
                    'password'        => Hash::make('password'),
                    'status_aktif'    => true,
                    'email_verified_at' => now(),
                ]
            );
            $mhs->update(['user_id' => $userMhs->id]);
        }

        /* ===== PT 2: Menunggu approval ===== */
        $userPt2 = User::updateOrCreate(
            ['email' => 'pt-menunggu@uin.ac.id'],
            [
                'role_id'         => $rolePt,
                'nama'            => 'STIKes Sehat Jaya',
                'password'        => Hash::make('password'),
                'status_aktif'    => true,
                'email_verified_at' => now(),
            ]
        );

        PerguruanTinggi::updateOrCreate(
            ['user_id' => $userPt2->id],
            [
                'nama_pt'         => 'STIKes Sehat Jaya',
                'alamat'          => 'Jl. Raya Indramayu No. 88',
                'pic_nama'        => 'Ns. Ratna Dewi, S.Kep.',
                'pic_email'       => 'ratna@sehatjaya.ac.id',
                'pic_telp'        => '081555666777',
                'status_approval' => 'menunggu',
            ]
        );
    }
}