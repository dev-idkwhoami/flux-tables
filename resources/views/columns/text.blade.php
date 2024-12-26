@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\Column
     */
@endphp
<flux:cell :align="$column->getAlignment()->asCellAlignment()">
    {{ $column->resolveValue($row) }}
</flux:cell>
