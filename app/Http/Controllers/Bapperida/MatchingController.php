<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\KelompokKkn;
use App\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MatchingController extends Controller
{
    public function __construct(
        private readonly MatchingService $matchingService,
    ) {
    }

    /**
     * UC-06 — Daftar kelompok KKN dari permohonan terverifikasi untuk di-match.
     */
    public function index(): View
    {
        return view('bapperida.matching.index');
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel index — paginate JSON.
     * Hanya menampilkan kelompok dari permohonan yang sudah terverifikasi.
     */
    public function data(Request $request): JsonResponse
    {
        $query = KelompokKkn::query()
            ->whereHas('permohonanKkn', fn ($q) => $q->where('status', 'terverifikasi'))
            ->with([
                'dosen',
                'permohonanKkn.perguruanTinggi',
                'riwayatMatching' => fn ($q) => $q->select('kelompok_kkn_id', 'skor_total', 'status'),
            ]);

        // Filter status.
        $allowed = ['menunggu_matching', 'menunggu_verifikasi_kecamatan', 'menunggu_persetujuan', 'aktif'];
        if ($request->filled('status') && in_array($request->status, $allowed, true)) {
            $query->where('status', $request->status);
        }

        $kelompok = $query->orderBy('created_at', 'desc')->paginate(10);

        $rows = $kelompok->getCollection()->map(function ($k) {
            $punyaHasil = $k->riwayatMatching->isNotEmpty();
            $dipilih = $k->riwayatMatching->firstWhere('status', 'dipilih');

            $btnRun = $k->status === 'menunggu_matching'
                ? '<form method="POST" action="'.route('bapperida.matching.run', $k).'" class="d-inline">'
                    .csrf_field()
                    .'<button class="btn btn-sm btn-primary" onclick="return confirm(\'Jalankan matching untuk kelompok '.e($k->kode_kelompok).'?\')">'
                    .'<i class="bi bi-magic me-1"></i> Jalankan</button></form>'
                : '';

            $btnShow = '<a href="'.route('bapperida.matching.show', $k).'" class="btn btn-sm btn-outline-primary">'
                .'<i class="bi bi-list-ol me-1"></i> Ranking</a>';

            return [
                'kode'       => e($k->kode_kelompok),
                'pt'         => e($k->permohonanKkn->perguruanTinggi->nama_pt ?? '-'),
                'dpl'        => e($k->dosen->nama ?? '-'),
                'tema'       => e($k->tema),
                'mahasiswa'  => $k->jumlah_mahasiswa,
                'hasil'      => $punyaHasil
                    ? '<span class="badge text-bg-info">'.count($k->riwayatMatching).' kandidat</span>'
                        .($dipilih ? ' <span class="badge text-bg-success">dipilih</span>' : '')
                    : '<span class="text-muted">-</span>',
                'status'     => view('components.status-badge', ['status' => $k->status])->render(),
                'aksi'       => $btnRun.$btnShow,
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
     * UC-06 — Jalankan matching untuk satu kelompok (per-kelompok, bukan batch).
     *
     * Catatan: parameter bernama $kelompokKkn agar cocok dengan route
     * `{kelompokKkn}` (implicit route-model binding Laravel).
     */
    public function run(KelompokKkn $kelompokKkn): RedirectResponse
    {
        if ($kelompokKkn->status !== 'menunggu_matching') {
            return back()->with('error', 'Kelompok '.$kelompokKkn->kode_kelompok.' tidak berstatus "menunggu matching".');
        }
        if ($kelompokKkn->permohonanKkn->status !== 'terverifikasi') {
            return back()->with('error', 'Permohonan induk belum terverifikasi; matching tidak dapat dijalankan.');
        }

        $jumlah = $this->matchingService->run($kelompokKkn, Auth::id());

        return redirect()->route('bapperida.matching.show', $kelompokKkn)
            ->with('success', "Matching selesai: {$jumlah} desa dinilai untuk kelompok {$kelompokKkn->kode_kelompok}.");
    }

    /**
     * UC-06 — Detail hasil ranking matching satu kelompok.
     */
    public function show(KelompokKkn $kelompokKkn): View
    {
        $kelompokKkn->load([
            'dosen',
            'permohonanKkn.perguruanTinggi',
            'riwayatMatching' => fn ($q) => $q->orderBy('skor_total', 'desc')->with(['desa.kecamatan']),
        ]);

        return view('bapperida.matching.show', ['kelompok' => $kelompokKkn]);
    }

    /**
     * BP-04 — Override: pilih satu desa sebagai lokasi final untuk kelompok.
     * Catatan: hanya menyimpan pilihan (status 'dipilih' + desa_id pada kelompok).
     * Penetapan status "Aktif" dikerjakan di branch verifikasi-kecamatan.
     */
    public function override(KelompokKkn $kelompokKkn, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
        ]);

        $dipilih = $kelompokKkn->riwayatMatching()->where('desa_id', $validated['desa_id'])->first();

        if (! $dipilih) {
            return back()->with('error', 'Desa tersebut tidak ada dalam hasil matching kelompok ini.');
        }
        if ($dipilih->status === 'ditolak') {
            return back()->with('error', 'Desa ini pernah ditolak/tidak siap dan tidak dapat dipilih.');
        }

        // Satu 'dipilih', sisanya kembali 'kandidat' (jejak 'ditolak' dipertahankan).
        $kelompokKkn->riwayatMatching()
            ->where('status', '!=', 'ditolak')
            ->update(['status' => 'kandidat']);
        $dipilih->update(['status' => 'dipilih']);
        $kelompokKkn->update(['desa_id' => $validated['desa_id']]);

        return back()->with('success', "Lokasi desa untuk kelompok {$kelompokKkn->kode_kelompok} dipilih.");
    }

    /**
     * Batal pilih — kembalikan seluruh hasil ke kandidat dan kosongkan desa_id kelompok.
     */
    public function batalPilih(KelompokKkn $kelompokKkn): RedirectResponse
    {
        // Kembalikan ke kandidat; jejak 'ditolak' tetap dipertahankan.
        $kelompokKkn->riwayatMatching()
            ->where('status', '!=', 'ditolak')
            ->update(['status' => 'kandidat']);
        $kelompokKkn->update(['desa_id' => null]);

        return back()->with('success', "Pilihan lokasi kelompok {$kelompokKkn->kode_kelompok} dibatalkan.");
    }
}
