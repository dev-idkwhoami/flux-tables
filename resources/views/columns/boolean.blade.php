@props([
    'column',
    'row'
])
@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\BooleanColumn
     */
    $value = $column->resolveValue($row);
    $useIcons = $column->useIcons();
    $color = $value
        ? $column->getColorTrue()
        : $column->getColorFalse();
    $icon = $value
        ? $column->getIconTrue()
        : $column->getIconFalse();
@endphp
<flux:cell :align="$column->getAlignment()->asCellAlignment()">
    <div @class(['flex justify-center', $color])>
        @if($useIcons)
            <flux:icon class="size-6" :$icon/>
        @else
            <span>{{ $value ? __('Yes') : __('No') }}</span>
        @endif
    </div>
</flux:cell>
