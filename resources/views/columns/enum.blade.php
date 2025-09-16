@if(is_null($value))
    <flux:text>—</flux:text>
@else
    <flux:badge
        color="{{ $config['color'] }}"
        variant="{{ $config['variant'] }}"
        size="{{ $config['size'] }}"
    >
        @if($config['icon'])
            <flux:icon :name="$config['icon']" />
        @endif
        {{ $config['label'] }}
    </flux:badge>
@endif