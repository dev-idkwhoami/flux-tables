<flux:input.group :label="$this->filter->getLabel()" class="w-full min-w-min">
    <flux:date-picker
        :start-day="$this->weekStartDay"
        with-presets
        mode="range"
        wire:model.live="range" />
    {{--<flux:input size="sm" :label="__('flux-tables::filter.date-range.start')" wire:model.live="start" type="date"/>
    <flux:input size="sm" :label="__('flux-tables::filter.date-range.end')" wire:model.live="end" type="date"/>--}}
</flux:input.group>
