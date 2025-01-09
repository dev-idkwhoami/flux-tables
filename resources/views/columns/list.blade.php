@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\Column
     */
    $items = $column->resolveValue($row) ?? [];
    $placeholder = $column->getPlaceholder();
    $count = count($items);
@endphp
<flux:cell :align="$column->getAlignment()->asCellAlignment()">
    @if($count > 1)
        <details>
            <summary>{{ $items->pop() }}</summary>
            <ul class="list-none">
                @foreach($items as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </details>
    @else
        {{ $items->pop() ?? $placeholder }}
    @endif
</flux:cell>
