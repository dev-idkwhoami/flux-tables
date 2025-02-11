<flux:select label="Deleted" :placeholder="trans('flux-tables::filter.deleted.placeholder')" size="sm"
             class="w-full min-w-min"
             wire:model.live="state" variant="listbox" icon="trash-2">
    @foreach(\Idkwhoami\FluxTables\Enums\DeletionState::cases() as $state)
        <flux:option :value="$state->value">
            {{ $state->getLabel() }}
        </flux:option>
    @endforeach
</flux:select>

