<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Illuminate\Support\Facades\Session;

/**
 * @property array $listeners
 */
trait HasFilters
{
    public function mountHasFilters(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }
    }

    public function getListeners(): array
    {
        return array_merge($this->listeners,
            [
                'table:{table.name}:filter:update' => 'handleFilterUpdate',
            ]
        );
    }

    public function getFilterFromTable(string $filter): Filter|false
    {
        $filters = array_filter($this->table->getFilters(), fn($f) => $f->getName() === $filter);
        if (empty($filters)) {
            return false;
        }
        return array_shift($filters);
    }

    public function handleFilterUpdate(string $filter, mixed $state): void
    {
        if ($filterRef = $this->getFilterFromTable($filter)) {
            $filterRef->setValue(new FilterValue($state));
        }
    }

    public function getFilterModalName(): string
    {
        return "table:{$this->table->name}:filter:modal";
    }

    public function getAllFilterSessionKeys(): array
    {
        return $this->table->hasFilters()
            ? array_map(fn(Filter $f) => $f->filterValueSessionKey(), $this->table->getFilters())
            : [];
    }

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

    public function resetFilter(string $filter): void
    {
        Session::forget($this->getFilterFromTable($filter)->filterValueSessionKey());
        $this->dispatch("table:{$this->table->name}:filter:reset");
    }

    public function resetFilters(): void
    {
        Session::forget($this->getAllFilterSessionKeys());
        $this->dispatch("table:{$this->table->name}:filter:reset");
    }
}
