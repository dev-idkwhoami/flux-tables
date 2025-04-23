<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column
        * @var \Idkwhoami\FluxTables\Abstracts\Filter\Filter $filter
        * @var \Illuminate\Database\Eloquent\Model $model
        */
        //dump(session()->all())
    @endphp

    <div class="flex w-full flex-col space-y-2">


        <div class="flex flex-col space-y-3">
            <div class="flex space-x-3">
                @if($this->table->hasLabel())
                    <flux:heading class="content-center" level="1" size="xl">
                        {{ $this->table->getLabel() }}
                    </flux:heading>
                @endif
                <flux:dropdown class="flex items-center">
                    <flux:button size="xs" square variant="ghost" icon="chevron-down"/>

                    <flux:menu>

                        <flux:menu.submenu :heading="__('flux-tables::table/toggleable.dropdown')">
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

                        <flux:menu.submenu :heading="__('flux-tables::table/pagination.dropdown')">
                            <flux:menu.radio.group>
                                @foreach($this->getPaginationOptions() as $value)
                                    <flux:menu.radio
                                        wire:click.prevent="setPaginationValue({{ $value }})"
                                        :checked="$this->getPaginationValue() === $value"
                                        :$value>
                                        {{ __('flux-tables::table/pagination.items', ['number' => $value]) }}
                                    </flux:menu.radio>
                                @endforeach
                            </flux:menu.radio.group>
                        </flux:menu.submenu>

                        <flux:menu.item wire:click.prevent="resetSorting" class="hover:text-orange-400">
                            {{ __('flux-tables::table/sorting.reset') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
                <flux:spacer/>
                @if($this->table->hasCreate())
                    <div class="flex items-center">
                        <flux:modal.trigger :name="$this->table->getCreateModalName()">
                            <flux:button size="sm" icon="plus" variant="primary">
                                {{ $this->table->getCreateText() }}
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal class="p-2" :name="$this->table->getCreateModalName()">
                            @livewire($this->table->getCreateComponent(), ['table' => $this->table, 'modal' => $this->table->getCreateModalName()], key($this->table->getCreateComponent() . '-key'))
                        </flux:modal>
                    </div>
                @endif
            </div>
            <div class="flex space-x-3">
                <flux:spacer/>

                @if($this->table->hasFilters())
                    <div class="flex items-center space-x-2">
                        <flux:spacer/>
                        @if($this->hasActiveFilters())
                            <flux:button wire:click.prevent="resetFilters"
                                         class="hover:text-red-400"
                                         size="sm"
                                         square
                                         icon="funnel-x"
                                         variant="filled"/>
                        @endif

                        <flux:modal.trigger :name="$this->getFilterModalName()">
                            <flux:button size="sm" square icon="funnel" variant="filled"/>
                        </flux:modal.trigger>
                    </div>
                @endif
                <div class="w-42">
                    <flux:input clearable size="sm" type="text" icon="search" wire:model.live.debounce="search"/>
                </div>
            </div>
            <div class="flex space-x-3">
                <flux:spacer/>
                @if($this->hasActiveFilters())
                    <div>
                        @foreach($this->getActiveFilters() as $filter)
                            <flux:badge size="sm" class="flex space-x-1" variant="pill">
                                {!! $filter->renderPill() !!}
                                <flux:badge.close wire:click.prevent="resetFilter('{{ $filter->getName() }}')"/>
                            </flux:badge>
                        @endforeach
                    </div>
                @endif
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

            <flux:table.columns>
                @foreach($this->table->getColumns() as $column)
                    @if($column->isSortable())
                        <flux:table.column
                            @class(['hidden' => $this->isColumnToggled($column->getName())])
                            sortable
                            :sorted="$this->getSortingColumn() === $column->getSortableProperty()"
                            :direction="$this->getSortingDirection()"
                            :key="$column->getName()"
                            wire:click.prevent="sort('{{ $column->getSortableProperty() }}')">
                            {{ $column->getLabel() }}
                        </flux:table.column>
                    @else
                        <flux:table.column
                            @class(['hidden' => $this->isColumnToggled($column->getName())])
                            :key="$column->getName()">
                            {{ $column->getLabel() }}
                        </flux:table.column>
                    @endif
                @endforeach
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->models as $model)
                    <flux:table.row
                        wire:loading.class="animate-pulse"
                        :key="$model->getKey()">
                        @foreach($this->table->getColumns() as $column)
                            <flux:table.cell
                                @class(['hidden' => $this->isColumnToggled($column->getName())])>
                                {{ $column->render($model) }}
                            </flux:table.cell>
                        @endforeach
                    </flux:table.row>
                @endforeach
            </flux:table.rows>

        </flux:table>

    </div>

</div>
