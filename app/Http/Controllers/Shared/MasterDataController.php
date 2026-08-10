<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * UC-08 — Kelola Master Data (CRUD generik).
 *
 * Satu controller generik yang mengelola beberapa jenis data referensi sederhana
 * yang belum punya modul khusus: kecamatan & perangkat daerah.
 *
 * Jenis data yang SUDAH punya modul khusus TIDAK diduplikasi di sini:
 *   - desa  → `bapperida.desa.*`   (CRUD kaya + profil/potensi/permasalahan/kebutuhan)
 *   - PT    → `bapperida.pt.*`     (alur persetujuan akun)
 *   - isu strategis → `perangkat-daerah.isu-strategis.*` (input oleh OPD)
 *
 * Struktur kolom tiap jenis didefinisikan deklaratif di `registry()` sehingga
 * field form & tabel dirender otomatis (warna/thema sesuai design system).
 */
class MasterDataController extends Controller
{
    /**
     * Daftar jenis master data yang dikelola di modul ini.
     *
     * `columns`: [key, label, type(text|number|textarea|select|date), required,
     *            max, unique, search (ikut pencarian), options (utk select)]
     *
     * `guard`: closure(bool $model) => string|null — pesan pemblokiran hapus bila
     *          data masih direferensikan relasi lain (UC-08 alternate flow 3a).
     *
     * @return array<string, array>
     */
    private function registry(): array
    {
        return [
            'kecamatan' => [
                'model'    => \App\Models\Kecamatan::class,
                'label'    => 'Kecamatan',
                'icon'     => 'bi-diagram-3',
                'subtitle' => 'Data wilayah kecamatan se-Kabupaten Indramayu — referensi untuk desa & verifikasi.',
                'columns'  => [
                    [
                        'key'      => 'nama_kecamatan',
                        'label'    => 'Nama Kecamatan',
                        'type'     => 'text',
                        'required' => true,
                        'max'      => 100,
                        'search'   => true,
                    ],
                    [
                        'key'      => 'kode_wilayah',
                        'label'    => 'Kode Wilayah',
                        'type'     => 'text',
                        'required' => true,
                        'max'      => 20,
                        'unique'   => true,
                        'search'   => true,
                        'hint'     => 'Diisi kode BPS, mis. 3212001.',
                    ],
                ],
                'guard' => fn ($kecamatan) => $kecamatan->desa()->exists()
                    ? "Kecamatan {$kecamatan->nama_kecamatan} tidak dapat dihapus karena masih memiliki data desa."
                    : null,
            ],

            'perangkat-daerah' => [
                'model'    => \App\Models\PerangkatDaerah::class,
                'label'    => 'Perangkat Daerah',
                'icon'     => 'bi-building',
                'subtitle' => 'Daftar OPD di Kabupaten Indramayu — pemberi isu strategis untuk Matching KKN.',
                'columns'  => [
                    [
                        'key'      => 'nama_opd',
                        'label'    => 'Nama OPD',
                        'type'     => 'text',
                        'required' => true,
                        'max'      => 255,
                        'search'   => true,
                    ],
                    [
                        'key'      => 'bidang_tugas',
                        'label'    => 'Bidang Tugas',
                        'type'     => 'textarea',
                        'required' => false,
                        'max'      => 1000,
                    ],
                ],
                'guard' => fn ($opd) => $opd->isuStrategis()->exists()
                    ? "Perangkat Daerah {$opd->nama_opd} tidak dapat dihapus karena masih memiliki isu strategis."
                    : null,
            ],
        ];
    }

    /**
     * Halaman utama modul (picker jenis) ATAU halaman CRUD per jenis.
     */
    public function index(?string $jenis = null): View
    {
        $semuaJenis = collect($this->registry())->map(function ($def, $slug) {
            $def['slug'] = $slug;
            return $def;
        });

        // Tanpa jenis → halaman pemilihan master data.
        if ($jenis === null) {
            return view('shared.master-data.index', [
                'semuaJenis' => $semuaJenis,
                'jenis'      => null,
                'jenisDef'   => null,
            ]);
        }

        $def = $this->resolveOrAbort($jenis);

        return view('shared.master-data.index', [
            'semuaJenis' => $semuaJenis,
            'jenis'      => $jenis,
            'jenisDef'   => $def,
        ]);
    }

