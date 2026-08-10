<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\IsuStrategis;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PerguruanTinggi;
use App\Models\PermohonanKkn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Dashboard utama setelah login — ringkasan statistik per-role (UC-09).
 *
 * Data dipersempit sesuai cakupan role:
 *  - Bapperida/superadmin → seluruh sistem
 *  - Perguruan Tinggi     → permohonan & kelompok milik PT sendiri
 *  - Kecamatan            → desa & kelompok di wilayahnya
 *  - Desa                 → data desa miliknya
 *  - Perangkat Daerah     → isu strategis milik OPD-nya
 *  - Mahasiswa/DPL        → info dasar (modul Fase 3)
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $roleSlug = $user->roleSlug();
        $roleLabel = str_replace('-', ' ', ucwords($roleSlug));

        $stats = match ($roleSlug) {
            'bapperida', 'superadmin' => $this->statGlobal(),
            'perguruan-tinggi'         => $this->statPerguruanTinggi($user),
            'kecamatan'                => $this->statKecamatan($user),
            'desa'                     => $this->statDesa($user),
            'perangkat-daerah'         => $this->statPerangkatDaerah($user),
            'mahasiswa'                => $this->statMahasiswa($user),
            'dosen'                    => $this->statDosen($user),
            default                    => $this->statKosong(),
        };

        return view('dashboard.index', [
            'roleSlug'  => $roleSlug,
            'roleLabel' => $roleLabel,
            'user'      => $user,
            'stats'     => $stats,
        ]);
    }

    /* ------------------------------------------------------------------
     | Agregasi per-role
     * ------------------------------------------------------------------ */

    private function statGlobal(): array
    {
        return [
            'label'      => 'Seluruh Sistem',
            'pt'         => PerguruanTinggi::count(),
            'permohonan' => PermohonanKkn::count(),
            'desa'       => Desa::count(),
            'mahasiswa'  => Mahasiswa::count(),
            'kelompok'   => $this->kelompokPerStatus(KelompokKkn::query()),
        ];
    }

    private function statPerguruanTinggi($user): array
    {
        $pt = $user->perguruanTinggi;
        $permohonanIds = $pt?->permohonanKkn()?->pluck('id') ?? collect();

        return [
            'label'      => 'Perguruan Tinggi Anda',
            'pt'         => 1,
            'permohonan' => $permohonanIds->count(),
            'desa'       => null,
            'mahasiswa'  => Mahasiswa::whereHas('kelompokKkn', fn ($q) => $q->whereIn('permohonan_kkn_id', $permohonanIds))->count(),
            'kelompok'   => $this->kelompokPerStatus(KelompokKkn::whereIn('permohonan_kkn_id', $permohonanIds)),
        ];
    }

    private function statKecamatan($user): array
    {
        $kecamatan = $user->kecamatan;

        return [
            'label'    => 'Kecamatan '.($kecamatan?->nama_kecamatan ?? ''),
            'desa'     => $kecamatan ? $kecamatan->desa()->count() : 0,
            'kelompok' => $this->kelompokPerStatus(
                KelompokKkn::whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan?->id))
            ),
        ];
    }

    private function statDesa($user): array
    {
        $desa = $user->desa;

        return [
            'label'     => 'Desa '.($desa?->nama_desa ?? ''),
            'desa'      => 1,
            'potensi'   => $desa?->potensi()->count() ?? 0,
            'kebutuhan' => $desa?->kebutuhan()->count() ?? 0,
            'kelompok'  => $this->kelompokPerStatus(
                KelompokKkn::where('desa_id', $desa?->id)
            ),
        ];
    }

    private function statPerangkatDaerah($user): array
    {
        $opd = $user->perangkatDaerah;

        return [
            'label' => $opd?->nama_opd ?? '',
            'isu'   => $opd ? IsuStrategis::where('perangkat_daerah_id', $opd->id)->count() : 0,
        ];
    }

    private function statMahasiswa($user): array
    {
        $mahasiswa = $user->mahasiswa;

        return [
            'label'    => 'Kelompok KKN Anda',
            'kelompok' => $this->kelompokPerStatus(
                KelompokKkn::where('id', $mahasiswa?->kelompok_kkn_id)
            ),
            'kelompokKode' => $mahasiswa?->kelompokKkn?->kode_kelompok,
        ];
    }

    private function statDosen($user): array
    {
        $dosen = $user->dosen;
        $kelompokIds = $dosen?->kelompokKkn()->pluck('id') ?? collect();

        return [
            'label'    => 'Kelompok Bimbingan Anda',
            'kelompok' => $this->kelompokPerStatus(KelompokKkn::whereIn('id', $kelompokIds)),
        ];
    }

    private function statKosong(): array
    {
        return [
            'label'    => 'Info Akun',
            'kelompok' => $this->kelompokPerStatus(KelompokKkn::query()),
        ];
    }

    /* ------------------------------------------------------------------
     | Helper — kelompok per status untuk query (global atau terbatas)
     * ------------------------------------------------------------------ */

    private function kelompokPerStatus(Builder $query): array
    {
        $statuses = ['menunggu_matching', 'menunggu_verifikasi_kecamatan', 'menunggu_persetujuan', 'aktif'];

        $counts = (clone $query)
            ->selectRaw('status, count(*) as total')
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
}
