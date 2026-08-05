@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'bodyClass' => null,
])

<div {{ $attributes->merge(['class' => 'card app-card']) }}>
    @if ($title || $subtitle)
        <div class="card-header bg-white">
            @if ($title)
                <h5 class="card-title mb-0">{{ $title }}</h5>
            @endif
            @if ($subtitle)
                <span class="card-subtitle text-muted small">{{ $subtitle }}</span>
            @endif
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    @if ($footer)
        <div class="card-footer bg-white">{{ $footer }}</div>
    @endif
</div>
