<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Configuration\TableConfiguration $configuration
        */
    @endphp

    <div class="flex w-full flex-col space-y-4">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            {{-- Title --}}
            @if($configuration && $configuration->hasLabel())
                <flux:heading class="content-center" level="1" size="xl">
                    {{ $configuration->getLabel() }}
                </flux:heading>
            @else
                <flux:spacer/>
            @endif

            {{-- Header Controls --}}
            <div class="flex items-center space-x-3">
                {{-- Column Toggle Button --}}
                @if($this->hasColumnToggling())
                    <flux:dropdown>
                        <flux:button size="sm" icon="adjustments-horizontal" variant="outline">
                            {{ __($this->getColumnTexts()['toggle_columns']) ?? 'Columns' }}
                        </flux:button>

                        <flux:menu keep-open>
                            <flux:menu.item wire:click="showAllColumns">
                                {{ __($this->getColumnTexts()['show_all']) ?? 'Show All' }}
                            </flux:menu.item>
                            <flux:menu.item wire:click="hideAllColumns">
                                {{ __($this->getColumnTexts()['hide_all']) ?? 'Hide All' }}
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($this->getToggleableColumns() as $column)
                                <flux:menu.checkbox
                                    :checked="$this->isColumnVisible($column->getName())"
                                    wire:click="toggleColumn('{{ $column->getName() }}')"
                                    keep-open
                                >
                                    {{ $column->getLabel() }}
                                </flux:menu.checkbox>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                @endif

                {{-- Filters Button --}}
                @if($this->hasFiltering())
                    <flux:modal.trigger :name="$this->getFilterModalName()">
                        <flux:button size="sm" icon="filter" variant="outline">
                            {{ __($this->getFilterTexts()['title']) ?? 'Filters' }}
                            @if($this->hasActiveFilters())
                                <flux:badge variant="solid" size="sm" color="blue">
                                    {{ count($this->getActiveFilters()) }}
                                </flux:badge>
                            @endif
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                {{-- Per Page Selector --}}
                @if($this->showPerPageSelector())
                    <flux:select wire:model.live="perPage" size="sm" class="w-20">
                        @foreach($this->getPaginationOptions() as $option)
                            <flux:select.option :value="$option">{{ $option }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                {{-- Create Button --}}
                @if($this->hasCreate())
                    <flux:modal.trigger :name="$this->getCreateModalName()">
                        <flux:button
                            size="{{ $this->getCreateSize() }}"
                            icon="{{ $this->getCreateIcon() }}"
                            variant="{{ $this->getCreateVariant() }}">
                            {{ $this->getCreateText() }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            </div>
        </div>

        {{-- Search Section --}}
        @if($this->hasSearch())
            <div class="flex justify-end">
                <div class="w-64">
                    <flux:input
                        clearable
                        size="sm"
                        type="text"
                        icon="search"
                        placeholder="{{ $this->getSearchPlaceholder() }}"
                        wire:model.live.debounce.500ms="search"
                    />
                </div>
            </div>
        @endif

        {{-- Active Filters Display --}}
        @if($this->hasFiltering() && $this->hasActiveFilters())
            <div class="flex flex-wrap gap-2">
                @foreach($this->getActiveFilters() as $filterName => $filterValue)
                    <flux:badge variant="outline" size="sm">
                        {{ $filterName }}: {{ $filterValue }}
                        <flux:button size="xs" variant="ghost" wire:click="resetFilter('{{ $filterName }}')">
                            <flux:icon name="x-mark" class="size-3" />
                        </flux:button>
                    </flux:badge>
                @endforeach
                <flux:button size="sm" variant="ghost" wire:click="resetAllFilters">
                    {{ __($this->getFilterTexts()['clear_all']) ?? 'Clear All' }}
                </flux:button>
            </div>
        @endif

        {{-- Bulk Actions Bar --}}
        @if($this->hasActions() && $this->showBulkActions() && $this->hasSelectedRows())
            <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <span class="text-sm text-blue-800">
                    {{ $this->getSelectedRowCount() }} {{ __($this->getActionTexts()['selected_items']) ?? 'items selected' }}
                </span>
                <div class="flex items-center space-x-2">
                    @foreach($this->getBulkActions() as $actionName => $action)
                        <flux:button size="sm" variant="outline" wire:click="executeBulkAction('{{ $actionName }}')">
                            {{ $actionName }}
                        </flux:button>
                    @endforeach
                    <flux:button size="sm" variant="ghost" wire:click="deselectAllRows">
                        {{ __($this->getActionTexts()['select_none']) ?? 'Clear Selection' }}
                    </flux:button>
                </div>
            </div>
        @endif

        {{-- Main Data Table --}}
        <div class="relative">

            <flux:table :paginate="$this->models">
            <flux:table.columns>
                {{-- Bulk Action Checkbox Column --}}
                @if($this->hasActions() && $this->showBulkActions())
                    <flux:table.column class="w-12">
                        <flux:checkbox
                            wire:model.live="selectedRows"
                            :checked="$this->areAllRowsSelected($this->models->pluck('id')->toArray())"
                            wire:click="toggleAllRowsSelection({{ json_encode($this->models->pluck('id')->toArray()) }})"
                        />
                    </flux:table.column>
                @endif

                {{-- Dynamic Columns --}}
                @foreach($this->getVisibleColumns() as $column)
                    <flux:table.column>
                        <div class="flex items-center space-x-1">
                            {{-- Sortable Column Header --}}
                            @if($column->isSortable() && $this->hasSorting())
                                <button
                                    wire:click="sortBy('{{ $column->getName() }}')"
                                    class="flex items-center space-x-1 hover:opacity-75 transition-opacity"
                                >
                                    <span>{{ $column->getLabel() }}</span>
                                    @if($this->isSortedBy($column->getName()))
                                        @if($this->getSortDirection($column->getName()) === 'asc')
                                            <flux:icon name="chevron-up" class="size-4" />
                                        @else
                                            <flux:icon name="chevron-down" class="size-4" />
                                        @endif
                                    @else
                                        <flux:icon name="chevron-up-down" class="size-4 opacity-50" />
                                    @endif
                                </button>
                            @else
                                <span>{{ $column->getLabel() }}</span>
                            @endif

                            {{-- Column Tooltip --}}
                            @if($column->hasTooltip())
                                <flux:tooltip :content="$column->getTooltip()">
                                    <flux:icon name="information-circle" class="size-4 text-zinc-400" />
                                </flux:tooltip>
                            @endif
                        </div>
                    </flux:table.column>
                @endforeach

                {{-- Actions Column --}}
                @if($this->hasActions() && !empty($this->getRowActions()))
                    <flux:table.column class="{{ $this->areActionsSticky() ? 'sticky right-0 bg-white' : '' }}">
                        {{ __($this->getActionTexts()['actions']) ?? 'Actions' }}
                    </flux:table.column>
                @endif
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->models as $model)
                    <flux:table.row>
                        {{-- Bulk Action Checkbox Cell --}}
                        @if($this->hasActions() && $this->showBulkActions())
                            <flux:table.cell>
                                <flux:checkbox
                                    wire:loading.attr="disabled"
                                    wire:model.live="selectedRows"
                                    :value="$model->id"
                                    :checked="$this->isRowSelected($model->id)"
                                />
                            </flux:table.cell>
                        @endif

                        {{-- Dynamic Column Cells --}}
                        @foreach($this->getVisibleColumns() as $column)
                            <flux:table.cell wire:loading.class="skeleton">
                                {!! $column->render($model) !!}
                            </flux:table.cell>
                        @endforeach

                        {{-- Actions Cell --}}
                        @if($this->hasActions() && !empty($this->getRowActions()))
                            <flux:table.cell class="skeleton {{ $this->areActionsSticky() ? 'sticky right-0 bg-white' : '' }}">
                                <div class="flex items-center space-x-2">
                                    @foreach($this->getRowActions() as $action)
                                        {{-- TODO Render row actions here --}}
                                        {{-- TODO This would need to be implemented based on your action structure --}}
                                    @endforeach
                                </div>
                            </flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
        </div>

        {{-- Create Modal --}}
        @if($this->hasCreate())
            <flux:modal
                :name="$this->getCreateModalName()"
                :variant="$this->getCreateModalVariant()"
                :size="$this->getCreateModalSize()"
                :dismissible="$this->isCreateModalDismissible()"
            >
                @livewire($this->getCreateComponent())
            </flux:modal>
        @endif

        {{-- Filters Modal --}}
        @if($this->hasFiltering())
            <flux:modal :name="$this->getFilterModalName()">
                <div class="space-y-6">
                    {{-- Header Section --}}
                    <div>
                        <flux:heading size="lg">{{ __($this->getFilterTexts()['title']) ?? 'Filters' }}</flux:heading>
                    </div>

                    {{-- Body Section --}}
                    <div class="space-y-4">
                        {{-- Render filter inputs based on configuration --}}
                        @foreach($this->getFilters() as $filter)
                            <div>
                                <flux:label>{{ $filter->getLabel() }}</flux:label>
                                @if($filter->getType() === 'text')
                                    <flux:input
                                        wire:model.live="filterValues.{{ $filter->getName() }}"
                                        placeholder="{{ $filter->getPlaceholder() ?? '' }}"
                                    />
                                @elseif($filter->getType() === 'select')
                                    <flux:select wire:model.live="filterValues.{{ $filter->getName() }}">
                                        <flux:select.option value="">{{ __('flux-tables::filters.show_all') }}</flux:select.option>
                                        @foreach($filter->getOptions() as $value => $label)
                                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                @elseif($filter->getType() === 'boolean')
                                    <flux:select wire:model.live="filterValues.{{ $filter->getName() }}">
                                        <flux:select.option value="">{{ __('flux-tables::filters.show_all') }}</flux:select.option>
                                        <flux:select.option value="1">{{ __('Yes') }}</flux:select.option>
                                        <flux:select.option value="0">{{ __('No') }}</flux:select.option>
                                    </flux:select>
                                @elseif($filter->getType() === 'date')
                                    <flux:input
                                        type="date"
                                        wire:model.live="filterValues.{{ $filter->getName() }}"
                                    />
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer Section --}}
                    <div class="flex space-x-2">
                        <flux:button variant="ghost" wire:click="resetAllFilters">
                            {{ __($this->getFilterTexts()['clear_all']) ?? 'Clear All' }}
                        </flux:button>
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="primary">
                                {{ __($this->getFilterTexts()['done']) ?? 'Done' }}
                            </flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            </flux:modal>
        @endif

        {{-- Footer Info --}}
        @if($this->hasSearch() && $this->hasActiveSearch())
            <div class="text-sm text-zinc-500 text-center">
                Showing search results for "{{ $this->search }}"
            </div>
        @endif
    </div>
</div>
