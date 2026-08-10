<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-12 (master data) — CRUD desa oleh Bapperida.
 *
 * Bapperida memegang penuh data master wilayah (desa/kecamatan) — lihat
 * docs/00-design-system.md §4 Role Matrix ("Manajemen master data: Full CRUD").
 * Operator desa kelola profil desanya sendiri di modul terpisah (UC-12 Desa).
 */
class DesaController extends Controller
{
    public function index(): View
    {
        return view('bapperida.desa.index');
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel index — paginate JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $query = Desa::with('kecamatan')
            ->withCount(['potensi', 'permasalahan', 'kebutuhan']);

        // Filter pencarian nama desa / kode wilayah / kecamatan.
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_desa', 'like', "%{$search}%")
                    ->orWhere('kode_wilayah', 'like', "%{$search}%")
                    ->orWhereHas('kecamatan', fn ($k) => $k->where('nama_kecamatan', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->integer('kecamatan_id'));
        }

        $desas = $query->orderBy('kecamatan_id')->orderBy('nama_desa')->paginate(10);

        $rows = $desas->getCollection()->map(function ($d) {
            return [
                'kode_wilayah' => e($d->kode_wilayah),
                'nama_desa'    => '<a href="'.route('bapperida.desa.show', $d).'">'.e($d->nama_desa).'</a>'
                    .'<div class="small text-muted">'.$d->kecamatan->nama_kecamatan ?? '-'.'</div>',
                'penduduk'     => $d->jumlah_penduduk ? number_format($d->jumlah_penduduk, 0, ',', '.') : '-',
                'luas'         => $d->luas_wilayah !== null ? $d->luas_wilayah.' km²' : '-',
                'data'         => '<span class="badge text-bg-light border">'.$d->potensi_count.' potensi</span> '
                    .'<span class="badge text-bg-light border">'.$d->permasalahan_count.' masalah</span> '
                    .'<span class="badge text-bg-light border">'.$d->kebutuhan_count.' kebutuhan</span>',
                'aksi'         => '<a href="'.route('bapperida.desa.edit', $d).'" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit</a>',
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $desas->firstItem(),
            'per_page'     => $desas->perPage(),
            'total'        => $desas->total(),
            'current_page' => $desas->currentPage(),
            'last_page'    => $desas->lastPage(),
            'filters'      => ['kecamatan' => Kecamatan::orderBy('nama_kecamatan')->get(['id', 'nama_kecamatan'])],
        ]);
    }

    public function create(): View
    {
        return view('bapperida.desa.form', [
            'desa'       => new Desa(),
            'kecamatans' => Kecamatan::orderBy('nama_kecamatan')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDesa($request);

        $desa = Desa::create($validated);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'tambah_desa',
            'deskripsi'  => "Menambah desa {$desa->nama_desa} (kode {$desa->kode_wilayah}).",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('bapperida.desa.show', $desa)
            ->with('success', "Desa {$desa->nama_desa} berhasil ditambahkan.");
    }

    public function show(Desa $desa): View
    {
        $desa->load(['kecamatan', 'potensi', 'permasalahan', 'kebutuhan']);

        return view('bapperida.desa.show', ['desa' => $desa]);
    }

    public function edit(Desa $desa): View
    {
        return view('bapperida.desa.form', [
            'desa'       => $desa,
            'kecamatans' => Kecamatan::orderBy('nama_kecamatan')->get(),
        ]);
    }

    public function update(Request $request, Desa $desa): RedirectResponse
    {
        $validated = $this->validateDesa($request, $desa->id);

        $desa->update($validated);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'ubah_desa',
            'deskripsi'  => "Mengubah data desa {$desa->nama_desa}.",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('bapperida.desa.show', $desa)
            ->with('success', "Data desa {$desa->nama_desa} berhasil diperbarui.");
    }

    public function destroy(Request $request, Desa $desa): RedirectResponse
    {
        // Jangan hapus desa yang sudah jadi lokasi kelompok KKN (integritas alur).
        if ($desa->kelompokKkn()->exists()) {
            return back()->with('error', "Desa {$desa->nama_desa} tidak dapat dihapus karena sudah menjadi lokasi kelompok KKN.");
        }

        $nama = $desa->nama_desa;
        $desa->delete();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'hapus_desa',
            'deskripsi'  => "Menghapus desa {$nama}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('bapperida.desa.index')
            ->with('success', "Desa {$nama} berhasil dihapus.");
    }

    private function validateDesa(Request $request, ?int $ignoreId = null): array
    {
        $kodeRule = ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'];
        if ($ignoreId !== null) {
            $kodeRule[] = \Illuminate\Validation\Rule::unique('desa', 'kode_wilayah')->ignore($ignoreId);
        } else {
            $kodeRule[] = 'unique:desa,kode_wilayah';
        }

        return $request->validate([
            'kecamatan_id'   => ['required', 'integer', 'exists:kecamatan,id'],
            'kode_wilayah'   => $kodeRule,
            'nama_desa'      => ['required', 'string', 'max:100'],
            'jumlah_penduduk'=> ['nullable', 'integer', 'min:0'],
            'luas_wilayah'   => ['nullable', 'numeric', 'min:0'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'profil_umum'    => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
