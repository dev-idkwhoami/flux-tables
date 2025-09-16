<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Services\TableConfigurationService $configuration
        * @var \Idkwhoami\FluxTables\Services\TableStateService $state
        * @var \Illuminate\Database\Eloquent\Model $model
        */
    @endphp

    <div class="flex w-full flex-col space-y-4">
        {{-- Header Section --}}
        <div class="flex flex-col space-y-3">
            <div class="flex items-center justify-between">
                @if($configuration && $configuration->hasLabel())
                    <flux:heading class="content-center" level="1" size="xl">
                        {{ $configuration->getLabel() }}
                    </flux:heading>
                @endif

                <div class="flex items-center space-x-2">
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

            {{-- Search and Filter Row --}}
            <div class="flex items-center justify-between">
                <flux:spacer/>

                <div class="flex items-center space-x-3">
                    {{-- Reset Filters --}}
                    @if($this->hasActiveFilters())
                        <flux:button
                            wire:click.prevent="resetAllFilters"
                            size="sm"
                            square
                            icon="funnel-x"
                            variant="ghost"
                        />
                    @endif

                    {{-- Filter Toggle --}}
                    @if($configuration->hasFilters())
                        <flux:modal.trigger :name="$this->getFilterModalName()">
                            <flux:button size="sm" square icon="funnel" variant="filled"/>
                        </flux:modal.trigger>
                    @endif

                    {{-- Search Input --}}
                    @if($configuration->hasSearchableColumns())
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
                    @endif
                </div>
            </div>

            {{-- Active Filter Pills --}}
            @if($this->hasActiveFilters())
                <div class="flex justify-end">
                    <x-flux-tables-table-filter-pills
                        :filters="$configuration->getFilters()"
                        :active-filters="$this->getActiveFilters()"
                    />
                </div>
            @endif
        </div>

        {{-- Filter Modal --}}
        @if($configuration && $configuration->hasFilters())
            <flux:modal :name="$this->getFilterModalName()" class="md:w-96">
                <flux:modal.header>
                    <flux:heading level="3">Filter {{ $configuration && $configuration->hasLabel() ? $configuration->getLabel() : 'Items' }}</flux:heading>
                </flux:modal.header>

                <flux:modal.body class="space-y-4">
                    @foreach($configuration->getFilters() as $filter)
                        @if($filter->isVisible())
                            <flux:field>
                                <flux:label>{{ $filter->getLabel() }}</flux:label>
                                @include($filter->component(), ['filter' => $filter])
                            </flux:field>
                        @endif
                    @endforeach
                </flux:modal.body>

                <flux:modal.footer>
                    <div class="flex justify-between w-full">
                        <flux:button variant="ghost" wire:click="resetAllFilters">
                            Clear All
                        </flux:button>
                        <flux:modal.close>
                            <flux:button variant="primary">Done</flux:button>
                        </flux:modal.close>
                    </div>
                </flux:modal.footer>
            </flux:modal>
        @endif

        {{-- Create Modal --}}
        @if($configuration && $configuration->hasCreate())
            <flux:modal :name="'create-modal-' . $this->tableId">
                @livewire($configuration->getCreateComponent())
            </flux:modal>
        @endif

        {{-- Data Table --}}
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

                {{-- Actions Column --}}
                @if($configuration->hasActions())
                    <flux:table.column>Actions</flux:table.column>
                @endif
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

                        {{-- Actions Cell --}}
                        @if($configuration->hasActions())
                            <flux:table.cell>
                                <div class="flex items-center space-x-2">
                                    @foreach($configuration->getActions() as $action)
                                        @if($action->shouldBeVisible($model))
                                            {!! $action->render($model->getKey()) !!}
                                        @endif
                                    @endforeach
                                </div>
                            </flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>