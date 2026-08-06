<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PerguruanTinggi;
use App\Notifications\ApprovalAkunNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerguruanTinggiApprovalController extends Controller
{
    /**
     * UC-01 — Daftar perguruan tinggi beserta status approval.
     */
    public function index(): View
    {
        return view('bapperida.perguruan-tinggi.index');
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel index — paginate JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $query = PerguruanTinggi::with('user')->withCount('permohonanKkn');

        // Filter status (menunggu/disetujui/ditolak).
        if ($request->filled('status') && in_array($request->status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $query->where('status_approval', $request->status);
        }

        $perguruanTinggi = $query->orderBy('created_at', 'desc')->paginate(10);

        $rows = $perguruanTinggi->getCollection()->map(function ($pt) {
            $showBtn = '<a href="'.route('bapperida.pt.show', $pt).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i> Detail</a>';
            return [
                'nama'       => '<a href="'.route('bapperida.pt.show', $pt).'" class="fw-semibold text-decoration-none">'.e($pt->nama_pt).'</a>',
                'pic'        => e($pt->pic_nama ?? '-'),
                'email'      => e($pt->pic_email ?? '-'),
                'status'     => view('components.status-badge', ['status' => $pt->status_approval])->render()
                              .($pt->status_approval === 'menunggu' ? ' <span class="badge badge-amber ms-1">Perlu Review</span>' : ''),
                'aksi'       => $showBtn,
                'status_raw' => $pt->status_approval,
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $perguruanTinggi->firstItem(),
            'per_page'     => $perguruanTinggi->perPage(),
            'total'        => $perguruanTinggi->total(),
            'current_page' => $perguruanTinggi->currentPage(),
            'last_page'    => $perguruanTinggi->lastPage(),
        ]);
    }

    /**
     * UC-01 — Detail perguruan tinggi + tombol approve/tolak.
     */
    public function show(PerguruanTinggi $perguruanTinggi): View
    {
        $perguruanTinggi->load('user', 'permohonanKkn');

        return view('bapperida.perguruan-tinggi.show', [
            'pt' => $perguruanTinggi,
        ]);
    }

    /**
     * UC-01 — Setujui akun PT. Akun langsung aktif mengajukan permohonan.
     */
    public function approve(PerguruanTinggi $perguruanTinggi): RedirectResponse
    {
        if ($perguruanTinggi->status_approval !== 'menunggu') {
            return back()->with('error', 'Status akun ini bukan "menunggu", tidak dapat disetujui lagi.');
        }

        $perguruanTinggi->update([
            'status_approval'   => 'disetujui',
            'catatan_penolakan' => null,
        ]);

        // Notifikasi in-app ke operator PT (SYS-01).
        $perguruanTinggi->user->notify(new ApprovalAkunNotification($perguruanTinggi));

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'approve_pt',
            'deskripsi'  => "Menyetujui registrasi Perguruan Tinggi: {$perguruanTinggi->nama_pt}.",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('bapperida.pt.index')
            ->with('success', "Akun \"{$perguruanTinggi->nama_pt}\" telah disetujui.");
    }

    /**
     * UC-01 — Tolak akun PT dengan catatan.
     */
    public function reject(PerguruanTinggi $perguruanTinggi, Request $request): RedirectResponse
    {
        if ($perguruanTinggi->status_approval !== 'menunggu') {
            return back()->with('error', 'Status akun ini bukan "menunggu", tidak dapat ditolak lagi.');
        }

        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        $perguruanTinggi->update([
            'status_approval'    => 'ditolak',
            'catatan_penolakan'  => $validated['catatan_penolakan'],
        ]);

        // Notifikasi in-app ke operator PT (SYS-01).
        $perguruanTinggi->user->notify(new ApprovalAkunNotification($perguruanTinggi));

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'tolak_pt',
            'deskripsi'  => "Menolak registrasi Perguruan Tinggi: {$perguruanTinggi->nama_pt}. Alasan: {$validated['catatan_penolakan']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('bapperida.pt.index')
            ->with('success', "Akun \"{$perguruanTinggi->nama_pt}\" ditolak.");
    }
}
