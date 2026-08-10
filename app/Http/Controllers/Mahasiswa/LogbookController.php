<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-14 — Isi logbook harian oleh Mahasiswa.
 *
 * Hanya mahasiswa yang kelompok KKN-nya berstatus "aktif" (UC-07) yang boleh
 * mengisi logbook. Satu logbook per mahasiswa per tanggal (unique constraint).
 */
class LogbookController extends Controller
{
    private function mahasiswaMilikUser(): Mahasiswa
    {
        $mahasiswa = Mahasiswa::with('kelompokKkn')->where('user_id', Auth::id())->first();

        abort_if(! $mahasiswa, 403, 'Akun ini belum terhubung ke data mahasiswa.');

        return $mahasiswa;
    }

    public function index(): View
    {
        return view('mahasiswa.logbook.index');
    }

    /**
     * Sumber data AJAX — daftar logbook milik mahasiswa login.
     */
    public function data(Request $request): JsonResponse
    {
        $mahasiswa = $this->mahasiswaMilikUser();

        $query = Logbook::where('mahasiswa_id', $mahasiswa->id);

        // Filter status.
        if ($request->filled('status') && in_array($request->status, ['menunggu', 'disetujui', 'revisi'], true)) {
            $query->where('status', $request->status);
        }

        $logbook = $query->orderBy('tanggal', 'desc')->paginate(10);

        $rows = $logbook->getCollection()->map(function ($l) {
            return [
                'tanggal' => $l->tanggal->format('d M Y'),
                'deskripsi'=> e($l->deskripsi_kegiatan),
                'foto'    => $l->foto ? '<a href="'.asset('storage/'.$l->foto).'" target="_blank"><span class="badge text-bg-light border"><i class="bi bi-image me-1"></i>Foto</span></a>' : '-',
                'status'  => view('components.status-badge', ['status' => $l->status])->render(),
                'catatan' => e($l->catatan_dpl ?: '-'),
            ];
        });

        return response()->json([
            'data'         => $rows,
            'from'         => $logbook->firstItem(),
            'per_page'     => $logbook->perPage(),
            'total'        => $logbook->total(),
            'current_page' => $logbook->currentPage(),
            'last_page'    => $logbook->lastPage(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $mahasiswa = $this->mahasiswaMilikUser();

        // Hanya kelompok aktif yang boleh mengisi logbook.
        abort_if($mahasiswa->kelompokKkn?->status !== 'aktif', 403, 'Kelompok KKN Anda belum berstatus Aktif.');

        $validated = $request->validate([
            'tanggal'           => ['required', 'date', 'before_or_equal:today'],
            'deskripsi_kegiatan'=> ['required', 'string', 'max:2000'],
            'foto'              => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        // Cegah duplikat (unique per mahasiswa per tanggal).
        // Pakai whereDate agar cocok lintas driver (MySQL simpan datetime).
        $sudahAda = Logbook::where('mahasiswa_id', $mahasiswa->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('logbook', 'public');
        }

        // Logbook revisi (status 'revisi') boleh di-submit ulang — perbarui baris
        // yang ada kembali ke 'menunggu'. Logbook lain (menunggu/disetujui) ditolak.
        if ($sudahAda && $sudahAda->status !== 'revisi') {
            return back()->with('error', 'Logbook untuk tanggal tersebut sudah diisi.');
        }

        if ($sudahAda && $sudahAda->status === 'revisi') {
            $sudahAda->update([
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
                'foto'               => $fotoPath ?: $sudahAda->foto,
                'status'             => 'menunggu',
                'catatan_dpl'        => null,
                'approved_by'        => null,
                'approved_at'        => null,
            ]);
        } else {
            Logbook::create([
                'kelompok_kkn_id'    => $mahasiswa->kelompok_kkn_id,
                'mahasiswa_id'       => $mahasiswa->id,
                'tanggal'            => $validated['tanggal'],
                'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
                'foto'               => $fotoPath,
                'status'             => 'menunggu',
            ]);
        }

        return back()->with('success', 'Logbook harian berhasil disimpan dan menunggu approval DPL.');
    }
}
