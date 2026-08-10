<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\LaporanAkhir;
use App\Models\Logbook;
use App\Models\PerguruanTinggi;
use App\Models\PermohonanKkn;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * H1 — Pengunduhan file yang diproteksi.
 *
 * Dokumen (surat/proposal permohonan, legalitas PT, laporan akhir, foto logbook)
 * disimpan di disk PRIVATE dan hanya bisa diunduh lewat route ini — yang
 * menerapkan autentikasi + otorisasi sesuai jenis file. Tidak lagi diekspos
 * mentah lewat symlink storage publik.
 */
class FileController extends Controller
{
    /**
     * Download satu file terproteksi.
     *
     * @param string $jenis  salah satu: permohonan|legalitas|laporan-akhir|logbook
     * @param string $path   path relatif dalam disk private
     */
    public function download(Request $request, string $jenis, string $path): StreamedResponse
    {
        // Normalisasi & pastikan path tidak keluar dari direktori (traversal).
        $path = ltrim(urldecode($path), '/');

        abort_unless($this->otorisasi($jenis, $path), 403, 'Anda tidak memiliki akses ke dokumen ini.');

        if (! Storage::disk('private')->exists($path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $nama = basename($path);

        return Storage::disk('private')->download($path, $nama);
    }

    /**
     * Otorisasi per jenis file:
     *  - permohonan   : operator PT pemilik permohonan, atau Bapperida
     *  - legalitas    : Bapperida/superadmin, atau operator PT pemilik dokumen
     *  - laporan-akhir: mahasiswa/DPL kelompok terkait, atau Bapperida
     *  - logbook      : mahasiswa pemilik, DPL bimbingan, atau Bapperida
     *
     * Jenis yang tak dikenal selalu ditolak (403), bahkan untuk Bapperida/superadmin —
     * supaya admin tidak bisa menyajikan file arbitrer dari dalam disk private.
     */
    private function otorisasi(string $jenis, string $path): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Jenis file yang dikenal. Segala sesuatu di luar ini tidak pernah boleh
        // diakses, apa pun perannya (termasuk Bapperida/superadmin).
        $jenisDikenal = ['permohonan', 'legalitas', 'laporan-akhir', 'logbook'];
        if (! in_array($jenis, $jenisDikenal, true)) {
            return false;
        }

        $role = $user->role?->nama_role;
        if (in_array($role, ['bapperida', 'superadmin'], true)) {
            return true;
        }

        return match ($jenis) {
            'permohonan'  => $this->aksesPermohonan($user, $role, $path),
            'legalitas'   => $this->aksesLegalitas($user, $role, $path),
            'laporan-akhir' => $this->aksesLaporanAkhir($user, $role, $path),
            'logbook'     => $this->aksesLogbook($user, $role, $path),
            default       => false,
        };
    }

    private function aksesPermohonan($user, ?string $role, string $path): bool
    {
        // Path format: permohonan/<hash>.pdf
        $permohonan = PermohonanKkn::where('file_surat_permohonan', $path)
            ->orWhere('file_proposal', $path)
            ->first();

        if (! $permohonan) {
            return false;
        }

        if ($role === 'perguruan_tinggi') {
            return $permohonan->perguruan_tinggi_id === $user->perguruanTinggi?->id;
        }

        return false;
    }

    private function aksesLegalitas($user, ?string $role, string $path): bool
    {
        if ($role !== 'perguruan_tinggi') {
            return false;
        }

        $pt = PerguruanTinggi::where('dokumen_legalitas', $path)->first();

        return $pt && $pt->user_id === $user->id;
    }

    private function aksesLaporanAkhir($user, ?string $role, string $path): bool
    {
        $laporan = LaporanAkhir::where('file_laporan', $path)
            ->orWhere('file_luaran', $path)
            ->first();

        if (! $laporan) {
            return false;
        }

        return match ($role) {
            'mahasiswa' => Mahasiswa::where('user_id', $user->id)
                    ->where('kelompok_kkn_id', $laporan->kelompok_kkn_id)->exists(),
            'dosen'     => Dosen::where('user_id', $user->id)
                    ->whereHas('kelompokKkn', fn ($q) => $q->where('id', $laporan->kelompok_kkn_id))->exists(),
            default     => false,
        };
    }

    private function aksesLogbook($user, ?string $role, string $path): bool
    {
        $logbook = Logbook::where('foto', $path)->first();

        if (! $logbook) {
            return false;
        }

        return match ($role) {
            'mahasiswa' => Mahasiswa::where('user_id', $user->id)
                    ->where('id', $logbook->mahasiswa_id)->exists(),
            'dosen'     => Dosen::where('user_id', $user->id)
                    ->whereHas('kelompokKkn', fn ($q) => $q->where('id', $logbook->kelompok_kkn_id))->exists(),
            default     => false,
        };
    }
}