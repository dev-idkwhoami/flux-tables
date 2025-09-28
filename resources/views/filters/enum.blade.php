{{-- EnumFilter Template --}}
@if($displayMode === 'select')
    {{-- Select Mode --}}
    <flux:select
        wire:model.live="value"
        :multiple="$multiple"
    >
            @if($multiple)
                @foreach($options as $value => $label)
                    <flux:select.option
                        :value="$value"
                        :selected="in_array($value, $selectedValues)"
                    >
                        {{ $label }}
                    </flux:select.option>
                @endforeach
            @else
                <flux:select.option value="">Select an option</flux:select.option>
                @foreach($options as $value => $label)
                    <flux:select.option
                        :value="$value"
                        :selected="in_array($value, $selectedValues)"
                    >
                        {{ $label }}
                    </flux:select.option>
                @endforeach
            @endif
        </flux:select>

    @elseif($displayMode === 'radio')
        {{-- Radio Mode --}}
        <div class="space-y-2">
            @foreach($options as $value => $label)
                <flux:radio
                    wire:model.live="value"
                    :value="$value"
                    :checked="in_array($value, $selectedValues)"
                >
                    {{ $label }}
                </flux:radio>
            @endforeach
        </div>

    @else
        {{-- Checkbox Mode (default for multiple) --}}
        <div class="space-y-2">
            @foreach($options as $value => $label)
                <flux:checkbox
                    wire:model.live="value"
                    :value="$value"
                    :checked="in_array($value, $selectedValues)"
                >
                    {{ $label }}
                </flux:checkbox>
            @endforeach
        </div>
@endif