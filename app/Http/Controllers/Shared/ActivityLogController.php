<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SYS-02 — Aktivitas Sistem (audit trail).
 *
 * Menampilkan jejak aksi pengguna (tambah/ubah/hapus desa, jalankan matching,
 * verifikasi, approval, dst.) yang ditulis ke tabel `activity_log` di seluruh
 * modul. Khusus role Bapperida (superadmin).
 */
class ActivityLogController extends Controller
{
    public function index(): View
    {
        return view('shared.activity-log.index');
    }

    /**
     * Sumber data AJAX (server-side) — paginate JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user');

        // Filter pencarian deskripsi / aksi / nama user.
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                    ->orWhere('aksi', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$search}%"));
            });
        }

        // Filter aksi spesifik.
        if ($request->filled('aksi') && $request->input('aksi') !== '') {
            $query->where('aksi', $request->input('aksi'));
        }

        $logs = $query->orderByDesc('id')->paginate(15);

        // Label aksi untuk filter dropdown (aksi yang pernah tercatat).
        $aksiList = ActivityLog::query()->distinct()->orderBy('aksi')->pluck('aksi');

        $rows = $logs->getCollection()->map(function ($log) {
            return [
                'waktu'    => $log->created_at->format('d M Y H:i'),
                'user'     => e($log->user?->nama ?: 'Sistem'),
                'role'     => e($log->user?->role?->nama_role ?? '-'),
                'aksi'     => '<span class="badge text-bg-light border">'.e($log->aksi).'</span>',
                'deskripsi'=> e($log->deskripsi),
                'ip'       => e($log->ip_address ?: '-'),
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $logs->firstItem(),
            'per_page'     => $logs->perPage(),
            'total'        => $logs->total(),
            'current_page' => $logs->currentPage(),
            'last_page'    => $logs->lastPage(),
            'filters'      => ['aksi' => $aksiList],
        ]);
    }
}