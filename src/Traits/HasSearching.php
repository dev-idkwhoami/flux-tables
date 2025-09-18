<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasSearching
{
    public string $search = '';

    /**
     * Initialize search functionality from configuration
     */
    protected function initializeSearch(TableConfiguration $configuration): void
    {
        // Set default search query if specified in configuration
        $this->search = $configuration->getSearchDefaultQuery() ?? '';
    }

    /**
     * Handle search input updates
     */
    public function updatedSearch(): void
    {
        $this->resetPage();

        // Store search state if state service is available
        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setSearch($this->search);
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Clear the search input
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();

        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setSearch('');
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Check if search is enabled based on configuration
     */
    public function hasSearch(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getSearchEnabled() && $this->hasSearchableColumns();
    }

    /**
     * Check if there are searchable columns available
     */
    public function hasSearchableColumns(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        $columns = $configuration->getColumns();
        foreach ($columns as $column) {
            if ($column->isSearchable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all searchable columns
     */
    public function getSearchableColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $searchableColumns = [];
        foreach ($configuration->getColumns() as $column) {
            if ($column->isSearchable()) {
                $searchableColumns[] = $column;
            }
        }

        return $searchableColumns;
    }

    /**
     * Get search placeholder text
     */
    public function getSearchPlaceholder(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'flux-tables::search.placeholder';
        }

        return $configuration->getSearchPlaceholder() ?? 'flux-tables::search.placeholder';
    }

    /**
     * Check if search has active value
     */
    public function hasActiveSearch(): bool
    {
        return !empty(trim($this->search));
    }
}