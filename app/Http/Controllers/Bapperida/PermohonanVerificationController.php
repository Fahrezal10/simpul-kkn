<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PermohonanKkn;
use App\Models\User;
use App\Notifications\PermohonanStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PermohonanVerificationController extends Controller
{
    /**
     * UC-05 — Daftar seluruh permohonan masuk dari semua PT.
     */
    public function index(): View
    {
        return view('bapperida.permohonan.index');
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel index — paginate JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $query = PermohonanKkn::with(['perguruanTinggi', 'kelompokKkn'])
            ->withCount('kelompokKkn');

        // Filter status.
        $allowed = ['diajukan', 'terverifikasi', 'ditolak'];
        if ($request->filled('status') && in_array($request->status, $allowed, true)) {
            $query->where('status', $request->status);
        }

        $permohonan = $query->orderBy('created_at', 'desc')->paginate(10);

        $rows = $permohonan->getCollection()->map(function ($p) {
            $isPending = $p->status === 'diajukan';
            $btn = $isPending
                ? '<a href="'.route('bapperida.permohonan.show', $p).'" class="btn btn-sm btn-primary"><i class="bi bi-clipboard-check me-1"></i> Review</a>'
                : '<a href="'.route('bapperida.permohonan.show', $p).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Detail</a>';
            return [
                'pt'       => e($p->perguruanTinggi->nama_pt ?? '-'),
                'periode'  => e($p->periode),
                'tanggal'  => $p->tanggal_mulai?->format('d M Y').' – '.$p->tanggal_selesai?->format('d M Y'),
                'kelompok' => '<span class="badge text-bg-secondary">'.$p->kelompok_kkn_count.'</span>',
                'status'   => view('components.status-badge', ['status' => $p->status])->render(),
                'aksi'     => $btn,
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $permohonan->firstItem(),
            'per_page'     => $permohonan->perPage(),
            'total'        => $permohonan->total(),
            'current_page' => $permohonan->currentPage(),
            'last_page'    => $permohonan->lastPage(),
        ]);
    }

    /**
     * UC-05 — Detail permohonan + form verifikasi/tolak.
     */
    public function show(PermohonanKkn $permohonan): View
    {
        $permohonan->load([
            'perguruanTinggi',
            'kelompokKkn.dosen',
            'kelompokKkn.mahasiswa',
        ]);

        return view('bapperida.permohonan.show', ['permohonan' => $permohonan]);
    }

    /**
     * UC-05 — Verifikasi permohonan (lengkap & sesuai) → status terverifikasi.
     * (Fase 2: trigger matching setelah status ini.)
     */
    public function verify(PermohonanKkn $permohonan): RedirectResponse
    {
        if ($permohonan->status !== 'diajukan') {
            return back()->with('error', 'Permohonan ini tidak dalam status "diajukan", tidak dapat diverifikasi.');
        }

        $permohonan->update([
            'status'             => 'terverifikasi',
            'verified_by'        => Auth::id(),
            'verified_at'        => now(),
            'catatan_verifikasi' => null,
        ]);

        // Notifikasi ke operator PT (SYS-01).
        $permohonan->perguruanTinggi->user->notify(new PermohonanStatusNotification($permohonan));

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'verifikasi_permohonan',
            'deskripsi'  => "Memverifikasi permohonan KKN periode {$permohonan->periode} dari {$permohonan->perguruanTinggi->nama_pt}.",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('bapperida.permohonan.index')
            ->with('success', "Permohonan periode {$permohonan->periode} telah diverifikasi.");
    }

    /**
     * UC-05 — Tolak permohonan dengan catatan.
     */
    public function reject(PermohonanKkn $permohonan, Request $request): RedirectResponse
    {
        if ($permohonan->status !== 'diajukan') {
            return back()->with('error', 'Permohonan ini tidak dapat ditolak lagi.');
        }

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:1000'],
        ]);

        $permohonan->update([
            'status'             => 'ditolak',
            'verified_by'        => Auth::id(),
            'verified_at'        => now(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'],
        ]);

        // Notifikasi ke operator PT (SYS-01).
        $permohonan->perguruanTinggi->user->notify(new PermohonanStatusNotification($permohonan));

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'tolak_permohonan',
            'deskripsi'  => "Menolak permohonan KKN periode {$permohonan->periode}. Alasan: {$validated['catatan_verifikasi']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('bapperida.permohonan.index')
            ->with('success', "Permohonan periode {$permohonan->periode} ditolak.");
    }
}