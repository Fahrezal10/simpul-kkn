<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KelompokKkn;
use App\Notifications\KelompokStatusNotification;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Bapperida\MonitoringController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-07 — Persetujuan akhir pelaksanaan KKN oleh Bapperida.
 *
 * Kecamatan telah memverifikasi kesiapan desa (UC-11) → kelompok berstatus
 * "menunggu_persetujuan". Bapperida review hasil verifikasi lalu:
 *  - Approve  → status "aktif" (KKN resmi berjalan, mahasiswa bisa isi logbook)
 *  - Tolak    → kembali "menunggu_matching" (Bapperida pilih desa alternatif)
 */
class ApprovalFinalController extends Controller
{
    public function index(): View
    {
        return view('bapperida.approval-final.index');
    }

    /**
     * Sumber data AJAX — kelompok menunggu persetujuan akhir.
     */
    public function data(): JsonResponse
    {
        $query = KelompokKkn::query()
            ->where('status', 'menunggu_persetujuan')
            ->with([
                'dosen',
                'permohonanKkn.perguruanTinggi',
                'desa.kecamatan',
                'verifikasiKecamatan',
                'riwayatMatching' => fn ($q) => $q->where('status', 'dipilih'),
            ]);

        $kelompok = $query->orderBy('updated_at', 'desc')->paginate(10);

        $rows = $kelompok->getCollection()->map(function ($k) {
            $verif = $k->verifikasiKecamatan->last();
            $dipilih = $k->riwayatMatching->first();
            $desa = $k->desa;
            $desaLabel = $desa
                ? e($desa->nama_desa)
                    .'<div class="small text-muted">'.e($desa->kecamatan->nama_kecamatan ?? '-').'</div>'
                : '-';

            return [
                'kode'  => '<strong>'.e($k->kode_kelompok).'</strong>',
                'pt'    => e($k->permohonanKkn->perguruanTinggi->nama_pt ?? '-'),
                'tema'  => e($k->tema),
                'desa'  => $desaLabel,
                'verif' => $verif
                    ? view('components.status-badge', ['status' => $verif->status])->render()
                        .'<div class="small text-muted">'.e($verif->verifier->nama ?? '-').'</div>'
                    : '<span class="text-muted">-</span>',
                'skor'  => $dipilih ? number_format($dipilih->skor_total, 0) : '-',
                'aksi'  => '<a href="'.route('bapperida.approval-final.show', $k).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-circle me-1"></i> Review</a>',
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
     * Detail hasil verifikasi kecamatan + tombol approve/tolak.
     */
    public function show(KelompokKkn $kelompokKkn): View
    {
        $kelompokKkn->load([
            'dosen',
            'permohonanKkn.perguruanTinggi',
            'desa.kecamatan',
            'verifikasiKecamatan.verifier',
            'riwayatMatching' => fn ($q) => $q->orderBy('skor_total', 'desc'),
        ]);

        return view('bapperida.approval-final.show', ['kelompok' => $kelompokKkn]);
    }

    /**
     * Setujui → kelompok berstatus aktif, KKN resmi berjalan.
     */
    public function approve(KelompokKkn $kelompokKkn): RedirectResponse
    {
        if ($kelompokKkn->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Kelompok ini tidak dalam status menunggu persetujuan.');
        }
        if (! $kelompokKkn->desa_id) {
            return back()->with('error', 'Kelompok belum memiliki lokasi desa terpilih.');
        }

        $kelompokKkn->update(['status' => 'aktif']);

        // Jumlah kelompok aktif berubah → buang cache agregasi monitoring.
        MonitoringController::flushCache();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'setujui_pelaksanaan',
            'deskripsi'  => "Menyetujui pelaksanaan KKN kelompok {$kelompokKkn->kode_kelompok} di desa {$kelompokKkn->desa->nama_desa}.",
            'ip_address' => request()->ip(),
        ]);

        // SYS-01: notifikasi ke semua pihak terkait.
        $this->notifPelaksanaan($kelompokKkn, 'disetujui');

        return back()->with('success', "Kelompok {$kelompokKkn->kode_kelompok} disetujui dan berstatus Aktif.");
    }

    /**
     * Tolak lokasi → kembali ke matching untuk pilih desa alternatif.
     */
    public function tolak(KelompokKkn $kelompokKkn): RedirectResponse
    {
        if ($kelompokKkn->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Kelompok ini tidak dalam status menunggu persetujuan.');
        }

        // Batalkan pilihan desa: tandai desa terpilih sebagai 'ditolak' agar
        // tidak muncul lagi di ranking re-matching (enum riwayat_matching.status
        // mendukung 'ditolak').
        $kelompokKkn->riwayatMatching()
            ->where('status', 'dipilih')
            ->update(['status' => 'ditolak']);
        $kelompokKkn->update(['status' => 'menunggu_matching', 'desa_id' => null]);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'tolak_pelaksanaan',
            'deskripsi'  => "Menolak lokasi kelompok {$kelompokKkn->kode_kelompok}; kembali ke tahap matching.",
            'ip_address' => request()->ip(),
        ]);

        $this->notifPelaksanaan($kelompokKkn, 'ditolak');

        return back()->with('success', "Lokasi kelompok {$kelompokKkn->kode_kelompok} ditolak; kembali ke matching.");
    }

    /**
     * SYS-01 — Kirim notifikasi status pelaksanaan ke operator PT & DPL.
     */
    private function notifPelaksanaan(KelompokKkn $kelompokKkn, string $status): void
    {
        $kelompokKkn->load('permohonanKkn.perguruanTinggi.user', 'dosen');

        $statusLabel = $status === 'disetujui' ? 'aktif' : 'menunggu_matching';

        // Operator PT.
        if ($userPt = $kelompokKkn->permohonanKkn?->perguruanTinggi?->user) {
            $userPt->notify(new KelompokStatusNotification($kelompokKkn, $statusLabel));
        }

        // DPL (dosen) — relasi user via email dosen (belum ada FK user di dosen).
        if ($dpl = $kelompokKkn->dosen) {
            \App\Models\User::where('email', $dpl->email)->get()
                ->each(fn ($u) => $u->notify(new KelompokStatusNotification($kelompokKkn, $statusLabel)));
        }
    }
}
