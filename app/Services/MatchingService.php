<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\KelompokKkn;
use App\Models\RiwayatMatching;
use Illuminate\Support\Facades\DB;

/**
 * UC-06 — Matching Engine (rule-based scoring).
 *
 * Menghitung rekomendasi desa per kelompok KKN berdasarkan kecocokan teks
 * (keyword overlap) pada 4 dimensi, mengikuti bobot dari docs/01-prd.md §7:
 *
 *   skor_total = 0.30·tema + 0.25·bidang + 0.25·prioritas + 0.20·kebutuhan
 *
 * Bobot dikunci sebagai konstanta (sesuai rekomendasi PRD: konfigurasi bobot
 * via UI dijadwalkan pasca-launch, bukan di fase ini).
 */
class MatchingService
{
    public const WEIGHTS = [
        'tema'      => 0.30,
        'bidang'    => 0.25,
        'prioritas' => 0.25,
        'kebutuhan' => 0.20,
    ];

    /**
     * Menghitung ranking desa untuk satu kelompok KKN (pure, tanpa menyimpan).
     *
     * @return array<int, array{
     *   desa_id: int,
     *   skor_tema: float,
     *   skor_bidang: float,
     *   skor_prioritas: float,
     *   skor_kebutuhan: float,
     *   skor_total: float,
     *   flag_tumpang_tindih: bool,
     *   alasan: array<int, string>,
     * }>
     */
    public function rank(KelompokKkn $kelompok): array
    {
        $tema = (string) $kelompok->tema;
        $bidang = (string) $kelompok->bidang_keilmuan;
        $hasil = [];

        // Kandidat desa yang layak: sudah punya data kebutuhan/potensi/isu.
        $desas = Desa::with(['kebutuhan', 'potensi', 'permasalahan'])->get();

        // Desa yang pernah ditolak (oleh kecamatan / Bapperida) untuk kelompok
        // ini — tidak boleh muncul lagi sebagai kandidat utama.
        $ditolak = RiwayatMatching::where('kelompok_kkn_id', $kelompok->id)
            ->where('status', 'ditolak')
            ->pluck('desa_id')
            ->flip();

        foreach ($desas as $desa) {
            $skor = $this->scoreDesa($kelompok, $desa, $tema, $bidang);

            // Jika desa pernah ditolak, turunkan skor ke nilai sangat rendah
            // (di bawah semua kandidat) tetapi tetap tampil sebagai info.
            $ditolakSebelumnya = $ditolak->has($desa->id);
            if ($ditolakSebelumnya) {
                $skor['total'] = 0.0;
            }

            $hasil[] = [
                'desa_id'            => $desa->id,
                'skor_tema'          => round($skor['tema'], 2),
                'skor_bidang'        => round($skor['bidang'], 2),
                'skor_prioritas'     => round($skor['prioritas'], 2),
                'skor_kebutuhan'     => round($skor['kebutuhan'], 2),
                'skor_total'         => round($skor['total'], 2),
                'flag_tumpang_tindih'=> $this->isTumpangTindih($kelompok, $desa),
                'ditolak_sebelumnya' => $ditolakSebelumnya,
                'alasan'             => $skor['alasan'],
            ];
        }

        // Urutkan skor_total menurun; skor sama → tampilkan lebih dulu yang non-tumpang tindih.
        usort($hasil, function ($a, $b) {
            if ($a['skor_total'] === $b['skor_total']) {
                return (int) $a['flag_tumpang_tindih'] - (int) $b['flag_tumpang_tindih'];
            }
            return $b['skor_total'] <=> $a['skor_total'];
        });

        return $hasil;
    }

