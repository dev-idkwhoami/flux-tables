<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Flux\DateRange;
use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

class DateRangeFilter extends PropertyFilter
{
    public DateRange $range;

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): void
    {
        if ($this->hasValue()) {
            $value = $this->getValue()?->getValue();

            if (!($value instanceof DateRange)) {
                throw new \Exception('Unable to apply date range filter without a valid value');
            }

            $query->whereBetween($this->property, $value);
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
     * @inheritdoc
     */
    public function setValue(FilterValue $value): void
    {
        $range = $value->getValue();
        parent::setValue(new FilterValue([array_shift($range), array_pop($range)]));
    }

    /**
     * @inheritdoc
     */
    public function getValue(): FilterValue
    {
        $range = Session::get($this->filterValueSessionKey(), []);
        return new FilterValue(new DateRange(array_shift($range), array_pop($range)));
    }

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        $value = $this->getValue()->getValue();

        if (!($value instanceof DateRange)) {
            throw new \Exception('Unable to display date range filter pill without a valid value');
        }

        $format = trans('flux-tables::formats.date') ?? 'm/d/Y';

        $display = 'error';

        if ($value->hasStart() && $value->hasEnd()) {
            $display = trans(
                'flux-tables::filter.date-range.between',
                [
                    'start' => $value->start()?->format($format),
                    'end' => $value->end()?->format($format)
                ]
            );
        }
        if ($value->hasStart() && !$value->hasEnd()) {
            $display = trans(
                'flux-tables::filter.date-range.after',
                ['start' => $value->start()?->format($format) ?? 'error']
            );
        }
        if (!$value->hasStart() && $value->hasEnd()) {
            $display = trans(
                'flux-tables::filter.date-range.before',
                ['end' => $value->end()?->format($format) ?? 'error']
            );
        }

        return Blade::render(
            '<span>{{ $filter->getLabel() }}:</span><span>{{ $display }}</span>',
            ['display' => $display, 'filter' => $this]
        );
    }

}
