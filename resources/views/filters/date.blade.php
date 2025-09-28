{{-- DateFilter Template --}}
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

    {{-- Date Input --}}
    <div class="flex-1">
        <flux:input
            type="{{ $inputType }}"
            wire:model.live="value.date"
            :value="$dateValue"
            placeholder="Select date"
        />
    </div>
</div>