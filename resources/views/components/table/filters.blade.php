@props([
    'modal',
    /** @var \Idkwhoami\FluxTables\Concretes\Table\EloquentTable $table */
    'table'
])
<flux:modal class="min-w-[15svw] space-y-6" variant="flyout" :name="$modal">
    <flux:heading>
        {{ __('flux-tables::table/filter.heading') }}
    </flux:heading>
    <div class="flex flex-col w-full space-y-4">
        @foreach($table->getFilters() as $filter)
            @livewire($filter->component(), ['filter' => $filter, 'table' => $table], key($filter->getName()))
        @endforeach
    </div>
</flux:modal>
