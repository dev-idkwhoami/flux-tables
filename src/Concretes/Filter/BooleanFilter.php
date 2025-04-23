<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class BooleanFilter extends PropertyFilter
{
    protected ?string $description = null;
    protected string $trueLabel = 'Yes';
    protected string $falseLabel = 'No';

    protected mixed $default = null;

    public function falseLabel(string $falseLabel): BooleanFilter
    {
        $this->falseLabel = $falseLabel;
        return $this;
    }

    public function getFalseLabel(): string
    {
        return $this->falseLabel;
    }

    public function trueLabel(string $trueLabel): BooleanFilter
    {
        $this->trueLabel = $trueLabel;
        return $this;
    }

    public function getTrueLabel(): string
    {
        return $this->trueLabel;
    }

    public function getValueLabel(): string
    {
        return $this->getValue()->getValue() === 1 ? $this->getTrueLabel() : $this->getFalseLabel();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function description(?string $description): BooleanFilter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query): void
    {
        if ($this->hasValue()) {
            if ($this->getValue()->getValue() !== 0) {
                $query->where($this->property, '=', $this->getValue()->getValue() === 1);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-boolean';
    }

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        return __('flux-tables::filters/boolean.pill', ['value' => $this->getValue()->getValue(), 'name' => $this->name, 'label' => $this->getValueLabel()]);
    }
}
