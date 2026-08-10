<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\EvaluasiDesa;
use App\Models\EvaluasiDpl;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PerguruanTinggi;
use App\Models\PermohonanKkn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * UC-09 (monitoring) — Dashboard monitoring & evaluasi pelaksanaan KKN.
 *
 * Agregasi menyeluruh untuk Bapperida: jumlah mahasiswa, kelompok aktif,
 * desa aktif, statistik evaluasi (rata-rata skor desa & DPL), dan distribusi
 * kelompok per status.
 *
 * Hasil agregasi di-cache 2 menit (load-testing §5: target < 3 detik untuk
 * akses 10-15 user bersamaan). Cache di-invalidasi setiap kali terjadi
 * perubahan data yang memengaruhi agregat (lihat BapperidaService::flushMonitoringCache
 * yang dipanggil setelah approval, evaluasi, dan penutupan periode).
 */
class MonitoringController extends Controller
{
    private const CACHE_KEY = 'monitoring_stats';

    public function index(): View
    {
        $stats = Cache::remember(self::CACHE_KEY, 120, function () {
            return [
                'pt'          => PerguruanTinggi::count(),
                'permohonan'  => PermohonanKkn::count(),
                'desa'        => Desa::count(),
                'desa_aktif'  => Desa::whereHas('kelompokKkn', fn ($q) => $q->where('status', 'aktif'))->count(),
                'mahasiswa'   => Mahasiswa::count(),
                'kelompok'    => $this->kelompokPerStatus(),
                'kelompok_aktif' => KelompokKkn::where('status', 'aktif')->count(),
                'evaluasi_desa' => $this->agregasiEvaluasiDesa(),
                'evaluasi_dpl'  => $this->agregasiEvaluasiDpl(),
                'kelompok_per_pt' => $this->kelompokPerPt(),
            ];
        });

        return view('bapperida.monitoring.index', compact('stats'));
    }

    /**
     * Buang cache agregasi monitoring. Dipanggil dari BapperidaService setelah
     * aksi yang mengubah data lintas agregat (approval, evaluasi, penutupan periode).
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function kelompokPerStatus(): array
    {
        $statuses = ['menunggu_matching', 'menunggu_verifikasi_kecamatan', 'menunggu_persetujuan', 'aktif'];

        $counts = KelompokKkn::selectRaw('status, count(*) as total')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];
        foreach ($statuses as $s) {
            $result[$s] = (int) ($counts[$s] ?? 0);
        }
        $result['total'] = (int) $counts->sum();

        return $result;
    }

    private function agregasiEvaluasiDesa(): array
    {
        $row = EvaluasiDesa::selectRaw('
                COALESCE(AVG(skor_kualitas_program), 0) as kualitas,
                COALESCE(AVG(skor_manfaat), 0) as manfaat,
                COALESCE(AVG(skor_kedisiplinan), 0) as kedisiplinan,
                COUNT(*) as jumlah
            ')
            ->first();

        return [
            'kualitas'     => round((float) $row->kualitas, 1),
            'manfaat'      => round((float) $row->manfaat, 1),
            'kedisiplinan' => round((float) $row->kedisiplinan, 1),
            'jumlah'       => (int) $row->jumlah,
        ];
    }

    private function agregasiEvaluasiDpl(): array
    {
        $row = EvaluasiDpl::selectRaw('
                COALESCE(AVG(nilai), 0) as rata_rata,
                COUNT(*) as jumlah
            ')
            ->first();

        return [
            'rata_rata' => round((float) $row->rata_rata, 1),
            'jumlah'    => (int) $row->jumlah,
        ];
    }

    private function kelompokPerPt(): \Illuminate\Support\Collection
    {
        return KelompokKkn::query()
            ->where('status', 'aktif')
            ->with('permohonanKkn.perguruanTinggi')
            ->get()
            ->groupBy(fn ($k) => $k->permohonanKkn?->perguruanTinggi?->nama_pt ?? 'Lainnya')
            ->map(fn ($g) => $g->count())
            ->sortDesc()
            ->take(8);
    }
}
