<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Configuration\Persistence;
use Idkwhoami\FluxTables\Enums\StateType;
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

    protected ?StatePersistenceService $persistenceService = null;
    protected array $persistenceConfigs = [];
    protected bool $initialized = false;

    public function __construct(
        protected readonly string $tableId,
        protected readonly Dispatcher $eventDispatcher
    ) {
        $this->persistenceService = new StatePersistenceService($tableId);
    }

    /**
     * Configure persistence for different state types
     */
    public function configurePersistence(StateType $stateType, Persistence $config): static
    {
        $this->persistenceConfigs[$stateType->value] = $config;
        return $this;
    }

    /**
     * Initialize state from persistence if not already done
     */
    public function initialize(): static
    {
        if ($this->initialized) {
            return $this;
        }

        // Load each state type from persistence
        foreach ($this->persistenceConfigs as $stateTypeKey => $config) {
            $stateType = StateType::from($stateTypeKey);
            $this->loadStateFromPersistence($stateType, $config);
        }

        $this->initialized = true;
        return $this;
    }

    /**
     * Load specific state type from persistence
     */
    protected function loadStateFromPersistence(StateType $stateType, Persistence $config): void
    {
        $data = $this->persistenceService->load($config, $stateType);

        if ($data === null) {
            return;
        }

        switch ($stateType) {
            case StateType::COLUMN_TOGGLES:
                $this->toggledColumns = $data;
                break;
            case StateType::FILTERS:
                $this->filterValues = $data;
                break;
            case StateType::SORTING:
                if (isset($data['column'])) {
                    $this->sortColumn = $data['column'];
                    $this->sortDirection = $data['direction'] ?? 'asc';
                }
                break;
            case StateType::SEARCH:
                $this->searchQuery = $data;
                break;
            case StateType::PAGINATION:
                $this->perPage = $data;
                break;
        }
    }

    /**
     * Save specific state type to persistence
     */
    protected function saveStateToPersistence(StateType $stateType): void
    {
        $config = $this->persistenceConfigs[$stateType->value] ?? null;
        if (!$config) {
            return;
        }

        $data = match ($stateType) {
            StateType::COLUMN_TOGGLES => $this->toggledColumns,
            StateType::FILTERS => $this->filterValues,
            StateType::SORTING => ['column' => $this->sortColumn, 'direction' => $this->sortDirection],
            StateType::SEARCH => $this->searchQuery,
            StateType::PAGINATION => $this->perPage,
        };

        $this->persistenceService->save($config, $stateType, $data);
    }

    /**
     * Get persistence configuration for a state type
     */
    public function getPersistenceConfig(StateType $stateType): ?Persistence
    {
        return $this->persistenceConfigs[$stateType->value] ?? null;
    }

    public function setFilterValue(string $filterName, mixed $value): static
    {
        $previousValue = $this->filterValues[$filterName] ?? null;
        $this->filterValues[$filterName] = $value;

        $this->eventDispatcher->dispatch(
            new FilterUpdatedEvent($this->tableId, $filterName, $value, $previousValue)
        );

        $this->saveStateToPersistence(StateType::FILTERS);
        return $this;
    }

    public function resetFilter(string $filterName): static
    {
        unset($this->filterValues[$filterName]);

        $this->eventDispatcher->dispatch(
            new FilterResetEvent($this->tableId, $filterName)
        );

        $this->saveStateToPersistence(StateType::FILTERS);
        return $this;
    }

    public function resetAllFilters(): static
    {
        $this->filterValues = [];

        $this->eventDispatcher->dispatch(
            new AllFiltersResetEvent($this->tableId)
        );

        $this->saveStateToPersistence(StateType::FILTERS);
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

        $this->saveStateToPersistence(StateType::SORTING);
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
        $this->saveStateToPersistence(StateType::SEARCH);
        return $this;
    }

    public function getSearch(): string
    {
        return $this->searchQuery;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;
        $this->saveStateToPersistence(StateType::PAGINATION);
        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function toggleColumn(string $columnName): static
    {
        if (in_array($columnName, $this->toggledColumns)) {
            // Column is currently toggled (hidden), so show it
            $this->toggledColumns = array_diff($this->toggledColumns, [$columnName]);
        } else {
            // Column is currently visible, so hide it
            $this->toggledColumns[] = $columnName;
        }
        $this->saveStateToPersistence(StateType::COLUMN_TOGGLES);
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

    public function showColumn(string $columnName): static
    {
        $this->toggledColumns = array_diff($this->toggledColumns, [$columnName]);
        $this->saveStateToPersistence(StateType::COLUMN_TOGGLES);
        return $this;
    }

    public function hideColumn(string $columnName): static
    {
        if (!in_array($columnName, $this->toggledColumns)) {
            $this->toggledColumns[] = $columnName;
        }
        $this->saveStateToPersistence(StateType::COLUMN_TOGGLES);
        return $this;
    }

    public function resetColumnVisibility(?array $defaultToggledColumns = null): static
    {
        $this->toggledColumns = $defaultToggledColumns ?? [];
        $this->saveStateToPersistence(StateType::COLUMN_TOGGLES);
        return $this;
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
