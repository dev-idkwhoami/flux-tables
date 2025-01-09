@php
    /**
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     */
@endphp
<div x-data="{ value: $wire.$parent.$entangle('table.filters.{{ $this->index }}.value', true) }">
    <flux:select
        variant="listbox"
        :label="$this->filter->getLabel()"
        clearable
        x-model="value"
        size="sm"
    >
        @foreach($this->filter->getOptions() as /** @var $option \Idkwhoami\FluxTables\Enums\DeletedFilterOption */ $option)
            <flux:option :value="$option->value">
                {{ $option->getLabel() }}
            </flux:option>
        @endforeach
    </flux:select>
</div>
