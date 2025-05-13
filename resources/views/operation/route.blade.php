@props([
    'action',
    'route',
    'model' => null,
    'id' => null
])
@php
    /**
     * @var Closure $route
     * @var \Idkwhoami\FluxTables\Abstracts\Action\DirectAction $action
     */

@endphp
@if($action->isLink())
    <flux:button
        as="a"
        {{ $attributes }}
        :href="$route->call($this, $model ?? $id)"
        operation="{{ $action->getOperationId() }}"
        :variant="$action->getVariant()"
        key="operation-route-{{ $id }}"
        icon="{{ $action->getIcon() }}">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        as="a"
        {{ $attributes }}
        :href="$route->call($this, $model ?? $id)"
        operation="{{ $action->getOperationId() }}"
        :variant="$action->getVariant()"
        icon="{{ $action->getIcon() }}"
        key="operation-route-{{ $id }}">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
