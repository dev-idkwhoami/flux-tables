<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class SelectFilter extends PropertyFilter
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): void
    {
        $filterValue = $this->getValue();

        if (empty($filterValue->getValue())) {
            return;
        }

        $value = $filterValue->getValue();

        $query->whereIn($this->property, $value);
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-select';
    }

    public function renderPill(): string|HtmlString|View
    {
        return join(", ",$this->getValue()->getValue());
    }
}
