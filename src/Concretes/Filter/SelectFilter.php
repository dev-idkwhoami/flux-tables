<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
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
        if ($this->hasValue()) {
            $value = $this->getValue()->getValue();
            $query->whereIn($this->property, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-select';
    }

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        return join(", ", $this->getValue()->getValue());
    }
}
