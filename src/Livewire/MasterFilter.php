<?php

namespace Idkwhoami\FluxTables\Livewire;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Idkwhoami\FluxTables\Configuration\TableConfiguration;

class MasterFilter extends Component
{
    #[Modelable]
    public array $filterValues = [];

    public array $pendingFilterValues = [];
    public string $tableId;
    public TableConfiguration $configuration;
    public array $visibleColumnNames = [];

    public function mount(string $tableId, TableConfiguration $configuration, array $filterValues = [], array $visibleColumnNames = []): void
    {
        $this->tableId = $tableId;
        $this->configuration = $configuration;
        $this->visibleColumnNames = $visibleColumnNames;

        // Only keep filter values for visible columns
        $this->filterValues = $this->filterVisibleOnly($filterValues);
        $this->pendingFilterValues = $this->filterValues;
    }

    /**
     * Update visible column names when columns are toggled
     */
    public function updateVisibleColumns(array $visibleColumnNames): void
    {
        $this->visibleColumnNames = $visibleColumnNames;

        // Remove any applied filters that are no longer visible
        $this->filterValues = $this->filterVisibleOnly($this->filterValues);
        $this->pendingFilterValues = $this->filterVisibleOnly($this->pendingFilterValues);

        // Dispatch updated filters to parent
        $this->dispatch('filtersApplied', filters: $this->filterValues);
    }

    /**
     * Filter out values for columns that aren't visible
     */
    protected function filterVisibleOnly(array $filterValues): array
    {
        $visibleFilters = $this->getVisibleFilters();
        $visibleFilterNames = array_map(fn ($filter) => $filter->getName(), $visibleFilters);

        return array_intersect_key($filterValues, array_flip($visibleFilterNames));
    }

    /**
     * Update a pending filter value without applying immediately
     */
    public function updatePendingFilter(string $filterName, mixed $value): void
    {
        // Only allow updates for visible filters
        if (!$this->isFilterVisible($filterName)) {
            return;
        }

        if (empty($value)) {
            unset($this->pendingFilterValues[$filterName]);
        } else {
            $this->pendingFilterValues[$filterName] = $value;
        }
    }

    /**
     * Apply all pending filter changes (only for visible filters)
     */
    public function applyFilters(): void
    {
        // Only apply filters that are currently visible
        $this->filterValues = $this->filterVisibleOnly($this->pendingFilterValues);
        $this->dispatch('filtersApplied', filters: $this->filterValues);
    }

    /**
     * Reset all filters to empty state
     */
    public function resetAllFilters(): void
    {
        $this->pendingFilterValues = [];
        $this->filterValues = [];
        $this->dispatch('filtersApplied', filters: $this->filterValues);
    }

    /**
     * Reset a specific filter
     */
    public function resetFilter(string $filterName): void
    {
        unset($this->pendingFilterValues[$filterName]);
        unset($this->filterValues[$filterName]);
        $this->dispatch('filtersApplied', filters: $this->filterValues);
    }

    /**
     * Cancel pending changes and revert to applied filters
     */
    public function cancelChanges(): void
    {
        $this->pendingFilterValues = $this->filterValues;
    }

    /**
     * Get all available filters from configuration, filtered by column visibility
     */
    public function getVisibleFilters(): array
    {
        $filters = $this->configuration->getFilters();

        return array_filter($filters, function ($filter) {
            return $this->isFilterVisible($filter->getName());
        });
    }

    /**
     * Get the Livewire component name for a filter
     */
    public function getFilterComponentName(object $filter): string
    {
        // Check if filter has a custom component defined
        if (method_exists($filter, 'getComponentName')) {
            return $filter->getComponentName();
        }

        // Map filter classes to component names
        $componentMap = [
            'Idkwhoami\FluxTables\Filters\DateRangeFilter' => 'flux-tables::date-range-filter',
            'Idkwhoami\FluxTables\Filters\RelationshipFilter' => 'flux-tables::searchable-select-filter',
            'Idkwhoami\FluxTables\Filters\EnumFilter' => 'flux-tables::enum-filter',
            'Idkwhoami\FluxTables\Filters\NumberFilter' => 'flux-tables::number-filter',
            'Idkwhoami\FluxTables\Filters\DateFilter' => 'flux-tables::date-filter',
        ];

        $filterClass = get_class($filter);
        return $componentMap[$filterClass] ?? 'flux-tables::basic-filter';
    }

    /**
     * Get component parameters for a filter
     */
    public function getFilterComponentParams(object $filter): array
    {
        $baseParams = [
            'filterName' => $filter->getName(),
            'masterFilterId' => $this->getId(),
            'value' => $this->pendingFilterValues[$filter->getName()] ?? null,
            'label' => $filter->getLabel(),
            'description' => $filter->getDescription(),
        ];

        // Add filter-specific configuration
        if (method_exists($filter, 'getComponentConfig')) {
            $baseParams['config'] = $filter->getComponentConfig();
        }

        // For basic filter component, pass the filter object
        if ($this->getFilterComponentName($filter) === 'flux-tables::basic-filter') {
            $baseParams['filter'] = $filter;
        }

        return $baseParams;
    }

    /**
     * Check if a filter should be visible based on its associated column
     */
    protected function isFilterVisible(string $filterName): bool
    {
        $filters = $this->configuration->getFilters();

        foreach ($filters as $filter) {
            if ($filter->getName() === $filterName) {
                $filterColumn = $filter->getColumn();

                // Handle relationship columns (e.g., "user.name")
                if (str_contains($filterColumn, '.')) {
                    $baseColumn = explode('.', $filterColumn)[0];
                    return in_array($baseColumn, $this->visibleColumnNames);
                }

                return in_array($filterColumn, $this->visibleColumnNames);
            }
        }

        return false;
    }

    /**
     * Check if there are pending changes
     */
    public function hasPendingChanges(): bool
    {
        return $this->pendingFilterValues !== $this->filterValues;
    }

    /**
     * Get count of active filters (only visible ones)
     */
    public function getActiveFilterCount(): int
    {
        return count(array_filter($this->filterValues));
    }

    /**
     * Get count of pending filters (only visible ones)
     */
    public function getPendingFilterCount(): int
    {
        return count(array_filter($this->pendingFilterValues));
    }

    /**
     * Get filter values that should be applied to the query (only visible filters)
     */
    public function getApplicableFilterValues(): array
    {
        return $this->filterVisibleOnly($this->filterValues);
    }

    public function render()
    {
        return view('flux-tables::livewire.master-filter', [
            'filters' => $this->getVisibleFilters(),
            'hasChanges' => $this->hasPendingChanges(),
            'activeCount' => $this->getActiveFilterCount(),
            'pendingCount' => $this->getPendingFilterCount(),
        ]);
    }
}
