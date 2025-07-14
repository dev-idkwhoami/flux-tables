<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Abstracts\Column\Column $column
        * @var \Idkwhoami\FluxTables\Abstracts\Filter\Filter $filter
        * @var \Illuminate\Database\Eloquent\Model $model
        */
    @endphp

    <div class="flex w-full flex-col space-y-2">


        <div class="flex flex-col space-y-3">
            <div class="flex space-x-3">
                @if($this->table->hasLabel())
                    <flux:heading class="content-center" level="1" size="xl">
                        {{ $this->table->getLabel() }}
                    </flux:heading>
                @endif
                <x-flux-tables-table-menu
                    :pagination-options="$this->getPaginationOptions()"
                    :pagination-value="$this->getPaginationValue()"
                    :toggleable-columns="$this->getToggleableColumns()"
                    :toggled-columns="$this->getToggledColumns()"
                />
                <flux:spacer/>
                @if($this->table->hasCreate())
                    <x-flux-tables-table-create
                        :modal-classes="$this->getCreateModalClasses()"
                        :modal-variant="$this->getCreateModalVariant()"
                        :modal-position="$this->getCreateModalPosition()"
                        :dismissible="$this->isCreateModalDismissible()"
                        :table="$this->table"/>
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
                    <x-flux-tables-table-filter-pills :filters="$this->getFilters()"/>
                @endif
            </div>
        </div>


        @if($this->table->hasFilters())
            <x-flux-tables-table-filters :modal="$this->getFilterModalName()" :table="$this->table"/>
        @endif

        <flux:table :paginate="$this->models">

            <flux:table.columns>
                @foreach($this->table->getColumns() as $column)
                    <x-flux-tables-table-column
                        :column="$column"
                        :sorting-column="$this->getSortingColumn()"
                        :sorting-direction="$this->getSortingDirection()"
                        :toggled-columns="$this->getToggledColumns()"
                    />
                @endforeach
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->models as $model)
                    <x-flux-tables-table-row
                        :model="$model"
                        :table="$this->table"
                        :toggled-columns="$this->getToggledColumns()"
                    />
                @endforeach
            </flux:table.rows>

        </flux:table>

    </div>

</div>
