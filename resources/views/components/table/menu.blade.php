@props([
    'paginationOptions',
    'paginationValue',
    'toggleableColumns',
    'toggledColumns'
])
<flux:dropdown {{ $attributes->class(['flex items-center']) }}>
    <flux:button size="xs" square variant="ghost" icon="chevron-down"/>

    <flux:menu>

        @if(isset($toggleableColumns) && isset($toggledColumns))
            <flux:menu.submenu :heading="__('flux-tables::table/toggleable.dropdown')">
                <flux:menu.checkbox.group>
                    @foreach($toggleableColumns as $column)
                        <flux:menu.checkbox
                                wire:click.prevent="toggle('{{ $column->getName() }}')"
                                :value="$column->getName()"
                                :checked="in_array($column->getName(), $toggledColumns)">
                            {{ $column->getLabel() }}
                        </flux:menu.checkbox>
                    @endforeach
                </flux:menu.checkbox.group>
            </flux:menu.submenu>
        @endif

        @if(isset($paginationOptions))
            <flux:menu.submenu :heading="__('flux-tables::table/pagination.dropdown')">
                <flux:menu.radio.group>
                    @foreach($paginationOptions as $value)
                        <flux:menu.radio
                                wire:click.prevent="setPaginationValue({{ $value }})"
                                :checked="$paginationValue === $value"
                                :$value>
                            {{ __('flux-tables::table/pagination.items', ['number' => $value]) }}
                        </flux:menu.radio>
                    @endforeach
                </flux:menu.radio.group>
            </flux:menu.submenu>
        @endif

        <flux:menu.item wire:click.prevent="resetSorting" class="hover:text-orange-400">
            {{ __('flux-tables::table/sorting.reset') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
