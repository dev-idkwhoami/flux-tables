<div>
    <flux:radio.group
        wire:model.live="state"
        :label="$this->filter->getLabel()"
        :description="$this->filter->getDescription()"
        variant="segmented">
        <flux:radio value="0" :label="__('flux-tables::filters/boolean.none')" />
        <flux:radio value="1" :label="$this->filter->getTrueLabel()" />
        <flux:radio value="2" :label="$this->filter->getFalseLabel()" />
    </flux:radio.group>
</div>
