<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Illuminate\Contracts\Database\Query\Builder;

class DeletedFilter extends Filter
{

    /**
     * @phpstan-ignore method.notFound
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
                $query->withoutTrashed();
                break;
            case DeletionState::OnlyDeleted:
                $query->onlyTrashed();
                break;
            case DeletionState::WithDeleted:
                $query->withTrashed();
                break;

        }
    }

    public function component(): string
    {
        return 'flux-filter-deleted';
    }
}
