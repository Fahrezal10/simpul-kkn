<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\KelompokKkn;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * UC-09 (GIS) — Dashboard peta interaktif Leaflet.
 *
 * Menampilkan marker desa dari `desa` (lat/long). Klik marker → info desa
 * (profil, potensi, kebutuhan) + kelompok KKN yang bertugas di sana.
 * Data di-serve via endpoint JSON untuk di-render oleh Leaflet di sisi klien.
 */
class DashboardGisController extends Controller
{
    public function index(): View
    {
        return view('dashboard.gis');
    }

    /**
     * Endpoint JSON: seluruh desa + kelompok aktif di dalamnya.
     */
    public function data(): JsonResponse
    {
        $desas = Desa::with(['kecamatan', 'potensi', 'kebutuhan'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Kelompok aktif per desa (sekali query, hindari N+1).
        $kelompokPerDesa = KelompokKkn::where('status', 'aktif')
            ->whereIn('desa_id', $desas->pluck('id'))
            ->get()
            ->groupBy('desa_id');

        $features = $desas->map(function ($desa) use ($kelompokPerDesa) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type'        => 'Point',
                    'coordinates' => [(float) $desa->longitude, (float) $desa->latitude],
                ],
                'properties' => [
                    'id'         => $desa->id,
                    'nama_desa'  => $desa->nama_desa,
                    'kecamatan'  => $desa->kecamatan->nama_kecamatan ?? '-',
                    'kode'       => $desa->kode_wilayah,
                    'penduduk'   => $desa->jumlah_penduduk,
                    'profil'     => \Illuminate\Support\Str::limit($desa->profil_umum, 200),
                    'potensi'    => $desa->potensi->pluck('kategori')->take(3)->values(),
                    'kebutuhan'  => $desa->kebutuhan->pluck('kategori')->take(3)->values(),
                    'kelompok'   => $kelompokPerDesa->get($desa->id, collect())
                        ->map(fn ($k) => [
                            'kode' => $k->kode_kelompok,
                            'tema' => $k->tema,
                        ])->values(),
                ],
            ];
        });

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features->values(),
            'meta'     => [
                'total_desa'      => $desas->count(),
                'total_kelompok'  => $kelompokPerDesa->flatten()->count(),
            ],
        ]);
    }
}
