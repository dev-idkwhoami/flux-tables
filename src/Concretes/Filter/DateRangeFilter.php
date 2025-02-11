<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Carbon\Carbon;
use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Illuminate\Contracts\Database\Query\Builder;

class DateRangeFilter extends PropertyFilter
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

        $start = Carbon::make($value[0]);
        $end = Carbon::make($value[1]);

        if ($start && $end) {
            $query->whereBetween($this->property, [$start, $end]);
        }

        if ($start && !$end) {
            $query->where($this->property, '>=', $start);
        }

        if (!$start && $end) {
            $query->where($this->property, '<=', $end);
        }
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-date-range';
    }
}
