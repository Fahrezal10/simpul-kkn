<?php

namespace App\Http\Controllers\PerangkatDaerah;

use App\Http\Controllers\Controller;
use App\Models\IsuStrategis;
use App\Models\PerangkatDaerah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-10 — Input isu strategis oleh Operator Perangkat Daerah (OPD).
 *
 * Isu strategis menjadi parameter "Prioritas Daerah" pada Matching System.
 * Operator OPD hanya mengelola isu untuk instansinya sendiri (perangkat_daerah.user_id).
 */
class IsuStrategisController extends Controller
{
    private function opdMilikUser(): PerangkatDaerah
    {
        $opd = PerangkatDaerah::with('isuStrategis')
            ->where('user_id', Auth::id())
            ->first();

        abort_if(! $opd, 403, 'Akun ini belum terhubung ke perangkat daerah manapun. Hubungi Bapperida.');

        return $opd;
    }

    public function index(): View
    {
        $opd = $this->opdMilikUser();

        return view('perangkat-daerah.isu-strategis.index', compact('opd'));
    }

    public function data(Request $request): JsonResponse
    {
        $opd = $this->opdMilikUser();

        $query = $opd->isuStrategis()->getQuery();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('kategori_isu', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('wilayah_terdampak', 'like', "%{$search}%");
            });
        }

        $isu = $query->orderBy('created_at', 'desc')->paginate(10);

        $rows = $isu->getCollection()->map(function ($i) {
            return [
                'kategori'   => '<span class="badge text-bg-primary">'.e($i->kategori_isu).'</span>',
                'deskripsi'  => e($i->deskripsi),
                'wilayah'    => e($i->wilayah_terdampak ?: '-'),
                'rekomendasi'=> e($i->rekomendasi_tema ?: '-'),
                'aksi'       => '<form method="POST" action="'.route('perangkat-daerah.isu-strategis.destroy', $i).'" class="d-inline" onsubmit="return confirm(\'Hapus isu strategis ini?\');">'
                    .csrf_field().method_field('DELETE')
                    .'<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Hapus</button></form>',
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $isu->firstItem(),
            'per_page'     => $isu->perPage(),
            'total'        => $isu->total(),
            'current_page' => $isu->currentPage(),
            'last_page'    => $isu->lastPage(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $opd = $this->opdMilikUser();

        $validated = $request->validate([
            'kategori_isu'    => ['required', 'string', 'max:100'],
            'deskripsi'       => ['required', 'string', 'max:2000'],
            'wilayah_terdampak'=> ['nullable', 'string', 'max:255'],
            'rekomendasi_tema' => ['nullable', 'string', 'max:255'],
        ]);

        $opd->isuStrategis()->create($validated);

        return back()->with('success', 'Isu strategis berhasil ditambahkan.');
    }

    public function destroy(IsuStrategis $isu): RedirectResponse
    {
        // Ownership: pastikan isu milik OPD yang login.
        if ($isu->perangkat_daerah_id !== $this->opdMilikUser()->id) {
            abort(403, 'Isu strategis ini bukan milik instansi Anda.');
        }

        $isu->delete();

        return back()->with('success', 'Isu strategis dihapus.');
    }
}
