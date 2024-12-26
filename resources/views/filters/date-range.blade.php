@php
    /**
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     */
@endphp
<div x-data="{
        value: $wire.$parent.$entangle('table.filters.{{ $this->index }}.value', true),
        init() {
                $watch('start', (start) => {
                    this.value[0] = start;
                    this.updateValue();
                });
                $watch('end', (end) => {
                    this.value[1] = end;
                    this.updateValue();
                });
        },
        updateValue() {
            if(this.start && this.end) {
                this.value = [this.start, this.end];
            } else {
                this.value = [];
            }
            $wire.$commit();
        },
        start: null,
        end: null,
        }">
    <flux:field>
        <flux:label>{{ $this->filter->getLabel() }}</flux:label>
        <div class="grid grid-cols-2 gap-4">
            <flux:input
                clearable
                type="date"
                x-model="start"
                size="sm"
            />
            <flux:input
                clearable
                type="date"
                x-model="end"
                size="sm"
            />
        </div>
    </flux:field>
</div>
