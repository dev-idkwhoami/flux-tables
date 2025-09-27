{{-- DateRangeFilter Template --}}
<div class="space-y-3">
    <flux:label>{{ $filter->getLabel() }}</flux:label>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        {{-- From Date --}}
        <div>
            <flux:date-picker
                wire:model.live="filterValues.{{ $filter->getName() }}.start"
                placeholder="Select start date"
                clearable
            >
                <flux:date-picker.input label="From" />
            </flux:date-picker>
        </div>

        {{-- To Date --}}
        <div>
            <flux:date-picker
                wire:model.live="filterValues.{{ $filter->getName() }}.end"
                placeholder="Select end date"
                clearable
            >
                <flux:date-picker.input label="To" />
            </flux:date-picker>
        </div>
    </div>

    @if($filter->getDescription())
        <div class="text-sm text-gray-500">
            {{ $filter->getDescription() }}
        </div>
    @endif
</div>