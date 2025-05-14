@props([
    /** @var \Idkwhoami\FluxTables\Concretes\Table\EloquentTable $table */
    'table'
])

<div {{ $attributes->class(['flex items-center']) }}>
    <flux:modal.trigger :name="$table->getCreateModalName()">
        <flux:button size="sm" icon="plus" variant="primary">
            {{ $table->getCreateText() }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal class="p-2" :name="$table->getCreateModalName()">
        @livewire($table->getCreateComponent(), ['table' => $table, 'modal' => $table->getCreateModalName()], key($table->getCreateComponent() . '-key'))
    </flux:modal>
</div>
