<?php

namespace Idkwhoami\FluxTables\Filters;

use Idkwhoami\FluxTables\Enums\DeletedFilterOption;
use Illuminate\Contracts\Database\Query\Builder;
use Override;

class SoftDeleteFilter extends Filter
{
    #[Override]
    public function apply(Builder $query): Builder
    {
        if (!$this->callback) {
            return match (intval($this->value)) {
                DeletedFilterOption::WITHOUT_DELETED->value => $query->withoutTrashed(),
                DeletedFilterOption::WITH_DELETED->value => $query->withTrashed(),
                DeletedFilterOption::ONLY_DELETED->value => $query->onlyTrashed(),
                default => $query,
            };
        }

        return ($this->callback)($query, collect($this->value)->toArray());
    }

    public static function fromLivewire($value): static
    {
        return parent::fromLivewire($value)->fill(__CLASS__, $value);
    }

    public static function make(string $name): static
    {
        return new static($name, view: 'filters.deleted', emptyValue: null);
    }

    public function getOptions(): array
    {
        return DeletedFilterOption::cases();
    }
}
