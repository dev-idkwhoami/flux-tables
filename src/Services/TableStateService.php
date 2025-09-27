<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Configuration\Persistence;
use Idkwhoami\FluxTables\Enums\StateType;
use Idkwhoami\FluxTables\Events\Filter\FilterUpdatedEvent;
use Idkwhoami\FluxTables\Events\Filter\FilterResetEvent;
use Idkwhoami\FluxTables\Events\Filter\AllFiltersResetEvent;
use Idkwhoami\FluxTables\Events\Sorting\SortingChangedEvent;
use Idkwhoami\FluxTables\Filters\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Manages table state including filters, sorting, search, pagination, and column toggles
 *
 * This service centralizes all table state management operations and provides
 * persistence capabilities through configurable drivers (session, database).
 * It also includes filter sharing/caching functionality for collaborative features.
 *
 * @package Idkwhoami\FluxTables\Services
 */
class TableStateService
{
    protected array $filterValues = [];
    protected ?string $sortColumn = null;
    protected string $sortDirection = 'asc';
    protected string $searchQuery = '';
    protected int $perPage = 0;
    protected array $toggledColumns = [];

    protected ?StatePersistenceService $persistenceService = null;
    protected array $persistenceConfigs = [];
    protected bool $initialized = false;

    // Filter sharing/caching properties
    protected const CACHE_PREFIX = 'flux_table_filters:';
    protected const DEFAULT_TTL = 86400; // 24 hours
    protected int $ttl;

    /**
     * Create a new table state service instance
     *
     * @param string $tableId Unique identifier for the table instance
     * @param Dispatcher $eventDispatcher Event dispatcher for broadcasting state changes
     * @param int $ttl Time-to-live for cached filter states in seconds
     */
    public function __construct(
        protected readonly string $tableId,
        protected readonly Dispatcher $eventDispatcher,
        int $ttl = self::DEFAULT_TTL
    ) {
        $this->persistenceService = new StatePersistenceService($tableId);
        $this->ttl = $ttl;
    }

    /**
     * Configure persistence for different state types
     *
     * @param StateType $stateType The type of state to configure persistence for
     * @param Persistence $config Persistence configuration (driver, TTL, metadata)
     * @return static Returns self for method chaining
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

    /**
     * Set the value for a specific filter
     *
     * @param string $filterName Name of the filter to update
     * @param mixed $value The filter value to set
     * @return static Returns self for method chaining
     */
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

    /**
     * Get the current value for a specific filter
     *
     * @param string $filterName Name of the filter to retrieve
     * @return mixed The current filter value or null if not set
     */
    public function getFilterValue(string $filterName): mixed
    {
        return $this->filterValues[$filterName] ?? null;
    }

    /**
     * Get all current filter values
     *
     * @return array Associative array of filter names and their values
     */
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

    // Filter sharing/caching methods (merged from FilterStateService)

    /**
     * Generate a unique filter ID for sharing
     */
    public function generateFilterId(): string
    {
        return Str::random(12);
    }

    /**
     * Store filter state and return unique ID for sharing
     */
    public function storeFilterState(array $filters, ?string $tableId = null): string
    {
        $filterId = $this->generateFilterId();
        $filterData = $this->serializeFilters($filters);

        $stateData = [
            'filters' => $filterData,
            'table_id' => $tableId ?? $this->tableId,
            'created_at' => now()->toISOString(),
            'expires_at' => now()->addSeconds($this->ttl)->toISOString(),
        ];

        Cache::put(
            $this->getCacheKey($filterId),
            $stateData,
            $this->ttl
        );

        return $filterId;
    }

    /**
     * Retrieve filter state by ID
     */
    public function getFilterState(string $filterId): ?array
    {
        $cacheKey = $this->getCacheKey($filterId);
        $stateData = Cache::get($cacheKey);

        if (!$stateData) {
            return null;
        }

        return [
            'filters' => $this->deserializeFilters($stateData['filters']),
            'table_id' => $stateData['table_id'] ?? null,
            'created_at' => $stateData['created_at'] ?? null,
            'expires_at' => $stateData['expires_at'] ?? null,
        ];
    }

    /**
     * Update existing filter state
     */
    public function updateFilterState(string $filterId, array $filters): bool
    {
        $cacheKey = $this->getCacheKey($filterId);
        $existingData = Cache::get($cacheKey);

        if (!$existingData) {
            return false;
        }

        $existingData['filters'] = $this->serializeFilters($filters);
        Cache::put($cacheKey, $existingData, $this->ttl);

        return true;
    }

    /**
     * Delete filter state
     */
    public function deleteFilterState(string $filterId): bool
    {
        return Cache::forget($this->getCacheKey($filterId));
    }

    /**
     * Check if filter ID exists and is valid
     */
    public function isValidFilterId(string $filterId): bool
    {
        return Cache::has($this->getCacheKey($filterId));
    }

    /**
     * Extend TTL for existing filter state
     */
    public function extendFilterState(string $filterId, int $additionalSeconds = null): bool
    {
        $additionalSeconds = $additionalSeconds ?? $this->ttl;
        $cacheKey = $this->getCacheKey($filterId);
        $stateData = Cache::get($cacheKey);

        if (!$stateData) {
            return false;
        }

        $stateData['expires_at'] = now()->addSeconds($additionalSeconds)->toISOString();
        Cache::put($cacheKey, $stateData, $additionalSeconds);

        return true;
    }

    /**
     * Clean up expired filter states (can be called via scheduled task)
     */
    public function cleanupExpiredFilters(): int
    {
        // This is a basic implementation. In production, you might want to use
        // a more sophisticated approach with Redis SCAN or database-based tracking
        $cleaned = 0;

        // Note: This is not efficient for large numbers of keys
        // Consider implementing a separate tracking mechanism for production

        return $cleaned;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        return [
            'ttl' => $this->ttl,
            'cache_driver' => config('cache.default'),
            'prefix' => self::CACHE_PREFIX,
        ];
    }

    /**
     * Serialize filters for storage
     */
    protected function serializeFilters(array $filters): array
    {
        $serialized = [];

        foreach ($filters as $filter) {
            if ($filter instanceof Filter && $filter->isActive()) {
                $serialized[] = $filter->toArray();
            }
        }

        return $serialized;
    }

    /**
     * Deserialize filters from storage
     */
    protected function deserializeFilters(array $serializedFilters): array
    {
        $filters = [];

        foreach ($serializedFilters as $filterData) {
            if (!isset($filterData['type']) || !class_exists($filterData['type'])) {
                continue;
            }

            $filterClass = $filterData['type'];

            if (!is_subclass_of($filterClass, Filter::class)) {
                continue;
            }

            $filter = new $filterClass($filterData['name']);
            $filter->fromArray($filterData);

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * Generate cache key for filter ID
     */
    protected function getCacheKey(string $filterId): string
    {
        return self::CACHE_PREFIX . $filterId;
    }

    /**
     * Set custom TTL for filter caching
     */
    public function setTtl(int $ttl): static
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * Get current TTL for filter caching
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
