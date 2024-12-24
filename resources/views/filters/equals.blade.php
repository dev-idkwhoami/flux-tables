@php
    /**
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     */
@endphp
<div x-data="{ value: $wire.$parent.$entangle('table.filters.{{ $this->name }}.value', true) }">
    <flux:input
        :label="$this->filter->getLabel()"
        clearable
        x-model="value"
        size="sm"
    />
</div>
