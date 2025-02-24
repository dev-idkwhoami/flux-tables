@props([
    'action',
    'id' => null
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\Action $action */
@endphp
@if($action->isLink())
    <flux:button
        variant="ghost"
        class="hover:text-red-500 cursor-pointer"
        key="action-delete-{{ $id }}"
        icon="{{ $action->getIcon() }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ base64_encode($action->getAction()) }}')">
        {{ $action->getLabel() }}
    </flux:button>
@else
    <flux:menu.item
        variant="danger"
        icon="{{ $action->getIcon() }}"
        class="cursor-pointer"
        key="action-delete-{{ $id }}"
        wire:click.prevent="callAction('{{ $id }}', '{{ base64_encode($action->getAction()) }}')">
        {{ $action->getLabel() }}
    </flux:menu.item>
@endif
