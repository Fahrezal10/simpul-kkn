<?php

namespace App\Http\Controllers\Bapperida;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Bapperida\MonitoringController;
use App\Models\ActivityLog;
use App\Models\KelompokKkn;
use App\Notifications\KelompokStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * UC-08/penutupan periode — Finalisasi pelaksanaan KKN oleh Bapperida.
 *
 * Menutup periode KKN: seluruh kelompok berstatus "aktif" diubah menjadi
 * "selesai", KKN resmi berakhir. Efek samping otomatis:
 *  - mahasiswa berhenti dapat mengisi logbook (LogbookController mengizinkan
 *    hanya kelompok berstatus "aktif");
 *  - status kelompok tampil sebagai "selesai" di dashboard/monitoring;
 *  - evaluasi desa & DPL tetap dapat diisi (Evaluasi*Controller menerima
 *    status "aktif" atau "selesai").
 *
 * Data desa lokasi kelompok dipertahankan (tidak direset) agar riwayat
 * penempatan tetap tersimpan.
 */
class PenutupanPeriodeController extends Controller
{
    public function index(): View
    {
        $kelompokAktif = KelompokKkn::where('status', 'aktif')
            ->with(['permohonanKkn.perguruanTinggi', 'desa'])
            ->orderBy('kode_kelompok')
            ->get();

        return view('bapperida.penutupan-periode.index', compact('kelompokAktif'));
    }

    public function store(): RedirectResponse
    {
        $jumlah = KelompokKkn::where('status', 'aktif')->count();

        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada kelompok berstatus Aktif yang perlu ditutup.');
        }

        // Transaksi: tutup semua kelompok aktif sekaligus.
        $kodeKelompok = KelompokKkn::where('status', 'aktif')
            ->pluck('kode_kelompok');

        KelompokKkn::where('status', 'aktif')
            ->update(['status' => 'selesai']);

        // Agregasi monitoring berubah drastis (aktif → selesai) → buang cache.
        MonitoringController::flushCache();

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'aksi'       => 'tutup_periode',
            'deskripsi'  => "Menutup periode KKN: {$jumlah} kelompok aktif diselesaikan (".$kodeKelompok->implode(', ').').',
            'ip_address' => request()->ip(),
        ]);

        // SYS-01: beri tahu DPL bahwa kelompok bimbingannya selesai.
        KelompokKkn::whereIn('kode_kelompok', $kodeKelompok)
            ->with('dosen')
            ->get()
            ->each(function (KelompokKkn $kelompok) {
                if ($dpl = $kelompok->dosen) {
                    \App\Models\User::where('email', $dpl->email)->get()
                        ->each(fn ($u) => $u->notify(new KelompokStatusNotification($kelompok, 'selesai')));
                }
            });

        return back()->with('success', "Periode KKN ditutup: {$jumlah} kelompok aktif kini berstatus Selesai.");
    }
}