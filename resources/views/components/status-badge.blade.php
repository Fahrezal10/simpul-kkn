@props(['status', 'labels' => []])

@php
    // Map status -> (label, kelas warna) sesuai design system §10.2/§10.4.
    $statuses = [
        'menunggu'                      => ['Menunggu', 'badge-amber'],
        'diajukan'                      => ['Diajukan', 'badge-amber'],
        'menunggu_matching'             => ['Menunggu Matching', 'badge-amber'],
        'menunggu_verifikasi_kecamatan' => ['Verifikasi Kecamatan', 'badge-amber'],
        'menunggu_persetujuan'          => ['Menunggu Persetujuan', 'badge-amber'],
        'terverifikasi'                 => ['Terverifikasi', 'badge-teal'],
        'disetujui'                     => ['Disetujui', 'badge-teal'],
        'aktif'                         => ['Aktif', 'badge-teal'],
        'siap'                          => ['Siap', 'badge-teal'],
        'kandidat'                      => ['Kandidat', 'badge-teal'],
        'dipilih'                       => ['Dipilih', 'badge-teal'],
        'disetujui'                     => ['Disetujui', 'badge-teal'],
        'ditolak'                       => ['Ditolak', 'badge-danger'],
        'revisi'                        => ['Revisi', 'badge-danger'],
        'tidak_siap'                    => ['Tidak Siap', 'badge-danger'],
        'selesai'                       => ['Selesai', 'badge-blue'],
    ];
    [$label, $class] = $statuses[$status] ?? [Str::headline($status), 'badge-secondary'];
@endphp

<span class="status-badge {{ $class }}">{{ $label }}</span>
