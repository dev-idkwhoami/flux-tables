@props([
    /** @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column */
    'column',
    'sortingColumn',
    'sortingDirection',
    'toggledColumns'
])
@php
    $toggled = isset($toggledColumns) ? in_array($column->getName(), $toggledColumns) : false
@endphp
<div>
    @if($column->isSortable())
        <flux:table.column
            @class(['hidden' => $toggled])
            sortable
            :sorted="$sortingColumn === $column->getName()"
            :direction="$sortingDirection"
            :key="$column->getName()"
            wire:click.prevent="sort('{{ $column->getName() }}')">
            {{ $column->getLabel() }}
        </flux:table.column>
    @else
        <flux:table.column
            @class(['hidden' => $toggled])
            :key="$column->getName()">
            {{ $column->getLabel() }}
        </flux:table.column>
    @endif
</div>
