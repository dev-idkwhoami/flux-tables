<flux:select :label="$this->filter->getLabel()"
             searchable
             :placeholder="__('flux-tables::filter.select.placeholder', ['label' => str($this->filter->getProperty())->plural()])"
             wire:model.live="state"
             variant="listbox"
             selected-suffix="{{ str($this->filter->getProperty())->plural() }} selected"
             multiple>
    @foreach($this->options as $value => $label)
        <flux:select.option :$value>
            {{ $label }}
        </flux:select.option>
    @endforeach
</flux:select>
