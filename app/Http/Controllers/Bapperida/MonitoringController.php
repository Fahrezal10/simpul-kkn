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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * UC-09 (monitoring) — Dashboard monitoring & evaluasi pelaksanaan KKN.
 *
 * Agregasi menyeluruh untuk Bapperida: jumlah mahasiswa, kelompok aktif,
 * desa aktif, statistik evaluasi (rata-rata skor desa & DPL), dan distribusi
 * kelompok per status.
 */
class MonitoringController extends Controller
{
    public function index(): View
    {
        $stats = [
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

        return view('bapperida.monitoring.index', compact('stats'));
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
