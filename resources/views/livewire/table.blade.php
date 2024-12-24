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
    <div class="flex justify-items-start mb-4">
        <flux:heading level="1" size="xl">
            {{ $heading }}
        </flux:heading>
        <flux:spacer/>
    </div>
    <div class="flex justify-items-between mb-4">
        @foreach($this->table->getFilters() as $filter)
            <livewire:flux-filter :$filter :name="$filter->getName()"/>
        @endforeach
        <flux:spacer/>
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
                <flux:column
                    :sortable="$column->isSortable()"
                    :sorted="$this->table->isSorted($column->getName())"
                    :align="$column->getAlignment()"
                >
                    {{ $column->getLabel() }}
                </flux:column>
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
</div>
