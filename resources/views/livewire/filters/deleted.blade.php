<flux:select :label="$this->filter->getLabel()" :placeholder="trans('flux-tables::filters/deleted.placeholder')" size="sm"
             class="w-full min-w-min"
             wire:model.live="state" variant="listbox" icon="trash-2">
    @foreach(\Idkwhoami\FluxTables\Enums\DeletionState::cases() as $state)
        <flux:select.option :value="$state->value">
            {{ $state->getLabel() }}
        </flux:select.option>
    @endforeach
</flux:select>

