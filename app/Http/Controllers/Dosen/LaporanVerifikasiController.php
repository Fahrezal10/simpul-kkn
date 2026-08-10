<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\LaporanAkhir;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-15 (verifikasi) — DPL melihat & memverifikasi laporan akhir kelompok bimbingan.
 */
class LaporanVerifikasiController extends Controller
{
    private function dosenMilikUser(): Dosen
    {
        $dosen = Dosen::with('kelompokKkn')->where('user_id', Auth::id())->first();

        abort_if(! $dosen, 403, 'Akun ini belum terhubung ke data dosen.');

        return $dosen;
    }

    public function index(): View
    {
        return view('dosen.laporan-akhir.index');
    }

    public function data(): JsonResponse
    {
        $dosen = $this->dosenMilikUser();
        $kelompokIds = $dosen->kelompokKkn->pluck('id');

        $laporan = LaporanAkhir::whereIn('kelompok_kkn_id', $kelompokIds)
            ->with(['kelompokKkn', 'verifier'])
            ->latest()
            ->get();

        $rows = $laporan->map(function ($l) {
            $btn = $l->status === 'menunggu'
                ? '<a href="'.route('dosen.laporan-akhir.show', $l).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Review</a>'
                : '<a href="'.route('dosen.laporan-akhir.show', $l).'" class="btn btn-sm btn-light"><i class="bi bi-eye me-1"></i> Lihat</a>';
            return [
                'kelompok' => '<strong>'.e($l->kelompokKkn->kode_kelompok).'</strong>',
                'uploaded' => $l->uploaded_at?->format('d M Y H:i'),
                'status'   => view('components.status-badge', ['status' => $l->status])->render(),
                'aksi'     => $btn,
            ];
        });

        return response()->json(['data' => $rows->values(), 'total' => $rows->count()]);
    }

    public function show(LaporanAkhir $laporan): View
    {
        $this->pastikanBimbingan($laporan);
        $laporan->load(['kelompokKkn', 'uploader', 'verifier']);

        return view('dosen.laporan-akhir.show', ['laporan' => $laporan]);
    }

    public function approve(LaporanAkhir $laporan): RedirectResponse
    {
        $this->pastikanBimbingan($laporan);

        $laporan->update([
            'status'            => 'disetujui',
            'verified_by'       => Auth::id(),
            'verified_at'       => now(),
            'catatan_verifikasi'=> null,
        ]);

        return back()->with('success', 'Laporan akhir disetujui.');
    }

    public function revisi(Request $request, LaporanAkhir $laporan): RedirectResponse
    {
        $this->pastikanBimbingan($laporan);

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:1000'],
        ]);

        $laporan->update([
            'status'            => 'revisi',
            'catatan_verifikasi'=> $validated['catatan_verifikasi'],
            'verified_by'       => null,
            'verified_at'       => null,
        ]);

        return back()->with('success', 'Laporan diminta revisi.');
    }

    private function pastikanBimbingan(LaporanAkhir $laporan): void
    {
        $dosen = $this->dosenMilikUser();
        $kelompokIds = $dosen->kelompokKkn->pluck('id');

        abort_if(! $kelompokIds->contains($laporan->kelompok_kkn_id), 403, 'Laporan ini bukan dari kelompok bimbingan Anda.');
    }
}
