<flux:select :label="$this->filter->getLabel()"
             searchable
             :placeholder="__('flux-tables::filters/select.placeholder', ['label' => str($this->filter->getProperty())->plural()])"
             wire:model.live="state"
             variant="listbox"
             :selected-suffix="__('flux-tables::filters/select.selected-suffix', ['label' => str($this->filter->getProperty())->plural()])"
             multiple>
    @foreach($this->options as $value => $label)
        <flux:select.option :$value>
            {{ $label }}
        </flux:select.option>
    @endforeach
</flux:select>
