<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Logbook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-16 — Approve logbook oleh Dosen Pembimbing Lapangan (DPL).
 *
 * DPL meninjau logbook harian mahasiswa yang berada di kelompok bimbingannya
 * lalu approve atau tolak (revisi) dengan catatan.
 */
class LogbookApprovalController extends Controller
{
    private function dosenMilikUser(): Dosen
    {
        $dosen = Dosen::with('kelompokKkn')->where('user_id', Auth::id())->first();

        abort_if(! $dosen, 403, 'Akun ini belum terhubung ke data dosen.');

        return $dosen;
    }

    public function index(): View
    {
        return view('dosen.logbook.index');
    }

    /**
     * Sumber data AJAX — logbook menunggu approval dari kelompok bimbingan.
     */
    public function data(Request $request): JsonResponse
    {
        $dosen = $this->dosenMilikUser();
        $kelompokIds = $dosen->kelompokKkn->pluck('id');

        $query = Logbook::whereIn('kelompok_kkn_id', $kelompokIds)
            ->where('status', 'menunggu')
            ->with(['mahasiswa', 'kelompokKkn']);

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('mahasiswa', fn ($q) => $q->where('nama', 'like', "%{$search}%"));
        }

        $logbook = $query->orderBy('tanggal', 'desc')->paginate(10);

        $rows = $logbook->getCollection()->map(function ($l) {
            return [
                'tanggal'  => $l->tanggal->format('d M Y'),
                'kelompok' => '<strong>'.e($l->kelompokKkn->kode_kelompok).'</strong>',
                'mahasiswa'=> e($l->mahasiswa->nama ?? '-'),
                'deskripsi'=> e($l->deskripsi_kegiatan),
                'foto'     => $l->foto ? '<a href="'.route('file.download', ['jenis' => 'logbook', 'path' => $l->foto]).'" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-image me-1"></i>Lihat</a>' : '-',
                'aksi'     => '<a href="'.route('dosen.logbook.show', $l).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Review</a>',
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $logbook->firstItem(),
            'per_page'     => $logbook->perPage(),
            'total'        => $logbook->total(),
            'current_page' => $logbook->currentPage(),
            'last_page'    => $logbook->lastPage(),
        ]);
    }

    /**
     * Detail logbook untuk review DPL + form approve/revisi.
     */
    public function show(Logbook $logbook): View
    {
        $this->pastikanBimbingan($logbook);

        $logbook->load(['mahasiswa', 'kelompokKkn', 'approver']);

        return view('dosen.logbook.show', ['logbook' => $logbook]);
    }

    public function approve(Logbook $logbook): RedirectResponse
    {
        $this->pastikanBimbingan($logbook);

        $logbook->update([
            'status'      => 'disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'catatan_dpl' => null,
        ]);

        return back()->with('success', 'Logbook disetujui.');
    }

    public function revisi(Request $request, Logbook $logbook): RedirectResponse
    {
        $this->pastikanBimbingan($logbook);

        $validated = $request->validate([
            'catatan_dpl' => ['required', 'string', 'max:1000'],
        ]);

        $logbook->update([
            'status'      => 'revisi',
            'catatan_dpl' => $validated['catatan_dpl'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'Logbook ditolak untuk revisi.');
    }

    private function pastikanBimbingan(Logbook $logbook): void
    {
        $dosen = $this->dosenMilikUser();
        $kelompokIds = $dosen->kelompokKkn->pluck('id');

        abort_if(! $kelompokIds->contains($logbook->kelompok_kkn_id), 403, 'Logbook ini bukan dari kelompok bimbingan Anda.');
    }
}
