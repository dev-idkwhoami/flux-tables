@if($showLabels && $label)
    <div class="flex items-center gap-2">
        <flux:icon :name="$icon" />
        <flux:text>{{ $label }}</flux:text>
    </div>
@else
    <flux:icon :name="$icon" />
@endif