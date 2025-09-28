<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('flux-tables::filters.title') ?? 'Filters' }}</flux:heading>

        @if($activeCount > 0)
            <flux:badge variant="outline" size="sm">
                {{ $activeCount }} {{ $activeCount === 1 ? 'filter' : 'filters' }} active
            </flux:badge>
        @endif
    </div>

    {{-- Filter Controls --}}
    <div class="space-y-4">
        @foreach($filters as $filter)
            @if($filter->isVisible())
                <div wire:key="filter-{{ $filter->getName() }}">
                    @livewire(
                        $this->getFilterComponentName($filter),
                        $this->getFilterComponentParams($filter),
                        key('filter-component-' . $filter->getName())
                    )
                </div>
            @endif
        @endforeach

        @if(empty($filters))
            <div class="text-center text-gray-500 py-8">
                <flux:icon name="funnel" class="size-12 mx-auto mb-2 text-gray-300"/>
                <p>No filters available for the current view</p>
            </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between pt-4 border-t">
        <flux:button
            variant="ghost"
            wire:click="resetAllFilters"
            :disabled="!$activeCount && !$pendingCount"
        >
            {{ __('flux-tables::filters.clear_all') ?? 'Clear All' }}
        </flux:button>

        <div class="flex space-x-2">
            @if($hasChanges)
                <flux:button variant="outline" wire:click="cancelChanges">
                    {{ __('flux-tables::filters.cancel') ?? 'Cancel' }}
                </flux:button>
            @endif

            <flux:button
                variant="primary"
                wire:click="applyFilters"
                :disabled="!$hasChanges && !$pendingCount"
            >
                {{ __('flux-tables::filters.apply') ?? 'Apply Filters' }}
                @if($pendingCount > 0 && $hasChanges)
                    ({{ $pendingCount }})
                @endif
            </flux:button>
        </div>
    </div>

    {{-- Pending Changes Indicator --}}
    @if($hasChanges)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center">
                <flux:icon name="information-circle" class="size-5 text-blue-600 mr-2"/>
                <span class="text-sm text-blue-800">
                    You have unsaved filter changes. Click "Apply Filters" to update the table.
                </span>
            </div>
        </div>
    @endif
</div>