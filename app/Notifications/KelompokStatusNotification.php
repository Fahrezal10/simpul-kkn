<?php

namespace App\Notifications;

use App\Models\KelompokKkn;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * UC-07 — Notifikasi status pelaksanaan kelompok KKN.
 *
 * Dikirim ke operator PT & DPL saat Bapperida menetapkan status akhir:
 *  - 'aktif'   → KKN disetujui berjalan, mahasiswa bisa mulai logbook.
 *  - 'menunggu_matching' → lokasi ditolak, kembali ke matching.
 *  - 'selesai' → periode KKN ditutup, pelaksanaan kelompok selesai.
 *
 * Sinkron (tanpa ShouldQueue) — konsisten dengan PermohonanStatusNotification.
 */
class KelompokStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public KelompokKkn $kelompok,
        public string $statusLabel,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $desa = $this->kelompok->desa?->nama_desa;

        $message = match ($this->statusLabel) {
            'aktif'   => "Pelaksanaan KKN kelompok {$this->kelompok->kode_kelompok} DISETUJUI".($desa ? " di desa {$desa}" : '').'.',
            'selesai' => "Periode KKN ditutup: kelompok {$this->kelompok->kode_kelompok} telah selesai melaksanakan KKN.",
            default   => "Lokasi kelompok {$this->kelompok->kode_kelompok} ditolak; kembali ke tahap matching.",
        };

        return [
            'message' => $message,
            'status'  => $this->statusLabel,
            'type'    => 'kelompok_status',
            'url'     => route('dashboard'),
        ];
    }
}
