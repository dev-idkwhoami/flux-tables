<div
    x-data="exampleTable"
>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column
        * @var \Idkwhoami\FluxTables\Abstracts\Filter\Filter $filter
        * @var \Illuminate\Database\Eloquent\Model $model
        */
        //dump(session()->all());
    @endphp

    <div class="flex w-full flex-col space-y-4">

        @if($this->table->hasFilters())
            <div>
                <div class="flex gap-x-4">
                    @if($this->table->hasLabel())
                        <flux:heading class="content-center" level="1" size="xl">
                            {{ $this->table->getLabel() }}
                        </flux:heading>
                    @endif
                    <flux:spacer/>
                    @if($this->hasActiveFilters())
                        <flux:button wire:click.prevent="resetFilters" class="hover:text-red-400" size="sm" square
                                     icon="filter-x" variant="filled"/>
                    @endif
                    <flux:modal.trigger :name="$this->getFilterModalName()">
                        <flux:button size="sm" square icon="filter" variant="filled"/>
                    </flux:modal.trigger>
                </div>

                <flux:modal class="min-w-[15svw] space-y-6" variant="flyout" :name="$this->getFilterModalName()">
                    <flux:heading>
                        Filters
                    </flux:heading>
                    <div class="flex flex-col w-full space-y-4">
                        @foreach($this->table->getFilters() as $filter)
                            @livewire($filter->component(), ['filter' => $filter], key($filter->getName()))
                        @endforeach
                    </div>
                </flux:modal>
            </div>
        @endif

        <flux:table :paginate="$this->models">

            <flux:columns>
                @foreach($this->table->getColumns() as $column)
                    <flux:column>
                        {{ $column->getLabel() }}
                    </flux:column>
                @endforeach
            </flux:columns>

            <flux:rows>
                @foreach($this->models as $model)
                    <flux:row wire:loading.delay.class="animate-pulse" :key="$model->getKey()">
                        @foreach($this->table->getColumns() as $column)
                            <flux:cell>
                                {{ $column->render($model) }}
                            </flux:cell>
                        @endforeach
                    </flux:row>
                @endforeach
            </flux:rows>

        </flux:table>

    </div>

</div>

@script
<script>
    console.debug("Example table loading..");

    Alpine.data('exampleTable', () => ({

        hiddenColumns: $wire.entangle('hiddenColumns', true),

        init() {
            console.debug("Example table loaded.");
            console.debug($wire.table);
        },

    }));
</script>
@endscript
