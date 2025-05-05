@props([
    'action',
    'route',
    'id' => null
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\DirectAction $action */
@endphp
@if($action->isLink())
    <flux:button
        as="a"
        {{ $attributes }}
        :href="route($route)"
        :variant="$action->getVariant()"
        key="action-route-{{ $id }}"
        icon="{{ $action->getIcon() }}">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        as="a"
        {{ $attributes }}
        :href="route($route)"
        :variant="$action->getVariant()"
        icon="{{ $action->getIcon() }}"
        key="action-route-{{ $id }}">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
