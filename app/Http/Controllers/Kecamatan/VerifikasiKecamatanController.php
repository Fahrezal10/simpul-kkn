<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\KelompokKkn;
use App\Models\Kecamatan;
use App\Models\VerifikasiKecamatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-11 — Verifikasi kesiapan desa oleh Operator Kecamatan.
 *
 * Bapperida memilih desa kandidat dari hasil matching → kelompok berstatus
 * "menunggu_verifikasi_kecamatan". Operator kecamatan (yang membawahi desa
 * terpilih) memverifikasi kesiapan desa: siap / tidak_siap + catatan.
 */
class VerifikasiKecamatanController extends Controller
{
    private function kecamatanMilikUser(): Kecamatan
    {
        $kecamatan = Kecamatan::where('user_id', Auth::id())->first();

        abort_if(! $kecamatan, 403, 'Akun ini belum terhubung ke kecamatan manapun. Hubungi Bapperida.');

        return $kecamatan;
    }

    /**
     * Daftar kelompok yang menunggu verifikasi kesiapan desa di kecamatan ini.
     */
    public function index(): View
    {
        return view('kecamatan.verifikasi.index');
    }

    /**
     * Sumber data AJAX — kelompok menunggu verifikasi di kecamatan ini.
     */
    public function data(Request $request): JsonResponse
    {
        $kecamatan = $this->kecamatanMilikUser();

        $query = KelompokKkn::query()
            ->where('status', 'menunggu_verifikasi_kecamatan')
            ->whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))
            ->with([
                'dosen',
                'permohonanKkn.perguruanTinggi',
                'desa',
                'riwayatMatching' => fn ($q) => $q->where('status', 'dipilih'),
            ]);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_kelompok', 'like', "%{$search}%")
                    ->orWhere('tema', 'like', "%{$search}%")
                    ->orWhereHas('desa', fn ($d) => $d->where('nama_desa', 'like', "%{$search}%"));
            });
        }

        $kelompok = $query->orderBy('created_at', 'desc')->paginate(10);

        $rows = $kelompok->getCollection()->map(function ($k) {
            $dipilih = $k->riwayatMatching->first();
            $desa = $k->desa;
            $desaLabel = $desa
                ? e($desa->nama_desa)
                    .'<div class="small text-muted">'.e($desa->kecamatan->nama_kecamatan ?? '-').'</div>'
                : '-';
            return [
                'kode'   => '<strong>'.e($k->kode_kelompok).'</strong>',
                'pt'     => e($k->permohonanKkn->perguruanTinggi->nama_pt ?? '-'),
                'tema'   => e($k->tema),
                'desa'   => $desaLabel,
                'skor'   => $dipilih ? number_format($dipilih->skor_total, 0) : '-',
                'aksi'   => '<a href="'.route('kecamatan.verifikasi.show', $k).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-clipboard-check me-1"></i> Verifikasi</a>',
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $kelompok->firstItem(),
            'per_page'     => $kelompok->perPage(),
            'total'        => $kelompok->total(),
            'current_page' => $kelompok->currentPage(),
            'last_page'    => $kelompok->lastPage(),
        ]);
    }

    /**
     * Detail kelompok + form verifikasi kesiapan desa.
     */
    public function show(KelompokKkn $kelompokKkn): View
    {
        $this->pastikanDiKecamatan($kelompokKkn);

        $kelompokKkn->load([
            'dosen',
            'permohonanKkn.perguruanTinggi',
            'desa.kecamatan',
            'riwayatMatching' => fn ($q) => $q->orderBy('skor_total', 'desc'),
            'verifikasiKecamatan',
        ]);

        return view('kecamatan.verifikasi.show', ['kelompok' => $kelompokKkn]);
    }

    /**
     * Simpan hasil verifikasi kesiapan desa.
     */
    public function store(Request $request, KelompokKkn $kelompokKkn): RedirectResponse
    {
        $this->pastikanDiKecamatan($kelompokKkn);

        if ($kelompokKkn->status !== 'menunggu_verifikasi_kecamatan') {
            return back()->with('error', 'Kelompok ini tidak dalam status menunggu verifikasi kecamatan.');
        }

        $validated = $request->validate([
            'status'  => ['required', 'in:siap,tidak_siap'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $kecamatan = $this->kecamatanMilikUser();

        VerifikasiKecamatan::updateOrCreate(
            ['kelompok_kkn_id' => $kelompokKkn->id, 'desa_id' => $kelompokKkn->desa_id],
            [
                'kecamatan_id' => $kecamatan->id,
                'status'       => $validated['status'],
                'catatan'      => $validated['catatan'] ?? null,
                'verified_by'  => Auth::id(),
                'verified_at'  => now(),
            ]
        );

        // Status kelompok lanjut ke Bapperida (menunggu persetujuan akhir) atau
        // kembali ke matching (desa tidak siap → Bapperida pilih desa lain).
        $isSiap = $validated['status'] === 'siap';
        if (! $isSiap) {
            // Tandai desa sebagai 'ditolak' agar tidak muncul lagi di re-matching.
            $kelompokKkn->riwayatMatching()
                ->where('status', 'dipilih')
                ->update(['status' => 'ditolak']);
            $kelompokKkn->update(['status' => 'menunggu_matching', 'desa_id' => null]);
        } else {
            $kelompokKkn->update(['status' => 'menunggu_persetujuan']);
        }

        $label = $validated['status'] === 'siap' ? 'siap' : 'tidak siap';

        return back()->with('success', "Desa {$kelompokKkn->desa->nama_desa} diverifikasi {$label}.");
    }

    /**
     * Pastikan kelompok terpilih berada di kecamatan milik user yang login.
     */
    private function pastikanDiKecamatan(KelompokKkn $kelompokKkn): void
    {
        $kecamatan = $this->kecamatanMilikUser();
        $desaKecamatanId = $kelompokKkn->desa?->kecamatan_id;

        abort_if($desaKecamatanId !== $kecamatan->id, 403, 'Desa ini bukan wilayah kecamatan Anda.');
    }
}
