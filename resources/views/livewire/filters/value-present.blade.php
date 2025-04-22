<div>
    <flux:switch
        wire:model.live="state"
        :label="$this->filter->getLabel()"
        :description="$this->filter->getDescription()" />
</div>
