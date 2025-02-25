@props([
    'action',
    'id' => null
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\DirectAction $action */
@endphp
@if($action->isLink())
    <flux:button
        variant="ghost"
        key="action-restore-{{ $id }}"
        icon="{{ $action->getIcon() }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ base64_encode($action->getAction()) }}')">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        variant="danger"
        icon="{{ $action->getIcon() }}"
        key="action-restore-{{ $id }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ base64_encode($action->getAction()) }}')">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
