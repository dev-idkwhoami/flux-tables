@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\Column
     */
@endphp
<flux:cell :align="$column->getAlignment()">
    {{ $column->hasTransform()
        ? $column->getTransform()($row->{$column->getName()}, $row)
        : $row->{$column->getName()} }}
</flux:cell>
