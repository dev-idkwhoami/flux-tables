@props([
    'value',
    'column'
])
<div @class([$column->getTrueColorClass() => $value, $column->getFalseColorClass() => !$value])>
    @if($value)
        <flux:icon :name="$column->getTrueIcon()"/>
    @else
        <flux:icon :name="$column->getFalseIcon()"/>
    @endif
</div>
