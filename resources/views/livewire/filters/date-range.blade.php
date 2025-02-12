<flux:input.group :label="$this->filter->getLabel()" class="w-full min-w-min">
    <flux:input size="sm" label="Start" wire:model.live="start" type="date"/>

    <flux:input size="sm" label="End" wire:model.live="end" type="date"/>
</flux:input.group>
