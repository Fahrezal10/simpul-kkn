@props(['status', 'labels' => []])

@php
    // §10.6 Badge Status — solid, konsisten di seluruh sistem.
    // Amber=menunggu, Teal=terverifikasi/disetujui/aktif, Blue=selesai,
    // Red=ditolak/revisi, Neutral=draft/nonaktif.
    $statuses = [
        // Menunggu/Pending (Amber)
        'menunggu'                      => ['Menunggu', 'badge-amber'],
        'diajukan'                      => ['Diajukan', 'badge-amber'],
        'menunggu_matching'             => ['Menunggu Matching', 'badge-amber'],
        'menunggu_verifikasi_kecamatan' => ['Verifikasi Kecamatan', 'badge-amber'],
        'menunggu_persetujuan'          => ['Menunggu Persetujuan', 'badge-amber'],
        'menunggu'                      => ['Menunggu', 'badge-amber'],
        // Terverifikasi/Disetujui/Aktif (Teal)
        'terverifikasi'                 => ['Terverifikasi', 'badge-teal'],
        'disetujui'                     => ['Disetujui', 'badge-teal'],
        'aktif'                         => ['Aktif', 'badge-teal'],
        'siap'                          => ['Siap', 'badge-teal'],
        'kandidat'                      => ['Kandidat', 'badge-teal'],
        'dipilih'                       => ['Dipilih', 'badge-teal'],
        // Selesai (Deep Blue)
        'selesai'                       => ['Selesai', 'badge-blue'],
        // Ditolak/Revisi (Red)
        'ditolak'                       => ['Ditolak', 'badge-danger'],
        'revisi'                        => ['Revisi', 'badge-danger'],
        'tidak_siap'                    => ['Tidak Siap', 'badge-danger'],
        // Draft/Nonaktif (Neutral)
        'draft'                         => ['Draft', 'badge-secondary'],
        'nonaktif'                      => ['Nonaktif', 'badge-secondary'],
    ];
    $label = $labels[$status] ?? ($statuses[$status][0] ?? \Illuminate\Support\Str::headline($status));
    $class = $statuses[$status][1] ?? 'badge-secondary';
@endphp

<span class="status-badge {{ $class }}">{{ $label }}</span>
