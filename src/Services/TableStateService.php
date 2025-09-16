<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Events\Filter\FilterUpdatedEvent;
use Idkwhoami\FluxTables\Events\Filter\FilterResetEvent;
use Idkwhoami\FluxTables\Events\Filter\AllFiltersResetEvent;
use Idkwhoami\FluxTables\Events\Sorting\SortingChangedEvent;
use Illuminate\Contracts\Events\Dispatcher;

class TableStateService
{
    protected array $filterValues = [];
    protected ?string $sortColumn = null;
    protected string $sortDirection = 'asc';
    protected string $searchQuery = '';
    protected int $perPage = 15;
    protected array $toggledColumns = [];

    public function __construct(
        protected readonly string $tableId,
        protected readonly Dispatcher $eventDispatcher
    ) {
    }

    public function setFilterValue(string $filterName, mixed $value): static
    {
        $previousValue = $this->filterValues[$filterName] ?? null;
        $this->filterValues[$filterName] = $value;

        $this->eventDispatcher->dispatch(
            new FilterUpdatedEvent($this->tableId, $filterName, $value, $previousValue)
        );

        return $this;
    }

    public function resetFilter(string $filterName): static
    {
        unset($this->filterValues[$filterName]);

        $this->eventDispatcher->dispatch(
            new FilterResetEvent($this->tableId, $filterName)
        );

        return $this;
    }

    public function resetAllFilters(): static
    {
        $this->filterValues = [];

        $this->eventDispatcher->dispatch(
            new AllFiltersResetEvent($this->tableId)
        );

        return $this;
    }

    public function getFilterValue(string $filterName): mixed
    {
        return $this->filterValues[$filterName] ?? null;
    }

    public function getFilterValues(): array
    {
        return $this->filterValues;
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->filterValues);
    }

    public function getActiveFilters(): array
    {
        return array_keys($this->filterValues);
    }

    public function setSort(string $column, string $direction = 'asc'): static
    {
        $previousColumn = $this->sortColumn;
        $previousDirection = $this->sortDirection;

        $this->sortColumn = $column;
        $this->sortDirection = $direction;

        $this->eventDispatcher->dispatch(
            new SortingChangedEvent($this->tableId, $column, $direction, $previousColumn, $previousDirection)
        );

        return $this;
    }

    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function setSearch(string $query): static
    {
        $this->searchQuery = $query;
        return $this;
    }

    public function getSearch(): string
    {
        return $this->searchQuery;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function toggleColumn(string $columnName): static
    {
        if (in_array($columnName, $this->toggledColumns)) {
            $this->toggledColumns = array_diff($this->toggledColumns, [$columnName]);
        } else {
            $this->toggledColumns[] = $columnName;
        }
        return $this;
    }

    public function getToggledColumns(): array
    {
        return $this->toggledColumns;
    }

    public function isColumnToggled(string $columnName): bool
    {
        return in_array($columnName, $this->toggledColumns);
    }

    public function reset(): static
    {
        $this->filterValues = [];
        $this->sortColumn = null;
        $this->sortDirection = 'asc';
        $this->searchQuery = '';
        $this->toggledColumns = [];
        return $this;
    }

    public function toArray(): array
    {
        return [
            'filters' => $this->filterValues,
            'sort' => ['column' => $this->sortColumn, 'direction' => $this->sortDirection],
            'search' => $this->searchQuery,
            'perPage' => $this->perPage,
            'toggledColumns' => $this->toggledColumns,
        ];
    }
}
