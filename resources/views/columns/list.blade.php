@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\Column
     */
    $items = $column->resolveValue($row);
@endphp
<flux:cell :align="$column->getAlignment()->asCellAlignment()">
    @if(count($items) > 1)
        <details>
            <summary>{{ $items->pop() }}</summary>
            <ul class="list-none">
                @foreach($items as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </details>
    @else
        {{ $items->pop() }}
    @endif
</flux:cell>