    /**
     * Menjalankan matching: simpan hasil ke riwayat_matching (bulk) untuk satu kelompok.
     *
     * @return int jumlah desa yang dihitung
     */
    public function run(KelompokKkn $kelompok, ?int $byUserId = null): int
    {
        $hasil = $this->rank($kelompok);

        // Fallback aman: pakai user yang sedang login; bila tidak ada konteks
        // user valid, biarkan NULL (kolom kini nullable) daripada FK 0 yang invalid.
        if ($byUserId === null) {
            $byUserId = auth()->id();
        }
        $byUserId = $byUserId ?: null;

        DB::transaction(function () use ($kelompok, $hasil, $byUserId) {
            // Refresh riwayat: buang hasil lama (kandidat), PERTAHANKAN jejak
            // 'ditolak' agar desa yang pernah ditolak tidak muncul lagi di ranking.
            $kelompok->riwayatMatching()->where('status', '!=', 'ditolak')->delete();

            foreach ($hasil as $h) {
                // Desa yang pernah ditolak: perbarui baris ditolak yang dipertahankan
                // (skor total 0) tanpa membuat baris kandidat baru.
                if ($h['ditolak_sebelumnya']) {
                    $kelompok->riwayatMatching()
                        ->where('desa_id', $h['desa_id'])
                        ->where('status', 'ditolak')
                        ->update([
                            'skor_tema'           => $h['skor_tema'],
                            'skor_bidang'         => $h['skor_bidang'],
                            'skor_prioritas'      => $h['skor_prioritas'],
                            'skor_kebutuhan'      => $h['skor_kebutuhan'],
                            'skor_total'          => $h['skor_total'],
                            'flag_tumpang_tindih' => $h['flag_tumpang_tindih'],
                            'dijalankan_oleh'     => $byUserId,
                        ]);
                    continue;
                }

                RiwayatMatching::create([
                    'kelompok_kkn_id'     => $kelompok->id,
                    'desa_id'             => $h['desa_id'],
                    'skor_tema'           => $h['skor_tema'],
                    'skor_bidang'         => $h['skor_bidang'],
                    'skor_prioritas'      => $h['skor_prioritas'],
                    'skor_kebutuhan'      => $h['skor_kebutuhan'],
                    'skor_total'          => $h['skor_total'],
                    'flag_tumpang_tindih' => $h['flag_tumpang_tindih'],
                    'status'              => 'kandidat',
                    'dijalankan_oleh'     => $byUserId,
                ]);
            }

            // Kelompok siap lanjut ke verifikasi kecamatan bila ada desa KANDIDAT
            // yang layak (skor > 0). Bila semua desa sudah ditolak (skor 0),
            // biarkan status menunggu_matching — Bapperida perlu meninjau,
            // dan kecamatan tidak akan melihat kelompok tanpa desa terpilih.
            $adaKandidat = collect($hasil)->contains(fn ($h) => $h['skor_total'] > 0);
            if ($adaKandidat && in_array($kelompok->status, ['menunggu_matching', 'terverifikasi'], true)) {
                $kelompok->update(['status' => 'menunggu_verifikasi_kecamatan']);
            }

            // ActivityLog ditulis hanya bila ada user valid (user_id NOT NULL).
            if ($byUserId) {
                ActivityLog::create([
                    'user_id'    => $byUserId,
                    'aksi'       => 'jalankan_matching',
                    'deskripsi'  => "Menjalankan matching untuk kelompok {$kelompok->kode_kelompok} (tema: {$kelompok->tema}).",
                    'ip_address' => request()->ip(),
                ]);
            }
        });

        return count($hasil);
    }

    /* ------------------------------------------------------------------
     | Scoring internal
     * ------------------------------------------------------------------ */

