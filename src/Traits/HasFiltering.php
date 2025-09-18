<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasFiltering
{
    public array $filterValues = [];

    /**
     * Handle updates to filterValues array
     */
    public function updatedFilterValues($value, $name): void
    {
        $this->resetPage();

        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
    public function hasFiltering(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getFilterEnabled() && $configuration->hasFilters();
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
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
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
}