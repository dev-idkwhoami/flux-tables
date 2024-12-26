@php
    /**
     * @var $column \Idkwhoami\FluxTables\Columns\Column
     * @var $filter \Idkwhoami\FluxTables\Filters\Filter
     * @var $model \Illuminate\Database\Eloquent\Model
     */
    $modelName = class_basename($this->table->getModel());
    $heading = __(str($modelName)->plural()->headline()->value());
    $placeholder = __("Search for " . str($modelName)->lower()->plural() . "...");
@endphp
<div>
    <div class="flex justify-items-start space-x-4 mb-4">
        <flux:heading level="1" size="xl">
            {{ $heading }}
        </flux:heading>
        <flux:spacer/>
    </div>
    <div class="flex justify-items-between gap-x-2 mb-4">
        @if($this->table->getPerPageOptions() !== null)
            <div class="max-w-lg">
                <flux:select size="sm" wire:model.live="table.perPage" variant="listbox">
                    @foreach($this->table->getPerPageOptions() as $perPageOption)
                        <flux:option :selected="$this->table->getPerPage() === $perPageOption">
                            {{ $perPageOption }}
                        </flux:option>
                    @endforeach
                </flux:select>
            </div>
        @endif
        @if($this->table->hasToggleableColumns())
            <flux:dropdown>
                <flux:button size="sm" variant="filled" icon="columns-3"/>

                <flux:menu>
                    @foreach($this->table->getToggleableColumns() as $index => $column)
                        <flux:menu.checkbox wire:model.live="table.columns.{{ $index }}.toggled">
                            {{ $column->getLabel() }}
                        </flux:menu.checkbox>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        @endif
        <flux:spacer/>
        @if($this->table->hasActiveFilters())
            <flux:tooltip content="Reset filters">
                <flux:button variant="filled" class="hover:text-red-400" size="sm" color="primary"
                             wire:click="resetFilters" icon="filter-x"/>
            </flux:tooltip>
        @endif
        <flux:modal.trigger name="filters">
            <flux:tooltip content="Apply filters">
                <flux:button size="sm" variant="filled" icon="filter"/>
            </flux:tooltip>
        </flux:modal.trigger>
        @if($this->table->hasSearchable())
            <div>
                <flux:input
                    size="sm"
                    icon="search"
                    wire:model.live="table.search"
                    clearable
                    :$placeholder
                />
            </div>
        @endif
    </div>
    <flux:table :paginate="$this->models">
        <flux:columns>
            @foreach($this->table->getColumns() as $column)
                @if(!$column->isToggleable() || $column->isToggled())
                    @if($column->isSortable())
                        <flux:column
                            x-data="{ column: $wire.$entangle('table.sortColumn', true), direction: $wire.$entangle('table.sortDirection', false) }"
                            x-effect="console.log(column, direction)"
                            @click="$wire.sort('{{ $column->getName() }}')"
                            sortable
                            :sorted="$this->table->isSorted($column->getName())"
                            :direction="$this->table->getSortDirection()"
                            :align="$column->getAlignment()->value"
                        >
                            {{ $column->getLabel() }}
                        </flux:column>
                    @else
                        <flux:column
                            :align="$column->getAlignment()->value"
                        >
                            {{ $column->getLabel() }}
                        </flux:column>
                    @endif
                @endif
            @endforeach
        </flux:columns>

        <flux:rows>
            @foreach($this->models as $model)
                <flux:rows>
                    @foreach($this->table->getColumns() as $column)
                        {{ $column->render($model) }}
                    @endforeach
                </flux:rows>
            @endforeach
        </flux:rows>
    </flux:table>
    <flux:modal :dismissible="false" variant="flyout" class="space-y-6" name="filters">
        @foreach($this->table->getFilters() as $index => $filter)
            <livewire:flux-filter :key="$index . '_' . $filter->getName() . '_' . $filter->getView()" :$index :$filter
                                  :name="$filter->getName()"/>
        @endforeach
    </flux:modal>
</div>
