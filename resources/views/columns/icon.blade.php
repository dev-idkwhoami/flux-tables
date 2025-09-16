@if($config['tooltip'])
    <flux:icon :name="$config['icon']"
        color="{{ $config['color'] }}"
        size="{{ $config['size'] }}"
        title="{{ $config['tooltip'] }}"
    />
@else
    <flux:icon :name="$config['icon']"
        color="{{ $config['color'] }}"
        size="{{ $config['size'] }}"
    />
@endif