    /**
     * Sumber data AJAX (server-side) untuk tabel CRUD satu jenis — paginate JSON.
     */
    public function data(Request $request, string $jenis): JsonResponse
    {
        $def = $this->resolveOrAbort($jenis);
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = ($def['model'])::query();

        // Pencarian teks lintas kolom yang diizinkan search.
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($def, $search) {
                $first = true;
                foreach ($def['columns'] as $col) {
                    if (empty($col['search'])) {
                        continue;
                    }
                    if ($first) {
                        $q->where($col['key'], 'like', "%{$search}%");
                        $first = false;
                    } else {
                        $q->orWhere($col['key'], 'like', "%{$search}%");
                    }
                }
            });
        }

        $data = $query->orderBy($def['columns'][0]['key'])->paginate(10);

        $rows = $data->getCollection()->map(function ($model) use ($def) {
            $row = ['id' => $model->id, '_aksi' => $model->id];
            foreach ($def['columns'] as $col) {
                $row[$col['key']] = (string) $model->{$col['key']};
            }
            return $row;
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $data->firstItem(),
            'per_page'     => $data->perPage(),
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
        ]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        $def = $this->resolveOrAbort($jenis);
        $validated = $this->validateFields($request, $def);

        $model = ($def['model'])::create($validated);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => "tambah_{$jenis}",
            'deskripsi'  => "Menambah {$def['label']} \"{$this->namaUtama($model, $def)}\".",
            'ip_address' => request()->ip(),
        ]);

        $label = $this->namaUtama($model, $def);

        return redirect()->route('master-data.list', $jenis)
            ->with('success', "{$def['label']} \"{$label}\" berhasil ditambahkan.");
    }

    public function update(Request $request, string $jenis, int $id): RedirectResponse
    {
        $def = $this->resolveOrAbort($jenis);
        $model = ($def['model'])::query()->findOrFail($id);
        $validated = $this->validateFields($request, $def, $model);

        $model->update($validated);

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => "ubah_{$jenis}",
            'deskripsi'  => "Mengubah {$def['label']} \"{$this->namaUtama($model, $def)}\".",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('master-data.list', $jenis)
            ->with('success', "Data {$def['label']} \"{$this->namaUtama($model, $def)}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, string $jenis, int $id): RedirectResponse
    {
        $def = $this->resolveOrAbort($jenis);
        $model = ($def['model'])::query()->findOrFail($id);

        // UC-08 alternate flow 3a: cegah hapus bila masih dipakai relasi lain.
        if (isset($def['guard']) && ($pesan = $def['guard']($model))) {
            return back()->with('error', $pesan);
        }

        $name = $this->namaUtama($model, $def);
        $model->delete();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => "hapus_{$jenis}",
            'deskripsi'  => "Menghapus {$def['label']} \"{$name}\".",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('master-data.list', $jenis)
            ->with('success', "{$def['label']} \"{$name}\" berhasil dihapus.");
    }

    /* ------------------------------------------------------------------
     | Helper internal
     * ------------------------------------------------------------------ */

    private function resolveOrAbort(string $jenis): ?array
    {
        $def = $this->registry()[$jenis] ?? null;
        abort_if($def === null, 404, "Jenis master data \"{$jenis}\" tidak dikenal.");

        return $def;
    }

    private function namaUtama($model, array $def): string
    {
        $key = $def['columns'][0]['key'];

        return (string) $model->{$key};
    }

    /**
     * Susun aturan validasi dari deklarasi kolom, lalu validasi.
     */
    private function validateFields(Request $request, array $def, $ignore = null): array
    {
        $rules = [];
        $attributes = [];

        foreach ($def['columns'] as $col) {
            $type = $col['type'] ?? 'text';
            $colRules = [];

            $colRules[] = !empty($col['required']) ? 'required' : 'nullable';
            $colRules[] = 'string';
            if ($type === 'number') {
                $colRules[] = 'numeric';
            }
            if (!empty($col['max'])) {
                $colRules[] = "max:{$col['max']}";
            }
            if (!empty($col['unique'])) {
                $table = ($def['model'])::query()->getModel()->getTable();
                $unique = Rule::unique($table, $col['key']);
                if ($ignore) {
                    $unique->ignore($ignore->id);
                }
                $colRules[] = $unique;
            }
            if ($type === 'select' && !empty($col['options'])) {
                $colRules[] = Rule::in(array_keys($col['options']));
            }

            $rules[$col['key']] = $colRules;
            $attributes[$col['key']] = $col['label'];
        }

        return Validator::make($request->all(), $rules, [], $attributes)->validate();
    }
}