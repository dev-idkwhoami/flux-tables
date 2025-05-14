@props([
    'filters'
])

<div {{ $attributes }}>
    @foreach($filters as $filter)
        <flux:badge size="sm" class="flex space-x-1" variant="pill">
            {!! $filter->renderPill() !!}
            <flux:badge.close wire:click.prevent="resetFilter('{{ $filter->getName() }}')"/>
        </flux:badge>
    @endforeach
</div>
