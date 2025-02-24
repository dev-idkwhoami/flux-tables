<flux:input.group :label="$this->filter->getLabel()" class="w-full min-w-min">
    <flux:date-picker
        :start-day="$this->weekStartDay"
        with-presets
        mode="range"
        wire:model.live="range" />
</flux:input.group>
