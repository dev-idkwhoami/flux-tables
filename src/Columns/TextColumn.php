<?php

namespace Idkwhoami\FluxTables\Columns;

use Idkwhoami\FluxTables\Enums\ColumnAlignment;

class TextColumn extends Column
{
    protected bool $list = false;

    public function __construct(
        ?string $view = null,
        ?string $name = null,
        ?\Closure $transform = null,
        ?string $label = null,
        bool $sortable = false,
        bool $searchable = false,
        bool $toggleable = false,
        bool $toggled = false,
        ColumnAlignment $alignment = ColumnAlignment::Left
    ) {
        parent::__construct($view, $name, $transform, $label, $sortable, $searchable,
            $toggleable, $toggled, $alignment);
    }

    public function list(bool $list = true): static
    {
        $this->list = $list;
        $this->view = $list ? 'columns.list' : $this->view;

        return $this;
    }

    public static function make(string $name): static
    {
        return new static('columns.text', $name);
    }
}
