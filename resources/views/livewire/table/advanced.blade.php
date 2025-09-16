<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Services\TableConfigurationService $configuration
        * @var \Idkwhoami\FluxTables\Services\TableStateService $state
        * @var \Illuminate\Database\Eloquent\Model $model
        */
    @endphp

    <div class="flex w-full flex-col space-y-4">
        {{-- Advanced Header - Title, column toggle, pagination --}}
        <div class="flex items-center justify-between">
            @if($configuration && $configuration->hasLabel())
                <flux:heading class="content-center" level="1" size="xl">
                    {{ $configuration->getLabel() }}
                </flux:heading>
            @else
                <flux:spacer/>
            @endif

            <div class="flex items-center space-x-3">
                {{-- Column Toggle --}}
                @if(!empty($this->getToggleableColumns()))
                    <flux:dropdown>
                        <flux:button size="sm" square icon="view-columns" variant="filled"/>
                        <flux:menu class="w-48">
                            @foreach($this->getToggleableColumns() as $column)
                                <flux:menu.item>
                                    <flux:checkbox
                                        wire:model.live="toggledColumns"
                                        :value="$column"
                                        :checked="in_array($column, $this->getToggledColumns())"
                                        wire:click="toggleColumn('{{ $column }}')"
                                    >
                                        {{ str($column)->title() }}
                                    </flux:checkbox>
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                @endif

                {{-- Pagination Options --}}
                <flux:select wire:model.live="perPage" size="sm" class="w-20">
                    @foreach($this->getPaginationOptions() as $option)
                        <flux:select.option :value="$option">{{ $option }}</flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Create Button --}}
                @if($configuration && $configuration->hasCreate())
                    <flux:modal.trigger :name="'create-modal-' . $this->tableId">
                        <flux:button size="sm" icon="plus" variant="filled">
                            {{ $configuration->getCreateText() ?? 'Create' }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        {{-- Search Row --}}
        @if($configuration->hasSearchableColumns())
            <div class="flex justify-end">
                <div class="w-64">
                    <flux:input
                        clearable
                        size="sm"
                        type="text"
                        icon="search"
                        placeholder="Search..."
                        wire:model.live.debounce.500ms="search"
                    />
                </div>
            </div>
        @endif

        {{-- Create Modal --}}
        @if($configuration && $configuration->hasCreate())
            <flux:modal :name="'create-modal-' . $this->tableId">
                @livewire($configuration->getCreateComponent())
            </flux:modal>
        @endif

        {{-- Advanced Data Table (WITH sorting, NO filtering) --}}
        <flux:table :paginate="$this->models">
            <flux:table.columns>
                @foreach($configuration->getColumns() as $column)
                    @if($column->isVisible() && !in_array($column->getName(), $this->getToggledColumns()))
                        <flux:table.column
                            :sortable="$column->isSortable()"
                            :sorted="$this->sortColumn === $column->getName()"
                            :direction="$this->sortColumn === $column->getName() ? $this->sortDirection : null"
                            wire:click="sortBy('{{ $column->getName() }}')"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ $column->getLabel() }}</span>
                                @if($column->hasTooltip())
                                    <flux:tooltip :content="$column->getTooltip()">
                                        <flux:icon name="information-circle" class="size-4 text-zinc-400" />
                                    </flux:tooltip>
                                @endif
                            </div>
                        </flux:table.column>
                    @endif
                @endforeach
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->models as $model)
                    <flux:table.row>
                        @foreach($configuration->getColumns() as $column)
                            @if($column->isVisible() && !in_array($column->getName(), $this->getToggledColumns()))
                                <flux:table.cell>
                                    {!! $column->render($model) !!}
                                </flux:table.cell>
                            @endif
                        @endforeach
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        {{-- Advanced Footer Note --}}
        <div class="text-sm text-zinc-500 text-center">
            @if($configuration->hasSearchableColumns())
                Click column headers to sort • Use search to find records
            @else
                Click column headers to sort
            @endif
        </div>
    </div>
</div>
