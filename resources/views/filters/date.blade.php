{{-- DateFilter Template --}}
<div class="space-y-3">
    <flux:label>{{ $filter->getLabel() }}</flux:label>

    <div class="flex space-x-2">
        {{-- Operator Select --}}
        <div class="w-32">
            <flux:select wire:model.live="filterValues.{{ $filter->getName() }}.operator">
                @foreach($availableOperators as $operator)
                    <flux:select.option :value="$operator" :selected="$operatorValue === $operator">
                        {{ $operatorLabels[$operator] ?? $operator }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Date Input --}}
        <div class="flex-1">
            <flux:input
                type="{{ $inputType }}"
                wire:model.live="filterValues.{{ $filter->getName() }}.date"
                :value="$dateValue"
                placeholder="Select date"
            />
        </div>
    </div>

    @if($filter->getDescription())
        <div class="text-sm text-gray-500">
            {{ $filter->getDescription() }}
        </div>
    @endif
</div>