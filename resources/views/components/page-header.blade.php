@props(['title', 'subtitle' => null, 'icon' => null])

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        @if ($icon)
            <div class="page-header-icon"><i class="bi {{ $icon }}"></i></div>
        @endif
        <div>
            <h2 class="page-title mb-0">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-muted small mb-0">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <hr class="page-header-divider mt-3 mb-0">
</div>
