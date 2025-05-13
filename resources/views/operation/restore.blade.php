@props([
    'action',
    'id' => null
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\DirectAction $action */
@endphp
@if($action->isLink())
    <flux:button
        :variant="$action->getVariant()"
        key="action-restore-{{ $id }}"
        icon="{{ $action->getIcon() }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ $action->getOperationId() }}')">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        :variant="$action->getVariant()"
        icon="{{ $action->getIcon() }}"
        key="action-restore-{{ $id }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ $action->getOperationId() }}')">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
