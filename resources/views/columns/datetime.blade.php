@if(empty($formatted))
    <flux:text>—</flux:text>
@elseif($tooltip && !$human && $value)
    <flux:text title="{{ $humanTime }}">{{ $formatted }}</flux:text>
@else
    <flux:text>{{ $formatted }}</flux:text>
@endif