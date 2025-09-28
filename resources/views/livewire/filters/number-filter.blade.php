<x-flux-tables::filter-wrapper :label="$label" :description="$description">
    <div class="flex space-x-2">
        {{-- Operator Select --}}
        <div class="w-32">
            <flux:select wire:model.live="value.operator">
                @foreach($availableOperators as $operator)
                    <flux:select.option :value="$operator" :selected="$operatorValue === $operator">
                        {{ $operatorLabels[$operator] ?? $operator }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Number Input --}}
        <div class="flex-1">
            <flux:input
                type="number"
                wire:model.live="value.number"
                :value="$numberValue"
                placeholder="Enter number"
                @if($min !== null) min="{{ $min }}" @endif
                @if($max !== null) max="{{ $max }}" @endif
                @if($step !== null) step="{{ $step }}" @endif
            />
        </div>
    </div>
</x-flux-tables::filter-wrapper>