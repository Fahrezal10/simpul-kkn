<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanDesaSeeder extends Seeder
{
    /**
     * Data MASTER WILAYAH (development placeholder).
     *
     * ⚠️ DAFTAR 31 KECAMATAN Kabupaten Indramayu dengan kode_wilayah pendekatan.
     * Ini DATA SEMENTARA untuk mengembangkan & menguji (Matching System, Dashboard GIS)
     * sebelum data riil (±309 desa) diterima dari Bapperida.
     * Saat data riil tiba, ganti isi array di bawah dengan data resmi
     * (atau ubah seeder ini menjadi pembaca CSV/Excel) lalu jalankan:
     *      php artisan migrate:fresh --seed
     *
     * Struktur: nama_kecamatan => [ [nama_desa, jumlah_penduduk], ... ]
     * Koordinat lat/long desa memakai nilai perkiraan di area Indramayu.
     */
    public function run(): void
    {
        $kecamatan = [
            'Haurgeulis'      => [['Ciherang', 5200], ['Wanakaya', 6100]],
            'Gantar'          => [['Situraja', 4800], ['Mulyasari', 3900]],
            'Kroya'           => [['Tanjungkerta', 3500], ['Sukaslamet', 4200]],
            'Gabuswetan'      => [['Gabuskulon', 4600], ['Rancahan', 4300]],
            'Cikedung'        => [['Jambak', 5100], ['Mundakjaya', 4900]],
            'Terisi'          => [['Karangasem', 3700], ['Rajasinga', 3300]],
            'Lelea'           => [['Lelea', 5800], ['Tugu', 4400]],
            'Bangodua'        => [['Bangodua', 5000], ['Wanasari', 3600]],
            'Tukdana'         => [['Rancajawat', 4100], ['Gadel', 3800]],
            'Widasari'        => [['Widasari', 5400], ['Bunder', 4700]],
            'Kertasemaya'     => [['Kertasemaya', 6200], ['Jengkok', 4500]],
            'Jatibarang'      => [['Jatibarang', 7000], ['Pilangsari', 5100]],
            'Sliyeg'          => [['Sliyeg', 5600], ['Gadingan', 4200]],
            'Juntinyuat'      => [['Juntinyuat', 6800], ['Lombang', 5400]],
            'Balongan'        => [['Balongan', 5300], ['Tegalsembadra', 4600]],
            'Indramayu'       => [['Pabeanudik', 7200], ['Karangsong', 6600]],
            'Sindang'         => [['Penganjang', 4900], ['Babadan', 4500]],
            'Cantigi'         => [['Panyingkiran', 3300], ['Cemara', 2900]],
            'Pasekan'         => [['Pasekan', 3500], ['Brondong', 3100]],
            'Lohbener'        => [['Lohbener', 5200], ['Kiajaran', 4000]],
            'Arahan'          => [['Arahan', 4400], ['Pangkalan', 2600]],
            'Losarang'        => [['Losarang', 5900], ['Rajaiyang', 4800]],
            'Kandanghaur'     => [['Kandanghaur', 8100], ['Ilir', 6200]],
            'Bongas'          => [['Bongas', 4700], ['Margamulya', 3900]],
            'Anjatan'         => [['Anjatan', 7700], ['Bugis', 5800]],
            'Sukra'           => [['Sukra', 3600], ['Sukrawetan', 3200]],
            'Karangampel'     => [['Karangampel', 6000], ['Benda', 4500]],
            'Kedokanbunder'   => [['Kedokanbunder', 4100], ['Cangkring', 3600]],
            'Krangkeng'       => [['Krangkeng', 5500], ['Kapricayan', 4700]],
            'Patrol'          => [['Patrol', 6300], ['Bulusan', 5200]],
            'Kedokan Agung'   => [['Kedokan Agung', 4300], ['Wanasari Lor', 3400]],
        ];

        // Koordinat acuan area Indramayu (lat/-lng) untuk menggeser tiap desa secara deterministik.
        $baseLat = -6.3278;
        $baseLng = 108.3201;

        $idx = 0;
        foreach ($kecamatan as $namaKec => $desas) {
            $kecamatanId = DB::table('kecamatan')->insertGetId([
                'nama_kecamatan' => $namaKec,
                'kode_wilayah' => '3201' . str_pad(($idx + 1), 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $idx++;

            foreach ($desas as $idxDesa => [$namaDesa, $populasi]) {
                DB::table('desa')->insert([
                    'user_id' => null, // operator desa diisi belakangan saat akun dibuat
                    'kecamatan_id' => $kecamatanId,
                    'nama_desa' => $namaDesa,
                    'kode_wilayah' => '3201' . str_pad($idx, 2, '0', STR_PAD_LEFT)
                                    . str_pad(($idxDesa + 1), 2, '0', STR_PAD_LEFT),
                    'jumlah_penduduk' => $populasi,
                    'luas_wilayah' => round(1.5 + (($idx + $idxDesa) % 9) * 0.6, 2),
                    'latitude' => round($baseLat + ($idx + $idxDesa) * 0.002, 7),
                    'longitude' => round($baseLng + (($idx * 2) + $idxDesa) * 0.003, 7),
                    'profil_umum' => 'Data placeholder pengembangan. Menunggu profil resmi dari Bapperida.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}