<div>
    @php
        /**
        * @var \Idkwhoami\FluxTables\Services\TableConfigurationService $configuration
        * @var \Illuminate\Database\Eloquent\Model $model
        */
    @endphp

    <div class="flex w-full flex-col space-y-4">
        {{-- Simple Header - Just title and search --}}
        <div class="flex items-center justify-between">
            @if($configuration && $configuration->hasLabel())
                <flux:heading class="content-center" level="1" size="xl">
                    {{ $configuration->getLabel() }}
                </flux:heading>
            @else
                <flux:spacer/>
            @endif

            <div class="flex items-center space-x-3">
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

        {{-- Search Only (No filters, no advanced features) --}}
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

        {{-- Simple Data Table (No sorting, no actions) --}}
        <flux:table :paginate="$this->models">
            <flux:table.columns>
                @foreach($configuration->getColumns() as $column)
                    @if($column->isVisible())
                        <flux:table.column>
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
                            @if($column->isVisible())
                                <flux:table.cell>
                                    {!! $column->render($model) !!}
                                </flux:table.cell>
                            @endif
                        @endforeach
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        {{-- Simple Footer Note --}}
        @if($configuration->hasSearchableColumns())
            <div class="text-sm text-zinc-500 text-center">
                Use the search box above to find specific records
            </div>
        @endif
    </div>
</div>