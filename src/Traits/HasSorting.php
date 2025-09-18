<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasSorting
{
    public string $sortColumn = '';
    public string $sortDirection = 'asc';

    /**
     * Initialize sorting functionality from configuration
     */
    protected function initializeSorting(TableConfiguration $configuration): void
    {
        $defaultSort = $configuration->getSortingDefaultSort();
        if ($defaultSort) {
            $this->sortColumn = $defaultSort['column'];
            $this->sortDirection = $defaultSort['direction'];

            // Store in state service if available
            if (method_exists($this, 'getStateService') && $this->getStateService()) {
                $this->getStateService()->setSort($this->sortColumn, $this->sortDirection);
            }
        }
    }

    /**
     * Sort by a specific column
     */
    public function sortBy(string $column): void
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return;
        }

        // Find the column configuration to check if it's sortable
        $columnConfig = collect($configuration->getColumns())
            ->first(function($col) use ($column) {
                return $col->getName() === $column;
            });

        // Return early if column is not sortable
        if (!$columnConfig || !$columnConfig->isSortable()) {
            return;
        }

        // Toggle direction if same column, otherwise set to ascending
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        // Store sort state if state service is available
        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setSort($this->sortColumn, $this->sortDirection);
        }

        $this->resetPage();

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Clear all sorting
     */
    public function clearSort(): void
    {
        $this->sortColumn = '';
        $this->sortDirection = 'asc';

        if (method_exists($this, 'getStateService') && $this->getStateService()) {
            $this->getStateService()->setSort('', 'asc');
        }

        $this->resetPage();

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Check if sorting is enabled based on configuration
     */
    public function hasSorting(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $this->hasSortableColumns();
    }

    /**
     * Check if there are sortable columns available
     */
    public function hasSortableColumns(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        $columns = $configuration->getColumns();
        foreach ($columns as $column) {
            if ($column->isSortable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all sortable columns
     */
    public function getSortableColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $sortableColumns = [];
        foreach ($configuration->getColumns() as $column) {
            if ($column->isSortable()) {
                $sortableColumns[] = $column;
            }
        }

        return $sortableColumns;
    }

    /**
     * Check if a specific column is currently being sorted
     */
    public function isSortedBy(string $column): bool
    {
        return $this->sortColumn === $column;
    }

    /**
     * Get the sort direction for a specific column
     */
    public function getSortDirection(string $column): ?string
    {
        if ($this->isSortedBy($column)) {
            return $this->sortDirection;
        }

        return null;
    }

    /**
     * Check if multi-sort is enabled
     */
    public function allowsMultiSort(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getSortingMultiSort();
    }

    /**
     * Check if there's an active sort
     */
    public function hasActiveSort(): bool
    {
        return !empty($this->sortColumn);
    }

    /**
     * Get current sort information as array
     */
    public function getCurrentSort(): ?array
    {
        if (empty($this->sortColumn)) {
            return null;
        }

        return [
            'column' => $this->sortColumn,
            'direction' => $this->sortDirection,
        ];
    }
}