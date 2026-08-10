<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\DesaKebutuhan;
use App\Models\DesaPermasalahan;
use App\Models\DesaPotensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-12 — Kelola profil & potensi desa oleh Operator Desa.
 *
 * Operator hanya mengelola profil desa miliknya sendiri (desa.user_id = user
 * yang login). Potensi, permasalahan, dan kebutuhan adalah parameter input
 * Matching System ("Kebutuhan Desa").
 */
class ProfilDesaController extends Controller
{
    private function desaMilikUser(): Desa
    {
        $desa = Desa::with(['kecamatan', 'potensi', 'permasalahan', 'kebutuhan'])
            ->where('user_id', Auth::id())
            ->first();

        abort_if(! $desa, 403, 'Akun ini belum terhubung ke desa manapun. Hubungi Bapperida.');

        return $desa;
    }

    public function index(): View
    {
        $desa = $this->desaMilikUser();

        return view('desa.profil.index', compact('desa'));
    }

    public function edit(): View
    {
        $desa = $this->desaMilikUser();

        return view('desa.profil.form', compact('desa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $desa = $this->desaMilikUser();

        $validated = $request->validate([
            'jumlah_penduduk' => ['nullable', 'integer', 'min:0'],
            'luas_wilayah'    => ['nullable', 'numeric', 'min:0'],
            'profil_umum'     => ['nullable', 'string', 'max:5000'],
        ]);

        $desa->update($validated);

        return redirect()->route('desa.profil.index')
            ->with('success', 'Profil desa berhasil diperbarui.');
    }

    /* ------------------------------------------------------------------
     | Potensi
     * ------------------------------------------------------------------ */

    public function potensiStore(Request $request): RedirectResponse
    {
        $desa = $this->desaMilikUser();

        $validated = $request->validate([
            'kategori'  => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        $desa->potensi()->create($validated);

        return back()->with('success', 'Potensi desa ditambahkan.');
    }

    public function potensiDestroy(Request $request, DesaPotensi $potensi): RedirectResponse
    {
        $this->pastikanMilikDesa($potensi->desa_id);
        $potensi->delete();

        return back()->with('success', 'Potensi desa dihapus.');
    }

    /* ------------------------------------------------------------------
     | Permasalahan
     * ------------------------------------------------------------------ */

    public function permasalahanStore(Request $request): RedirectResponse
    {
        $desa = $this->desaMilikUser();

        $validated = $request->validate([
            'kategori'  => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:2000'],
        ]);

        $desa->permasalahan()->create($validated);

        return back()->with('success', 'Permasalahan desa ditambahkan.');
    }

    public function permasalahanDestroy(Request $request, DesaPermasalahan $permasalahan): RedirectResponse
    {
        $this->pastikanMilikDesa($permasalahan->desa_id);
        $permasalahan->delete();

        return back()->with('success', 'Permasalahan desa dihapus.');
    }

    /* ------------------------------------------------------------------
     | Kebutuhan
     * ------------------------------------------------------------------ */

    public function kebutuhanStore(Request $request): RedirectResponse
    {
        $desa = $this->desaMilikUser();

        $validated = $request->validate([
            'kategori'  => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
        ]);

        $desa->kebutuhan()->create($validated);

        return back()->with('success', 'Kebutuhan desa ditambahkan.');
    }

    public function kebutuhanDestroy(Request $request, DesaKebutuhan $kebutuhan): RedirectResponse
    {
        $this->pastikanMilikDesa($kebutuhan->desa_id);
        $kebutuhan->delete();

        return back()->with('success', 'Kebutuhan desa dihapus.');
    }

    /**
     * Cegah operator desa A menghapus data desa B (ownership check).
     */
    private function pastikanMilikDesa(int $desaId): void
    {
        $desa = $this->desaMilikUser();
        abort_if($desaId !== $desa->id, 403, 'Data ini bukan milik desa Anda.');
    }
}
