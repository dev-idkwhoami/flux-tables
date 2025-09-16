@if(empty($formatted))
    <flux:text>—</flux:text>
@else
    <flux:text>{{ $formatted }}</flux:text>
@endif