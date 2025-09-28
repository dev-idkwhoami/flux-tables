<div class="space-y-2">
    {{-- Filter Label with Reset Button --}}
    <div class="flex items-center justify-between">
        <flux:label>{{ $label }}</flux:label>

        @if($this->hasValue())
            <flux:button
                size="xs"
                variant="ghost"
                icon="x-mark"
                wire:click="resetFilter"
                class="text-gray-400 hover:text-gray-600"
            />
        @endif
    </div>

    {{-- Filter Content (from the actual component) --}}
    <div>
        {{ $slot }}
    </div>

    {{-- Filter Description --}}
    @if($description)
        <div class="text-sm text-gray-500">
            {{ $description }}
        </div>
    @endif
</div>