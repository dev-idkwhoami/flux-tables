<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Carbon\Carbon;
use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

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

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        $display = '';

        $value = $this->getValue()->getValue();
        $start = Carbon::make($value[0]);
        $end = Carbon::make($value[1]);

        $format = 'm/d/Y';

        if ($start && $end) {
            $display = sprintf('%s - %s', $start->format($format), $end->format($format));
        }

        if (!$start && $end) {
            $display = sprintf('until %s', $end->format($format));
        }

        if ($start && !$end) {
            $display = sprintf('from %s', $start->format($format));
        }

        return Blade::render('<span>{{ $filter->getLabel() }}:</span><span>{{ $display }}</span>', ['display' => $display, 'filter' => $this]);
    }
}
