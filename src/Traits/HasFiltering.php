<?php

namespace Idkwhoami\FluxTables\Traits;

trait HasFiltering
{
    public array $filterValues = [];
    public bool $useFilterModal = true;

    /**
     * Listen for filter application from master filter component
     */
    protected function getListeners(): array
    {
        return array_merge(parent::getListeners() ?? [], [
            'filtersApplied' => 'handleFiltersApplied',
        ]);
    }

    /**
     * Handle filters applied from master filter component
     */
    public function handleFiltersApplied(array $filters): void
    {
        $this->filterValues = $filters;
        $this->resetPage();

        $stateService = $this->getStateServiceIfAvailable();
        if ($stateService) {
            // Update state service with new filter values
            foreach ($filters as $name => $value) {
                $stateService->setFilterValue($name, $value);
            }

            // Remove filters that are no longer present
            $currentFilters = $stateService->getFilterValues();
            foreach ($currentFilters as $name => $value) {
                if (!isset($filters[$name])) {
                    $stateService->resetFilter($name);
                }
            }
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Handle updates to filterValues array (legacy support)
     */
    public function updatedFilterValues($value, $name): void
    {
        // Only handle this if not using master filter component
        if ($this->useFilterModal) {
            return;
        }

        $this->resetPage();

        $stateService = $this->getStateServiceIfAvailable();
        if ($stateService) {
            if (empty($value)) {
                $stateService->resetFilter($name);
            } else {
                $stateService->setFilterValue($name, $value);
            }
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }
    /**
     * Update a filter value
     */
    public function updateFilter(string $filterName, mixed $value): void
    {
        $stateService = $this->getStateServiceIfAvailable();
        if (!$stateService) {
            return;
        }

        if (empty($value)) {
            $stateService->resetFilter($filterName);
        } else {
            $stateService->setFilterValue($filterName, $value);
        }

        $this->resetPage();

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Reset a specific filter
     */
    public function resetFilter(string $filterName): void
    {
        $stateService = $this->getStateServiceIfAvailable();
        if (!$stateService) {
            return;
        }

        $stateService->resetFilter($filterName);
        $this->resetPage();

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Reset all filters
     */
    public function resetAllFilters(): void
    {
        $stateService = $this->getStateServiceIfAvailable();
        if (!$stateService) {
            return;
        }

        $stateService->resetAllFilters();
        $this->resetPage();

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Check if filtering is enabled based on configuration
     */
    public function filtersEnabled(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->hasFilters();
    }

    /**
     * Backward compatibility alias for filtersEnabled()
     * @deprecated Use filtersEnabled() instead
     */
    public function hasFiltering(): bool
    {
        return $this->filtersEnabled();
    }

    /**
     * Get all available filters from configuration
     */
    public function getFilters(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getFilters();
    }

    /**
     * Check if there are active filters
     */
    public function hasActiveFilters(): bool
    {
        $stateService = $this->getStateServiceIfAvailable();
        if (!$stateService) {
            return false;
        }

        return $stateService->hasActiveFilters();
    }

    /**
     * Get active filter values
     */
    public function getActiveFilters(): array
    {
        $stateService = $this->getStateServiceIfAvailable();
        if (!$stateService) {
            return [];
        }

        return $stateService->getFilterValues();
    }

    /**
     * Get filter position (sidebar, header, modal, etc.)
     */
    public function getFilterPosition(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'sidebar';
        }

        return $configuration->getFilterPosition();
    }

    /**
     * Check if filters are collapsible
     */
    public function areFiltersCollapsible(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getFilterCollapsible();
    }

    /**
     * Check if filters should be expanded by default
     */
    public function areFiltersDefaultExpanded(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getFilterDefaultExpanded();
    }

    /**
     * Check if clear all button should be shown
     */
    public function showClearAllFilters(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getFilterShowClearAll();
    }

    /**
     * Check if active filter count should be shown
     */
    public function showActiveFilterCount(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getFilterShowActiveCount();
    }

    /**
     * Get filter texts for localization
     */
    public function getFilterTexts(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'title' => 'flux-tables::filters.title',
                'clear_all' => 'flux-tables::filters.clear_all',
                'apply' => 'flux-tables::filters.apply',
                'reset' => 'flux-tables::filters.reset',
                'done' => 'flux-tables::filters.done',
                'cancel' => 'flux-tables::filters.cancel',
            ];
        }

        return $configuration->getFilterTexts();
    }

    /**
     * Get filter modal name for Livewire wire:model
     */
    public function getFilterModalName(): string
    {
        return "flux-tables::table:{$this->tableId}:filter:modal";
    }

    /**
     * Get default filter values
     */
    public function getDefaultFilterValues(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getFilterDefaultValues();
    }

    /**
     * Check if filters should persist between sessions
     */
    public function shouldPersistFilters(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getFilterPersistFilters();
    }

    /**
     * Get active filter badges for display
     */
    public function getActiveFilterBadges(): array
    {
        if (!$this->hasActiveFilters()) {
            return [];
        }

        $configuration = $this->getConfiguration();
        $filters = $configuration->getFilters();
        $badges = [];

        foreach ($filters as $filter) {
            if ($filter->isActive()) {
                $badges[$filter->getName()] = [
                    'label' => $filter->getLabel(),
                    'description' => $filter->getValueDescription(),
                    'detailed_description' => $filter->getDescription() ?? $filter->getValueDescription(),
                ];
            }
        }

        return $badges;
    }


    /**
     * Get visible column names for filter visibility checking
     */
    public function getVisibleColumnNames(): array
    {
        if (method_exists($this, 'getVisibleColumns')) {
            return collect($this->getVisibleColumns())
                ->map(fn ($column) => method_exists($column, 'getName') ? $column->getName() : (string) $column)
                ->toArray();
        }

        // Fallback: get all column names if visibility method not available
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getColumns()
            ->map(fn ($column) => $column->getName())
            ->toArray();
    }

    /**
     * Edit a specific filter (placeholder for future functionality)
     */
    public function editFilter(string $filterName): void
    {
        // For now, this could open a modal or redirect to filter editing
        // This is a placeholder for future implementation
    }


    /**
     * Search relationship options for RelationshipFilter
     */
    public function searchRelationshipOptions(string $filterName, string $query): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $filters = $configuration->getFilters();

        foreach ($filters as $filter) {
            if ($filter->getName() === $filterName && method_exists($filter, 'search')) {
                return $filter->search($query)->toArray();
            }
        }

        return [];
    }

    /**
     * Helper method to get state service if available, with caching to reduce method_exists calls
     *
     * @return \Idkwhoami\FluxTables\Services\TableStateService|null
     */
    protected function getStateServiceIfAvailable(): ?\Idkwhoami\FluxTables\Services\TableStateService
    {
        return method_exists($this, 'getStateService') ? $this->getStateService() : null;
    }
}
