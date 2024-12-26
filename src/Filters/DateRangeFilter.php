<?php

namespace Idkwhoami\FluxTables\Filters;

use Idkwhoami\FluxTables\Filters\Filter;

class DateRangeFilter extends Filter
{
    public static function make(string $name): static
    {
        return new static($name, view: 'filters.date-range', value: []);
    }

    public static function fromLivewire($value): static
    {
        $filter = parent::fromLivewire($value);
        $filter->view = 'filters.date-range';

        return $filter;
    }
}
