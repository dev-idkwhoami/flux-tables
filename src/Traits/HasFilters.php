<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Session;

/**
 * @property array $listeners
 */
trait HasFilters
{
    /**
     * @return void
     * @throws \Exception
     */
    public function mountHasFilters(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }
    }

    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        return array_merge(
            $this->listeners,
            [
                'flux-tables::table:{table.name}:filter:update' => 'handleFilterUpdate',
            ]
        );
    }

    public function applyFilters(Builder $query): void
    {
        if ($this->table->hasFilters()) {
            $query->tap(
                fn(Builder $query) => collect($this->table->getFilters())
                    ->each(fn($filter) => $filter->apply($query))
            );
        }
    }

    /**
     * @param  string  $filter
     * @return Filter|null
     */
    public function getFilterFromTable(string $filter): ?Filter
    {
        $filters = array_filter($this->table->getFilters(), fn($f) => $f->getName() === $filter);
        return array_shift($filters);
    }

    /**
     * @param  string  $filter
     * @param  mixed  $state
     * @return void
     */
    public function handleFilterUpdate(string $filter, mixed $state): void
    {
        if ($filterRef = $this->getFilterFromTable($filter)) {
            $filterRef->setValue(new FilterValue($state));
        }
    }

    /**
     * @return string
     */
    public function getFilterModalName(): string
    {
        return "flux-tables::table:{$this->table->name}:filter:modal";
    }

    /**
     * @return string[]
     */
    public function getAllFilterSessionKeys(): array
    {
        return $this->table->hasFilters()
            ? array_map(fn(Filter $f) => $f->filterValueSessionKey(), $this->table->getFilters())
            : [];
    }

    /**
     * @return bool
     */
    public function hasActiveFilters(): bool
    {
        return Session::hasAny($this->getAllFilterSessionKeys());
    }

    /**
     * @return Filter[]
     */
    public function getActiveFilters(): array
    {
        return array_filter($this->table->getFilters(), fn(Filter $f) => Session::has($f->filterValueSessionKey()));
    }

    /**
     * @param  string  $filter
     * @return void
     */
    public function resetFilter(string $filter): void
    {
        $filter = $this->getFilterFromTable($filter);
        if ($filter) {
            Session::forget($filter->filterValueSessionKey());
            $this->dispatch("flux-tables::table:{$this->table->name}:filter:reset");
        }
    }

    /**
     * @return void
     */
    public function resetFilters(): void
    {
        Session::forget($this->getAllFilterSessionKeys());
        $this->dispatch("flux-tables::table:{$this->table->name}:filter:reset");
    }
}
