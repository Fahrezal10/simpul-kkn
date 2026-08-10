<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanDesaSeeder extends Seeder
{
    /**
     * Data MASTER WILAYAH Kabupaten Indramayu.
     *
     * Sumber: https://indramayukab.go.id/daftar-nama-desa-di-kabupaten-indramayu/
     * (31 kecamatan; 318 entri desa/kelurahan sesuai tabel resmi situs Pemkab).
     *
     * Struktur: nama_kecamatan => [nama_desa, ...]
     *
     * Catatan:
     *  - jumlah_penduduk dikosongkan (null) karena tidak tersedia di sumber;
     *    menunggu data riil dari Bapperida. UI menampilkan '-' sampai terisi.
     *  - kode_wilayah memakai pola '3201' + nomor urut kecamatan + nomor urut desa
     *    (pendekatan, bukan kode Kemendagri final).
     *  - Koordinat lat/long & luas_wilayah memakai nilai perkiraan deterministik
     *    di area Indramayu agar GIS tetap berfungsi (bukan data resmi Bapperida).
     */
    public function run(): void
    {
        $kecamatan = [
            'Haurgeulis' => [
                'Haurkolot', 'Haurgeulis', 'Sukajati', 'Wanakaya', 'Karangtumaritis',
                'Kertanegara', 'Cipancuh', 'Mekarjati', 'Sidadadi', 'Sumbermulya',
            ],
            'Gantar' => [
                'Bantarwaru', 'Sanca', 'Mekarjaya', 'Gantar', 'Situraja', 'Baleraja', 'Mekarwaru',
            ],
            'Kroya' => [
                'Sukaslamet', 'Tanjungkerta', 'Kroya', 'Sumbon', 'Sukamelang',
                'Temiyang', 'Temiyangsari', 'Jayamulya', 'Sumberjaya',
            ],
            'Gabuswetan' => [
                'Kedungdawa', 'Babakanjaya', 'Gabuskulon', 'Sekarmulya', 'Kedokangabus',
                'Rancamulya', 'Rancahan', 'Gabuswetan', 'Drunten Wetan', 'Drunten Kulon',
            ],
            'Cikedung' => [
                'Loyang', 'Amis', 'Jatisura', 'Jambak', 'Cikedung', 'Cikedung Lor', 'Mundakjaya',
            ],
            'Terisi' => [
                'Cikawung', 'Jatimunggul', 'Jatimulya', 'Plosokerep', 'Rajasinga',
                'Karangasem', 'Manggungan', 'Cibereng', 'Kendayakan',
            ],
            'Lelea' => [
                'Tempel Kulon', 'Tunggulpayung', 'Tugu', 'Nunuk', 'Tempel', 'Pangauban',
                'Telagasari', 'Langgengsari', 'Tamansari', 'Lelea', 'Cempeh',
            ],
            'Bangodua' => [
                'Rancasari', 'Mulyasari', 'Bangodua', 'Beduyut', 'Karanggetas',
                'Tegalgirang', 'Wanasari', 'Malangsari',
            ],
            'Tukdana' => [
                'Mekarsari', 'Bodas', 'Gadel', 'Rancajawat', 'Kerticala', 'Sukamulya',
                'Karangkerta', 'Cangko', 'Pagedangan', 'Sukaperna',
                'Sukadana', 'Tukdana', 'Lajer',
            ],
            'Widasari' => [
                'Mekarsari', 'Bangkaloa Ilir', 'Widasari', 'Kalensari', 'Bunder', 'Ujungaris',
                'Kongsijaya', 'Ujungjaya', 'Ujungpendokjaya', 'Leuwigede', 'Kasmaran',
            ],
            'Kertasemaya' => [
                'Sukawera', 'Tulungagung', 'Jengkok', 'Tegalwirangrong', 'Manguntara', 'Jambe',
                'Lemahayu', 'Tenajar Kidul', 'Kertasemaya', 'Kliwed', 'Tenajar',
                'Laranganjambe', 'Tenajar Lor',
            ],
            'Sukagumiwang' => [
                'Gedangan', 'Cibeber', 'Bondan', 'Gunungsari', 'Sukagumiwang', 'Tersana', 'Cadangpinggan',
            ],
            'Krangkeng' => [
                'Tanjakan', 'Purwajaya', 'Kapringan', 'Singakerta', 'Dukuhjati', 'Tegalmulya',
                'Kedungwungu', 'Srengseng', 'Luwunggesik', 'Kalianyar', 'Krangkeng',
            ],
            'Karangampel' => [
                'Mundu', 'Kaplonganlor', 'Tanjungpura', 'Tanjungsari', 'Pringgacala', 'Benda',
                'Sendang', 'Karangampel Kidul', 'Karangampel', 'Dukuh Jeruk', 'Dukuh Tengah',
            ],
            'Kedokanbunder' => [
                'Jayalaksana', 'Kedokanbunder Wetan', 'Kaplongan', 'Kedokan Agung',
                'Kedokanbunder', 'Jayawinangun', 'Cangkingan',
            ],
            'Juntinyuat' => [
                'Limbangan', 'Segeran Kidul', 'Segeran', 'Juntiweden', 'Juntikebon', 'Dadap',
                'Juntinyuat', 'Juntikedokan', 'Pondoh', 'Sambimaya', 'Tinumpuk', 'Lombang',
            ],
            'Sliyeg' => [
                'Longok', 'Sleman', 'Tambi', 'Sudikampiran', 'Tambi Lor', 'Sleman Lor', 'Majasari',
                'Majasih', 'Sliyeg', 'Gadingan', 'Mekargading', 'Sliyeglor', 'Tugu Kidul', 'Tugu',
            ],
            'Jatibarang' => [
                'Lobener Lor', 'Sukalila', 'Pilangsari', 'Jatibarang Baru', 'Bulak', 'Bulak Lor',
                'Jatibarang', 'Kebulen', 'Pawidean', 'Jatisawit', 'Jatisawit Lor', 'Krasak',
                'Kalimati', 'Malangsemirang', 'Lobener',
            ],
            'Balongan' => [
                'Majakerta', 'Tegalsembadra', 'Sukareja', 'Sukaurip', 'Rawadalem', 'Gelarmendala',
                'Tegalurung', 'Balongan', 'Sudimampir', 'Sudimampirlor',
            ],
            'Indramayu' => [
                'Tambak', 'Telukagung', 'Plumbon', 'Dukuh', 'Pekandangan Jaya', 'Singaraja',
                'Singajaya', 'Pekandangan', 'Bojongsari', 'Kepandean', 'Karangmalang',
                'Karanganyar', 'Lemahmekar', 'Lemahabang', 'Margadadi', 'Paoman',
                'Karangsong', 'Pabeanudik',
            ],
            'Sindang' => [
                'Wanantara', 'Panyindangan Kulon', 'Rambatan Wetan', 'Panyindangan Wetan', 'Kenanga',
                'Terusan', 'Dermayu', 'Sindang', 'Penganjang', 'Babadan',
            ],
            'Cantigi' => [
                'Cemara', 'Cangkring', 'Cantigi Kulon', 'Cantigi Wetan',
                'Panyingkiran Lor', 'Panyingkiran Kidul', 'Lamarantarung',
            ],
            'Pasekan' => [
                'Karanganyar', 'Pagirikan', 'Pasekan', 'Brondong', 'Pabeanilir', 'Totoran',
            ],
            'Lohbener' => [
                'Rambatan Kulon', 'Kiajaran Kulon', 'Kijaran Wetan', 'Lanjan', 'Langut',
                'Larangan', 'Waru', 'Legok', 'Bojongslawi', 'Lohbener', 'Pamayahan', 'Sindangkerta',
            ],
            'Arahan' => [
                'Cidempet', 'Sukasari', 'Arahan Kidul', 'Arahan Lor', 'Linggajati',
                'Tawangsari', 'Sukadadi', 'Pranggong',
            ],
            'Losarang' => [
                'Ranjeng', 'Cemara Kulon', 'Krimun', 'Puntang', 'Pegagan', 'Rajaiyang',
                'Jangga', 'Jumbleng', 'Pangkalan', 'Losarang', 'Muntur', 'Santing',
            ],
            'Kandanghaur' => [
                'Soge', 'Curug', 'Pranti', 'Wirakanan', 'Karangmulya', 'Karanganyar',
                'Wirapanjunan', 'Parean Girang', 'Bulak', 'Ilir', 'Eretan Wetan',
                'Eretan Kulon', 'Kertawinangon',
            ],
            'Bongas' => [
                'Plawangan', 'Cipedang', 'Sidamulya', 'Margamulya', 'Kertajaya',
                'Bongas', 'Cipaat', 'Kertamulya',
            ],
            'Anjatan' => [
                'Anjatan Utara', 'Mangunjaya', 'Bugistua', 'Bugis', 'Salamdarma', 'Kedungwungu',
                'Wanguk', 'Lempuyang', 'Kopyah', 'Anjatan Baru', 'Anjatan', 'Cilandak', 'Cilandak Lor',
            ],
            'Sukra' => [
                'Karanglayung', 'Bogor', 'Sukra', 'Ujunggebang', 'Tegaltaman',
                'Sukrawetan', 'Sumuradem', 'Sumuradem Timur',
            ],
            'Patrol' => [
                'Limpas', 'Patrol', 'Arjasari', 'Sukahaji', 'Bugel', 'Patrollor',
                'Patrol Baru', 'Mekarsari',
            ],
        ];

        // Koordinat acuan area Indramayu (lat/-lng) untuk menggeser tiap desa secara deterministik.
        $baseLat = -6.3278;
        $baseLng = 108.3201;

        $idx = 0;
        foreach ($kecamatan as $namaKec => $desas) {
            $kodeKec = '3201' . str_pad(($idx + 1), 2, '0', STR_PAD_LEFT);

            $kecamatanId = DB::table('kecamatan')->updateOrInsert(
                ['kode_wilayah' => $kodeKec],
                ['nama_kecamatan' => $namaKec, 'updated_at' => now(), 'created_at' => now()]
            ) ? DB::table('kecamatan')->where('kode_wilayah', $kodeKec)->value('id') : null;

            $idx++;

            foreach ($desas as $idxDesa => $namaDesa) {
                $kodeDesa = '3201' . str_pad($idx, 2, '0', STR_PAD_LEFT)
                            . str_pad(($idxDesa + 1), 2, '0', STR_PAD_LEFT);

                DB::table('desa')->updateOrInsert(
                    ['kode_wilayah' => $kodeDesa],
                    [
                        'user_id' => null, // operator desa diisi belakangan saat akun dibuat
                        'kecamatan_id' => $kecamatanId,
                        'nama_desa' => $namaDesa,
                        'jumlah_penduduk' => null, // menunggu data riil dari Bapperida
                        'luas_wilayah' => round(1.5 + (($idx + $idxDesa) % 9) * 0.6, 2),
                        'latitude' => round($baseLat + ($idx + $idxDesa) * 0.002, 7),
                        'longitude' => round($baseLng + (($idx * 2) + $idxDesa) * 0.003, 7),
                        'profil_umum' => 'Data wilayah Kabupaten Indramayu (sumber: indramayukab.go.id).',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}