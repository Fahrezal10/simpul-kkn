<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\EvaluasiDpl;
use App\Models\KelompokKkn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-17 — Evaluasi kelompok oleh DPL.
 *
 * DPL memberi penilaian akhir (0-100) terhadap kelompok bimbingannya.
 * Satu evaluasi per kelompok.
 */
class EvaluasiDplController extends Controller
{
    private function dosenMilikUser(): Dosen
    {
        $dosen = Dosen::with('kelompokKkn')->where('user_id', Auth::id())->first();

        abort_if(! $dosen, 403, 'Akun ini belum terhubung ke data dosen.');

        return $dosen;
    }

    public function index(): View
    {
        return view('dosen.evaluasi.index');
    }

    public function data(): JsonResponse
    {
        $dosen = $this->dosenMilikUser();
        $kelompokIds = $dosen->kelompokKkn->pluck('id');

        $kelompok = KelompokKkn::whereIn('id', $kelompokIds)
            ->whereIn('status', ['aktif', 'selesai'])
            ->with(['permohonanKkn.perguruanTinggi'])
            ->with(['evaluasiDpl' => fn ($q) => $q->where('dosen_id', $dosen->id)])
            ->get();

        $rows = $kelompok->map(function ($k) use ($dosen) {
            $eval = $k->evaluasiDpl->first();
            return [
                'kode'   => '<strong>'.e($k->kode_kelompok).'</strong>',
                'pt'     => e($k->permohonanKkn->perguruanTinggi->nama_pt ?? '-'),
                'tema'   => e($k->tema),
                'status' => view('components.status-badge', ['status' => $k->status])->render(),
                'evaluasi' => $eval
                    ? '<span class="badge text-bg-success">'.$eval->nilai.'</span>'
                    : '<span class="badge text-bg-light border">Belum</span>',
                'aksi'  => '<a href="'.route('dosen.evaluasi.show', $k).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-star me-1"></i> Evaluasi</a>',
            ];
        });

        return response()->json(['data' => $rows->values(), 'total' => $rows->count()]);
    }

    public function show(KelompokKkn $kelompokKkn): View
    {
        $dosen = $this->dosenMilikUser();
        abort_if(! $dosen->kelompokKkn->pluck('id')->contains($kelompokKkn->id), 403, 'Kelompok ini bukan bimbingan Anda.');

        $kelompokKkn->load(['permohonanKkn.perguruanTinggi']);
        $evaluasi = EvaluasiDpl::where('kelompok_kkn_id', $kelompokKkn->id)
            ->where('dosen_id', $dosen->id)->first();

        return view('dosen.evaluasi.show', ['kelompok' => $kelompokKkn, 'evaluasi' => $evaluasi]);
    }

    public function store(Request $request, KelompokKkn $kelompokKkn): RedirectResponse
    {
        $dosen = $this->dosenMilikUser();
        abort_if(! $dosen->kelompokKkn->pluck('id')->contains($kelompokKkn->id), 403, 'Kelompok ini bukan bimbingan Anda.');

        $validated = $request->validate([
            'nilai'   => ['required', 'integer', 'between:0,100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        EvaluasiDpl::updateOrCreate(
            ['kelompok_kkn_id' => $kelompokKkn->id, 'dosen_id' => $dosen->id],
            $validated
        );

        // Agregasi evaluasi di dashboard monitoring berubah → buang cache.
        \App\Http\Controllers\Bapperida\MonitoringController::flushCache();

        return back()->with('success', 'Evaluasi DPL untuk kelompok '.$kelompokKkn->kode_kelompok.' disimpan.');
    }
}
