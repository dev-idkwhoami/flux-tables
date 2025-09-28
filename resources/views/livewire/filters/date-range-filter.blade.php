<x-flux-tables::filter-wrapper :label="$label" :description="$description">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        {{-- From Date --}}
        <div>
            <flux:date-picker
                wire:model.live="value.start"
                placeholder="Select start date"
                clearable
            >
                <flux:date-picker.input label="From" />
            </flux:date-picker>
        </div>

        {{-- To Date --}}
        <div>
            <flux:date-picker
                wire:model.live="value.end"
                placeholder="Select end date"
                clearable
            >
                <flux:date-picker.input label="To" />
            </flux:date-picker>
        </div>
    </div>
</x-flux-tables::filter-wrapper>