@props([
    'column',
    'value',
    'component'
])
<div class="">
    @php
        if (!($value instanceof \Illuminate\Database\Eloquent\Model)) {
            $type = get_class($value);
            throw new InvalidArgumentException("The supplied value needs to be of type '\Illuminate\Database\Eloquent\Model' but '$type' was given.");
        }

        $key = 'column-' . $column->getName() . '-id-' . $value->{$value->getKeyName()};
    @endphp
    @livewire($component, compact(['value', 'column']), key($key))
</div>
