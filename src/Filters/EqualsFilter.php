<?php

namespace Idkwhoami\FluxTables\Filters;

use Laravel\SerializableClosure\SerializableClosure;

class EqualsFilter extends Filter
{
    public static function make(string $name): static
    {
        return new static($name, view: 'filters.equals');
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'view' => $this->view,
            'callback' => serialize(new SerializableClosure($this->callback)),
            'value' => $this->value,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['name'],
            $value['label'],
            'filters.equals',
            unserialize($value['callback'])->getClosure(),
            $value['value']
        );
    }

}
