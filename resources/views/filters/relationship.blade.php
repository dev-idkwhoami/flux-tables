{{-- RelationshipFilter Template --}}
<div class="space-y-3">
    <flux:label>{{ $filter->getLabel() }}</flux:label>

    @if($searchable)
        {{-- Searchable Select with Live Search --}}
        <div class="relative" x-data="{
            open: false,
            search: '',
            loading: false,
            results: [],
            selected: @js($selectedValues),
            multiple: @js($multiple),

            async searchOptions(query) {
                if (query.length < {{ $minSearchLength }}) {
                    this.results = [];
                    return;
                }

                this.loading = true;
                try {
                    const response = await $wire.searchRelationshipOptions('{{ $filter->getName() }}', query);
                    this.results = response || [];
                } catch (error) {
                    console.error('Search error:', error);
                    this.results = [];
                }
                this.loading = false;
            },

            selectOption(option) {
                if (this.multiple) {
                    if (!this.selected.includes(option.id)) {
                        this.selected.push(option.id);
                        $wire.set('filterValues.{{ $filter->getName() }}', this.selected);
                    }
                } else {
                    this.selected = [option.id];
                    $wire.set('filterValues.{{ $filter->getName() }}', option.id);
                    this.open = false;
                }
                this.search = '';
                this.results = [];
            },

            removeOption(optionId) {
                if (this.multiple) {
                    this.selected = this.selected.filter(id => id !== optionId);
                    $wire.set('filterValues.{{ $filter->getName() }}', this.selected);
                } else {
                    this.selected = [];
                    $wire.set('filterValues.{{ $filter->getName() }}', null);
                }
            }
        }">
            {{-- Selected Items Display --}}
            @if($selectedOptions->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($selectedOptions as $option)
                        <flux:badge variant="outline" size="sm">
                            {{ $option->name ?? $option->id }}
                            <flux:button
                                size="xs"
                                variant="ghost"
                                x-on:click="removeOption({{ $option->id }})"
                                class="ml-1"
                            >
                                <flux:icon name="x-mark" class="size-3"/>
                            </flux:button>
                        </flux:badge>
                    @endforeach
                </div>
            @endif

            {{-- Search Input --}}
            <div class="relative">
                <flux:input
                    x-model="search"
                    x-on:input.debounce.300ms="searchOptions($event.target.value)"
                    x-on:focus="open = true"
                    placeholder="Search {{ strtolower($filter->getLabel()) }}..."
                    class="w-full"
                />

                {{-- Search Results Dropdown --}}
                <div
                    x-show="open && (results.length > 0 || loading)"
                    x-on:click.away="open = false"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                >
                    {{-- Loading State --}}
                    <div x-show="loading" class="p-3 text-center text-gray-500">
                        Searching...
                    </div>

                    {{-- Search Results --}}
                    <template x-for="option in results" :key="option.id">
                        <div
                            x-on:click="selectOption(option)"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                            :class="{ 'bg-blue-50': selected.includes(option.id) }"
                        >
                            <span x-text="option.text"></span>
                        </div>
                    </template>

                    {{-- No Results --}}
                    <div
                        x-show="!loading && results.length === 0 && search.length >= {{ $minSearchLength }}"
                        class="p-3 text-center text-gray-500"
                    >
                        No results found
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Simple Select (Non-searchable) --}}
        <flux:select
            wire:model.live="filterValues.{{ $filter->getName() }}"
            :multiple="$multiple"
        >
            @if(!$multiple)
                <flux:select.option value="">Select {{ strtolower($filter->getLabel()) }}</flux:select.option>
            @endif

            @foreach($selectedOptions as $option)
                <flux:select.option
                    :value="$option->id"
                    :selected="in_array($option->id, $selectedValues)"
                >
                    {{ $option->name ?? $option->id }}
                </flux:select.option>
            @endforeach
        </flux:select>
    @endif

    @if($filter->getDescription())
        <div class="text-sm text-gray-500">
            {{ $filter->getDescription() }}
        </div>
    @endif
</div>