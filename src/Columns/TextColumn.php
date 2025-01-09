<?php

namespace Idkwhoami\FluxTables\Columns;

use Idkwhoami\FluxTables\Enums\ColumnAlignment;

class TextColumn extends Column
{
    public function __construct(
        ?string $view = null,
        ?string $name = null,
        ?string $column = null,
        ?string $placeholder = null,
        ?\Closure $transform = null,
        ?string $label = null,
        bool $sortable = false,
        bool $searchable = false,
        bool $toggleable = false,
        bool $toggled = false,
        ColumnAlignment $alignment = ColumnAlignment::Left
    ) {
        parent::__construct($view, $name, $column, $placeholder, $transform, $label, $sortable, $searchable,
            $toggleable, $toggled, $alignment);
    }

    public static function fromLivewire($value): static
    {
        return self::make($value['name'])->fill(__CLASS__, $value);
    }

    public static function make(string $name): static
    {
        return new static('columns.text', $name);
    }
}
