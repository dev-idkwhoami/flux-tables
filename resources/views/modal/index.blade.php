@props([
    'action',
    'id' => null,
    'modalClasses' => 'md:w-96',
    'modalVariant' => null,
    'modalPosition' => null,
    'dismissible' => false,
])
@php
    /** @var \Idkwhoami\FluxTables\Abstracts\Action\ModalAction $action */
@endphp
<flux:modal.trigger :name="$action->modalUniqueName($id)">
    @if($action->isLink())
        <flux:button
            :variant="$action->getVariant()"
            key="modal-action-{{ $id }}"
            icon="{{ $action->getIcon() }}">
            {{ $action->getLabel() }}
        </flux:button>
    @else
        <flux:menu.item
            :variant="$action->getVariant()"
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
    :dismissible="$dismissible"
    {{ $attributes->class([$modalClasses]) }}
    {{ $attributes->whereStartsWith('wire:model') }}
    :name="$action->modalUniqueName($id)">
    @livewire($action->getComponent(), array_merge(['action' => $action, 'id' => $id, 'model' => $action->getModel($id)], $action->getComponentData($id)), key($action->modalUniqueName($id)))
</flux:modal>
@endteleport
