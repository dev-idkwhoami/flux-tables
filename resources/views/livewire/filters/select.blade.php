<flux:select :label="$this->filter->getLabel()"
             searchable
             placeholder="Select one or more {{ str($this->filter->getProperty())->plural() }}"
             wire:model.live="state"
             variant="listbox"
             selected-suffix="{{ str($this->filter->getProperty())->plural() }} selected"
             multiple>
    @foreach($this->options as $value => $label)
        <flux:option :$value>
            {{ $label }}
        </flux:option>
    @endforeach
</flux:select>