    /**
     * @return array{tema: float, bidang: float, prioritas: float, kebutuhan: float, total: float, alasan: array<int,string>}
     */
    private function scoreDesa(KelompokKkn $kelompok, Desa $desa, string $tema, string $bidang): array
    {
        // Gabungkan seluruh teks desa yang relevan per dimensi.
        $teksKebutuhan = $desa->kebutuhan->pluck('kategori')->merge($desa->kebutuhan->pluck('deskripsi'))->implode(' ');
        $teksPotensi   = $desa->potensi->pluck('kategori')->merge($desa->potensi->pluck('deskripsi'))->implode(' ');
        $isu = \App\Models\IsuStrategis::where('wilayah_terdampak', $desa->nama_desa)
            ->orWhere('wilayah_terdampak', 'like', '%'.$desa->nama_desa.'%')
            ->get();
        $teksIsuRekom = $isu->pluck('rekomendasi_tema')->implode(' ');
        $teksIsuKat   = $isu->pluck('kategori_isu')->implode(' ');

        $temaSkor   = max(
            $this->similarity($tema, $teksIsuRekom),
            $this->similarity($tema, $teksKebutuhan)
        );
        $bidangSkor = max(
            $this->similarity($bidang, $teksPotensi),
            $this->similarity($bidang, $teksKebutuhan),
            $this->similarity($bidang, $teksIsuRekom)
        );
        $prioritasSkor = $this->similarity($tema, $teksIsuKat);
        $kebutuhanSkor = max(
            $this->similarity($tema, $teksKebutuhan),
            $this->similarity($bidang, $teksKebutuhan)
        );

        // Bonus kecil bila kebutuhan desa berprioritas tinggi (sangat perlu intervensi).
        $adaPrioritasTinggi = $desa->kebutuhan->contains(fn ($k) => in_array(strtolower((string) $k->prioritas), ['tinggi', 'urgent', 'sangat'], true));
        if ($adaPrioritasTinggi) {
            $kebutuhanSkor = min(100, $kebutuhanSkor + 10);
        }

        $total = $this->total($temaSkor, $bidangSkor, $prioritasSkor, $kebutuhanSkor);

        $alasan = [];
        if ($temaSkor > 0) {
            $alasan[] = "Tema kelompok cocok dengan rekomendasi tema/isu daerah.";
        }
        if ($bidangSkor > 0) {
            $alasan[] = "Bidang keilmuan sesuai potensi/kebutuhan desa.";
        }
        if ($prioritasSkor > 0) {
            $alasan[] = "Selaras dengan prioritas pembangunan daerah.";
        }
        if ($kebutuhanSkor > 0) {
            $alasan[] = "Memenuhi kebutuhan desa.";
        }

        return [
            'tema'      => $temaSkor,
            'bidang'    => $bidangSkor,
            'prioritas' => $prioritasSkor,
            'kebutuhan' => $kebutuhanSkor,
            'total'     => $total,
            'alasan'    => $alasan,
        ];
    }

    private function total(float $tema, float $bidang, float $prioritas, float $kebutuhan): float
    {
        return $tema * self::WEIGHTS['tema']
            + $bidang * self::WEIGHTS['bidang']
            + $prioritas * self::WEIGHTS['prioritas']
            + $kebutuhan * self::WEIGHTS['kebutuhan'];
    }

    /**
     * Desa dikatakan tumpang tindih bila sudah dipakai kelompok lain yang temanya
     * mirip (overlap kata ≥ 0.5) — cegah banyak KKN tema serupa di satu desa.
     */
    private function isTumpangTindih(KelompokKkn $kelompok, Desa $desa): bool
    {
        return $kelompok->permohonanKkn
            ->kelompokKkn
            ->where('desa_id', $desa->id)
            ->where('id', '!=', $kelompok->id)
            ->whereIn('status', ['menunggu_verifikasi_kecamatan', 'menunggu_persetujuan', 'aktif'])
            ->contains(fn ($k) => $this->similarity((string) $kelompok->tema, (string) $k->tema) >= 0.5);
    }

    /* ------------------------------------------------------------------
     | Util text similarity (keyword overlap)
     * ------------------------------------------------------------------ */

    /**
     * Skor 0–100: Jaccard-style overlap antara dua teks (set token).
     */
    private function similarity(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }
        $sa = $this->tokenSet($a);
        $sb = $this->tokenSet($b);
        if (count($sa) === 0 || count($sb) === 0) {
            return 0.0;
        }
        $irisan = count(array_intersect($sa, $sb));
        $gabung = count(array_unique(array_merge($sa, $sb)));

        return $gabung > 0 ? round(($irisan / $gabung) * 100, 2) : 0.0;
    }

    /**
     * @return array<int,string> set token normalisasi (lowercase, tanpa non-word, tanpa stopword umum)
     */
    private function tokenSet(string $text): array
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? $text;
        $tokens = preg_split('/\s+/', trim($text)) ?: [];
        $tokens = array_filter($tokens, fn ($t) => $t !== '');

        $stopwords = [
            'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'adalah',
            'ini', 'itu', 'atau', 'serta', 'akan', 'tidak', 'para', 'agar', 'guna',
            'program', 'pengembangan', 'desa', 'kkn', 'kuliah', 'kerja', 'nyata',
        ];

        return array_unique(array_values(array_diff($tokens, $stopwords)));
    }
}
