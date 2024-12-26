@php
    /**
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     */
@endphp
<div x-data="{ value: $wire.$parent.$entangle('table.filters.{{ $this->index }}.value', true) }">
    <flux:input
        :label="$this->filter->getLabel()"
        clearable
        x-model="value"
        size="sm"
    />
</div>
