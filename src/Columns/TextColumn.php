<?php

namespace Idkwhoami\FluxTables\Columns;

class TextColumn extends Column
{

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'alignment' => $this->alignment,
            'view' => $this->view,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['view'],
            $value['name'],
            null,
            $value['label'],
            $value['sortable'],
            $value['searchable'],
            $value['alignment'],
        );
    }

    public static function make(string $name): static
    {
        return new static('columns.text', $name);
    }
}
