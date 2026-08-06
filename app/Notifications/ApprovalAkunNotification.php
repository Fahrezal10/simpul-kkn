<?php

namespace App\Notifications;

use App\Models\PerguruanTinggi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApprovalAkunNotification extends Notification
{
    use Queueable;

    /**
     * UC-01 — Notifikasi ke operator PT saat status approval akun berubah
     * (disetujui/ditolak) oleh Bapperida.
     *
     * Sinkron (tanpa ShouldQueue): XAMPP dev tidak menjalankan queue worker,
     * dan jumlah notifikasi kecil, sehingga dikirim langsung saat notify().
     */
    public function __construct(public PerguruanTinggi $perguruanTinggi)
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
        $status = $this->perguruanTinggi->status_approval;

        $message = $status === 'disetujui'
            ? "Akun institusi \"{$this->perguruanTinggi->nama_pt}\" telah disetujui. Anda kini dapat mengajukan permohonan KKN."
            : "Akun institusi \"{$this->perguruanTinggi->nama_pt}\" ditolak. Catatan: {$this->perguruanTinggi->catatan_penolakan}";

        // Arah tujuan berbeda sesuai penerima (role Bapperida vs PT).
        $role = $notifiable->role?->nama_role;
        $url = $role === 'bapperida'
            ? route('bapperida.pt.show', $this->perguruanTinggi)
            : route('dashboard');

        return [
            'message'     => $message,
            'status'      => $status,
            'perguruan_tinggi_id' => $this->perguruanTinggi->id,
            'type'        => 'approval_akun',
            'url'         => $url,
        ];
    }
}
