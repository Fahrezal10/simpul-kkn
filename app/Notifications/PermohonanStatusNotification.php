<?php

namespace App\Notifications;

use App\Models\PermohonanKkn;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PermohonanStatusNotification extends Notification
{
    use Queueable;

    /**
     * UC-05 — Notifikasi ke PT saat status permohonan berubah
     * (diajukan → terverifikasi / ditolak) oleh Bapperida.
     *
     * Sinkron (tanpa ShouldQueue): XAMPP dev tidak menjalankan queue worker,
     * dan jumlah notifikasi kecil, sehingga dikirim langsung saat notify().
     */
    public function __construct(public PermohonanKkn $permohonan)
    {
        //
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
        $status = $this->permohonan->status;
        $periode = $this->permohonan->periode;

        $message = match ($status) {
            'terverifikasi' => "Permohonan KKN periode {$periode} telah diverifikasi Bapperida.",
            'ditolak'       => "Permohonan KKN periode {$periode} ditolak. Catatan: {$this->permohonan->catatan_verifikasi}",
            default         => "Status permohonan KKN periode {$periode} berubah menjadi \"{$status}\".",
        };

        // Arah tujuan berbeda sesuai penerima (role Bapperida vs PT).
        $role = $notifiable->role?->nama_role;
        $url = match ($role) {
            'bapperida' => route('bapperida.permohonan.show', $this->permohonan),
            default     => route('perguruan-tinggi.permohonan.show', $this->permohonan),
        };

        return [
            'message'       => $message,
            'status'        => $status,
            'permohonan_id' => $this->permohonan->id,
            'type'          => 'permohonan_status',
            'url'           => $url,
        ];
    }
}