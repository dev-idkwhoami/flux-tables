<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DeletedFilter extends Filter
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $query): void
    {
        $value = $this->getValue();

        if (empty($value->getValue())) {
            return;
        }

        switch (DeletionState::from($value->getValue())) {
            case DeletionState::WithoutDeleted:
                /** @phpstan-ignore method.notFound */
                $query->withoutTrashed();
                break;
            case DeletionState::OnlyDeleted:
                /** @phpstan-ignore method.notFound */
                $query->onlyTrashed();
                break;
            case DeletionState::WithDeleted:
                /** @phpstan-ignore method.notFound */
                $query->withTrashed();
                break;

        }
    }

    /**
     * @inheritDoc
     */
    public function component(): string
    {
        return 'flux-filter-deleted';
    }

    /**
     * @inheritDoc
     */
    public function renderPill(): string|HtmlString|View
    {
        return DeletionState::from($this->getValue()->getValue())->getLabel();
    }
}
