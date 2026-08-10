<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LaporanAkhir;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-15 — Upload laporan akhir & luaran kegiatan oleh Mahasiswa.
 *
 * Hanya mahasiswa dari kelompok yang berstatus "aktif" dapat meng-upload.
 * Laporan di-upload sekali per kelompok (perwakilan); status menunggu verifikasi DPL.
 */
class LaporanAkhirController extends Controller
{
    private function mahasiswaMilikUser(): Mahasiswa
    {
        $mahasiswa = Mahasiswa::with('kelompokKkn')->where('user_id', Auth::id())->first();

        abort_if(! $mahasiswa, 403, 'Akun ini belum terhubung ke data mahasiswa.');

        return $mahasiswa;
    }

    public function index(): View
    {
        return view('mahasiswa.laporan-akhir.index');
    }

    public function data(): JsonResponse
    {
        $mahasiswa = $this->mahasiswaMilikUser();

        $laporan = LaporanAkhir::where('kelompok_kkn_id', $mahasiswa->kelompok_kkn_id)
            ->with('verifier')
            ->latest()
            ->get();

        $rows = $laporan->map(function ($l) {
            return [
                'file_laporan' => '<a href="'.route('file.download', ['jenis' => 'laporan-akhir', 'path' => $l->file_laporan]).'" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Laporan</a>',
                'file_luaran'  => $l->file_luaran ? '<a href="'.route('file.download', ['jenis' => 'laporan-akhir', 'path' => $l->file_luaran]).'" target="_blank"><i class="bi bi-paperclip me-1"></i>Luaran</a>' : '-',
                'uploaded_at'  => $l->uploaded_at?->format('d M Y H:i'),
                'status'       => view('components.status-badge', ['status' => $l->status])->render(),
                'catatan'      => e($l->catatan_verifikasi ?: '-'),
            ];
        });

        return response()->json(['data' => $rows->values(), 'total' => $rows->count()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $mahasiswa = $this->mahasiswaMilikUser();

        abort_if($mahasiswa->kelompokKkn?->status !== 'aktif', 403, 'Kelompok KKN Anda belum berstatus Aktif.');

        $validated = $request->validate([
            'file_laporan' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'file_luaran'  => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,zip', 'max:10240'],
        ]);

        // H1: simpan ke disk private (local) — diakses via route file.download terproteksi.
        $laporanPath = $request->file('file_laporan')->store('laporan-akhir');
        $luaranPath  = $request->hasFile('file_luaran')
            ? $request->file('file_luaran')->store('laporan-akhir')
            : null;

        // Laporan revisi boleh di-upload ulang (update ke menunggu);
        // yang sudah menunggu/disetujui ditolak.
        $existing = LaporanAkhir::where('kelompok_kkn_id', $mahasiswa->kelompok_kkn_id)->latest()->first();

        if ($existing && $existing->status !== 'revisi') {
            return back()->with('error', 'Laporan akhir untuk kelompok ini sudah ada dan tidak dalam status revisi.');
        }

        if ($existing) {
            $existing->update([
                'file_laporan'       => $laporanPath,
                'file_luaran'        => $luaranPath,
                'uploaded_by'        => Auth::id(),
                'uploaded_at'        => now(),
                'status'             => 'menunggu',
                'catatan_verifikasi' => null,
                'verified_by'        => null,
                'verified_at'        => null,
            ]);
        } else {
            LaporanAkhir::create([
                'kelompok_kkn_id' => $mahasiswa->kelompok_kkn_id,
                'file_laporan'    => $laporanPath,
                'file_luaran'     => $luaranPath,
                'uploaded_by'     => Auth::id(),
                'uploaded_at'     => now(),
                'status'          => 'menunggu',
            ]);
        }

        return back()->with('success', 'Laporan akhir berhasil di-upload dan menunggu verifikasi.');
    }
}
