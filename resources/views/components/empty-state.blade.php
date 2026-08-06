@props(['icon' => 'bi-inbox', 'title' => 'Belum ada data', 'description' => null, 'action' => null])

<div class="empty-state">
    <i class="bi {{ $icon }}"></i>
    <h6 class="mt-3">{{ $title }}</h6>
    @if ($description)
        <p class="mb-3">{{ $description }}</p>
    @else
        <p class="mb-3">Tidak ada data untuk ditampilkan pada halaman ini.</p>
    @endif
    @if ($action)
        <div>{{ $action }}</div>
    @endif
</div>
