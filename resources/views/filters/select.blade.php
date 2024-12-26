@php
    /**
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     */
@endphp
<div x-data="{ value: $wire.$parent.$entangle('table.filters.{{ $this->index }}.value', true) }">
    <flux:select
        variant="listbox"
        :multiple="$this->filter->isMultiple()"
        :label="$this->filter->getLabel()"
        clearable
        x-model="value"
        size="sm"
    >
        @foreach($this->filter->getOptions()->pluck($this->filter->getValueColumn(), $this->filter->getKeyColumn()) as $value => $label)
            <flux:option :$value>
                {{ $label }}
            </flux:option>
        @endforeach
    </flux:select>
</div>
