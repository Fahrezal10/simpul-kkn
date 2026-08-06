@props([
    'title' => null,
    'columns' => [],
    'rows' => [],
    'cell' => null, // closure (string $key, array $row) => mixed; fallback ke teks bila null
    'emptyMessage' => 'Belum ada data.',
    'emptyIcon' => 'bi-inbox',
])

@php
    // Kolom: string "nama_kolom" (label otomatis) atau ['key'=>..., 'label'=>..., 'class'=>...].
    $normalized = array_map(function ($col) {
        if (is_string($col)) {
            return ['key' => $col, 'label' => ucwords(str_replace('_', ' ', $col)), 'class' => null];
        }
        return [
            'key' => $col['key'],
            'label' => $col['label'] ?? ucwords(str_replace('_', ' ', $col['key'])),
            'class' => $col['class'] ?? null,
        ];
    }, $columns);
@endphp

<x-card :title="$title" {{ $attributes }}>
    @if (count($normalized) > 0 && count($rows) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        @foreach ($normalized as $col)
                            <th @if ($col['class']) class="{{ $col['class'] }}" @endif>{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($normalized as $col)
                                <td @if ($col['class']) class="{{ $col['class'] }}" @endif>
                                    {{ $cell ? $cell($col['key'], $row) : ($row[$col['key']] ?? '') }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-empty-state :icon="$emptyIcon" :title="$emptyMessage" />
    @endif
</x-card>
