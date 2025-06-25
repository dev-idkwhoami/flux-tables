<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class ValuePresentFilter extends PropertyFilter
{
    protected ?string $description = null;
    protected string $pillContent;

    protected mixed $default = false;

    public function getPillContent(): string
    {
        return $this->pillContent;
    }

    public function pillContent(string $pillContent): ValuePresentFilter
    {
        $this->pillContent = $pillContent;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function description(?string $description): ValuePresentFilter
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
            if ($this->getValue()->getValue() !== false) {
                $query->whereNotNull($this->property);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-value-present';
    }

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        return $this->getPillContent();
    }
}
