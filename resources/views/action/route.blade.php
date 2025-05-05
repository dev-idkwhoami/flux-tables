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
        :href="route($route)"
        :variant="$action->getVariant()"
        key="action-delete-{{ $id }}"
        icon="{{ $action->getIcon() }}">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        as="a"
        :href="route($route)"
        :variant="$action->getVariant()"
        icon="{{ $action->getIcon() }}"
        key="action-delete-{{ $id }}">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
