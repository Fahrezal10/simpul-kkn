<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\EvaluasiDesa;
use App\Models\KelompokKkn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-13 — Evaluasi kelompok mahasiswa oleh Operator Desa.
 *
 * Desa menilai kelompok KKN yang sedang/penah bertugas di desanya
 * (kualitas program, manfaat, kedisiplinan). Satu evaluasi per kelompok.
 */
class EvaluasiDesaController extends Controller
{
    private function desaMilikUser(): Desa
    {
        $desa = Desa::where('user_id', Auth::id())->first();

        abort_if(! $desa, 403, 'Akun ini belum terhubung ke desa manapun. Hubungi Bapperida.');

        return $desa;
    }

    public function index(): View
    {
        return view('desa.evaluasi.index');
    }

    /**
     * Sumber data — kelompok yang bertugas di desa ini + status evaluasi.
     */
    public function data(): JsonResponse
    {
        $desa = $this->desaMilikUser();

        $kelompok = KelompokKkn::where('desa_id', $desa->id)
            ->whereIn('status', ['aktif', 'selesai'])
            ->with(['dosen', 'permohonanKkn.perguruanTinggi'])
            ->with(['evaluasiDesa' => fn ($q) => $q->where('desa_id', $desa->id)])
            ->get();

        $rows = $kelompok->map(function ($k) use ($desa) {
            $eval = $k->evaluasiDesa->first();
            return [
                'kode'   => '<strong>'.e($k->kode_kelompok).'</strong>',
                'pt'     => e($k->permohonanKkn->perguruanTinggi->nama_pt ?? '-'),
                'dpl'    => e($k->dosen->nama ?? '-'),
                'status' => view('components.status-badge', ['status' => $k->status])->render(),
                'evaluasi' => $eval
                    ? '<span class="badge text-bg-success">Sudah</span> <small class="text-muted">rata-rata '.round(($eval->skor_kualitas_program + $eval->skor_manfaat + $eval->skor_kedisiplinan) / 3, 1).'</small>'
                    : '<span class="badge text-bg-light border">Belum</span>',
                'aksi'  => '<a href="'.route('desa.evaluasi.show', $k).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-star me-1"></i> Evaluasi</a>',
            ];
        });

        return response()->json(['data' => $rows->values(), 'total' => $rows->count()]);
    }

    public function show(KelompokKkn $kelompokKkn): View
    {
        $desa = $this->desaMilikUser();
        abort_if($kelompokKkn->desa_id !== $desa->id, 403, 'Kelompok ini tidak bertugas di desa Anda.');

        $kelompokKkn->load(['dosen', 'permohonanKkn.perguruanTinggi']);
        $evaluasi = EvaluasiDesa::where('kelompok_kkn_id', $kelompokKkn->id)
            ->where('desa_id', $desa->id)->first();

        return view('desa.evaluasi.show', ['kelompok' => $kelompokKkn, 'evaluasi' => $evaluasi]);
    }

    public function store(Request $request, KelompokKkn $kelompokKkn): RedirectResponse
    {
        $desa = $this->desaMilikUser();
        abort_if($kelompokKkn->desa_id !== $desa->id, 403, 'Kelompok ini tidak bertugas di desa Anda.');

        $validated = $request->validate([
            'skor_kualitas_program' => ['required', 'integer', 'between:1,5'],
            'skor_manfaat'          => ['required', 'integer', 'between:1,5'],
            'skor_kedisiplinan'     => ['required', 'integer', 'between:1,5'],
            'catatan'               => ['nullable', 'string', 'max:1000'],
        ]);

        EvaluasiDesa::updateOrCreate(
            ['kelompok_kkn_id' => $kelompokKkn->id, 'desa_id' => $desa->id],
            $validated
        );

        return back()->with('success', 'Evaluasi desa untuk kelompok '.$kelompokKkn->kode_kelompok.' disimpan.');
    }
}
