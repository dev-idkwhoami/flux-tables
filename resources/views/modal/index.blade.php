@props([
    'action',
    'id' => null,
    'modalClasses' => 'max-w-3/5',
    'modalVariant' => 'bare',
    'modalPosition' => 'right'
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\ModalAction $action */
@endphp
<flux:modal.trigger :name="$action->modalUniqueName($id)">
    @if($action->isLink())
        <flux:button
            variant="ghost"
            key="modal-action-{{ $id }}"
            icon="{{ $action->getIcon() }}">
            {{ $action->getLabel() }}
        </flux:button>
    @else
        <flux:menu.item
            variant="danger"
            icon="{{ $action->getIcon() }}"
            key="modal-action-{{ $id }}">
            {{ $action->getLabel() }}
        </flux:menu.item>
    @endif
</flux:modal.trigger>

@teleport('body')
<flux:modal
    :position="$modalPosition"
    :variant="$modalVariant"
    @class([$modalClasses])
    :name="$action->modalUniqueName($id)">
    @livewire($action->getComponent(), ['action' => $action, 'id' => $id], key($action->modalUniqueName($id)))
</flux:modal>
@endteleport
