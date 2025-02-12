<div
    x-data="exampleTable"
>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column
        * @var \Idkwhoami\FluxTables\Abstracts\Filter\Filter $filter
        * @var \Illuminate\Database\Eloquent\Model $model
        */
        dump(session()->all());
    @endphp

    <div class="flex w-full flex-col space-y-4">


        <div>
            <div class="flex gap-x-3">
                @if($this->table->hasLabel())
                    <flux:heading class="content-center" level="1" size="xl">
                        {{ $this->table->getLabel() }}
                    </flux:heading>
                @endif
                <flux:dropdown class="flex items-center">
                    <flux:button size="xs" square variant="ghost" icon="chevron-down"/>

                    <flux:menu>

                        <flux:menu.submenu heading="Toggle Columns">
                            <flux:menu.checkbox.group>
                                @foreach($this->getToggleableColumns() as $column)
                                    <flux:menu.checkbox
                                        wire:click.prevent="toggle('{{ $column->getName() }}')"
                                        :value="$column->getName()"
                                        :checked="$this->isColumnToggled($column->getName())">
                                        {{ $column->getLabel() }}
                                    </flux:menu.checkbox>
                                @endforeach
                            </flux:menu.checkbox.group>
                        </flux:menu.submenu>

                        <flux:menu.item wire:click.prevent="resetSorting" class="hover:text-orange-400">Reset Sorting</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
                <flux:spacer/>

                <div class="flex flex-col gap-y-1">
                    <div class="flex w-2xl">
                        <flux:spacer/>
                        <flux:input clearable size="sm" type="text" icon="search" wire:model.live.debounce="search"/>
                    </div>
                    @if($this->table->hasFilters())
                        <div class="flex items-center gap-x-2">
                            <flux:spacer/>
                            @if($this->hasActiveFilters())
                                <div>
                                    @foreach($this->getActiveFilters() as $filter)
                                        <flux:badge size="sm" class="flex gap-x-1" variant="pill">
                                            {!! $filter->renderPill() !!}
                                            <flux:badge.close wire:click.prevent="resetFilter('{{ $filter->getName() }}')"/>
                                        </flux:badge>
                                    @endforeach
                                </div>

                                <flux:button wire:click.prevent="resetFilters"
                                             class="hover:text-red-400"
                                             size="sm"
                                             square
                                             icon="filter-x"
                                             variant="filled"/>
                            @endif

                            <flux:modal.trigger :name="$this->getFilterModalName()">
                                <flux:button size="sm" square icon="filter" variant="filled"/>
                            </flux:modal.trigger>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        @if($this->table->hasFilters())
            <flux:modal class="min-w-[15svw] space-y-6" variant="flyout" :name="$this->getFilterModalName()">
                <flux:heading>
                    Filters
                </flux:heading>
                <div class="flex flex-col w-full space-y-4">
                    @foreach($this->table->getFilters() as $filter)
                        @livewire($filter->component(), ['filter' => $filter, 'table' => $this->table], key($filter->getName()))
                    @endforeach
                </div>
            </flux:modal>
        @endif

        <flux:table :paginate="$this->models">

            <flux:columns>
                @foreach($this->table->getColumns() as $column)
                    <flux:column
                        @class(['hidden' => $this->isColumnToggled($column->getName())])
                        :sortable="$column->isSortable()"
                        :sorted="$this->getSortingColumn() === $column->getProperty()"
                        :direction="$this->getSortingDirection()"
                        wire:click.prevent="sort('{{ $column->getProperty() }}')">
                        {{ $column->getLabel() }}
                    </flux:column>
                @endforeach
            </flux:columns>

            <flux:rows>
                @foreach($this->models as $model)
                    <flux:row
                        wire:loading.class="animate-pulse"
                        :key="$model->getKey()">
                        @foreach($this->table->getColumns() as $column)
                            <flux:cell
                                @class(['hidden' => $this->isColumnToggled($column->getName())])>
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

        init() {
            console.debug("Example table loaded.");
        },

    }));
</script>
@endscript
