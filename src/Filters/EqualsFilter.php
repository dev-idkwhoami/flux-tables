<?php

namespace Idkwhoami\FluxTables\Filters;

class EqualsFilter extends Filter
{
    public static function make(string $name): static
    {
        return new static($name, view: 'filters.equals');
    }

    public static function fromLivewire($value): static
    {
        $filter = parent::fromLivewire($value);
        $filter->view = 'filters.equals';

        return $filter;
    }
}
