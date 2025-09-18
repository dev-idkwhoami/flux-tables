<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasColumnToggling
{
    /**
     * Toggle visibility of a specific column
     */
    public function toggleColumn(string $columnName): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        $stateService->toggleColumn($columnName);

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Show a specific column
     */
    public function showColumn(string $columnName): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        $stateService->showColumn($columnName);
    }

    /**
     * Hide a specific column
     */
    public function hideColumn(string $columnName): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        $stateService->hideColumn($columnName);
    }

    /**
     * Show all columns
     */
    public function showAllColumns(): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        foreach ($this->getToggleableColumns() as $column) {
            $stateService->showColumn($column->getName());
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Hide all toggleable columns
     */
    public function hideAllColumns(): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        foreach ($this->getToggleableColumns() as $column) {
            $stateService->hideColumn($column->getName());
        }

        // Clear computed properties to force refresh
        unset($this->models);
    }

    /**
     * Reset columns to their default visibility state
     */
    public function resetColumnVisibility(): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        $stateService->resetColumnVisibility();
    }

    /**
     * Check if column toggling is enabled
     */
    public function hasColumnToggling(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getColumnShowToggleButton() && !empty($this->getToggleableColumns());
    }

    /**
     * Get all toggleable columns
     */
    public function getToggleableColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $toggleableColumns = [];
        foreach ($configuration->getColumns() as $column) {
            if ($column->isToggleable()) {
                $toggleableColumns[] = $column;
            }
        }

        return $toggleableColumns;
    }

    /**
     * Get currently toggled (hidden) columns
     */
    public function getToggledColumns(): array
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return [];
        }

        return $stateService->getToggledColumns();
    }

    /**
     * Check if a specific column is currently visible
     */
    public function isColumnVisible(string $columnName): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        // Find the column configuration using a closure since name is a protected property
        $column = collect($configuration->getColumns())
            ->first(function($column) use ($columnName) {
                return $column->getName() === $columnName;
            });

        if (!$column) {
            return false;
        }

        // Check if column is toggled off by user
        $toggledColumns = $this->getToggledColumns();
        if (in_array($columnName, $toggledColumns)) {
            return false;
        }

        // Return the column's base visibility
        return $column->isVisible();
    }

    /**
     * Check if toggle button should be shown
     */
    public function showToggleButton(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getColumnShowToggleButton();
    }

    /**
     * Get toggle button position
     */
    public function getToggleButtonPosition(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'header';
        }

        return $configuration->getColumnToggleButtonPosition();
    }

    /**
     * Check if column toggle state should be remembered
     */
    public function shouldRememberToggleState(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getColumnRememberToggleState();
    }

    /**
     * Get column texts for localization
     */
    public function getColumnTexts(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'toggle_columns' => 'flux-tables::columns.toggle_columns',
                'show_all' => 'flux-tables::columns.show_all',
                'hide_all' => 'flux-tables::columns.hide_all',
                'visible_columns' => 'flux-tables::columns.visible_columns',
                'hidden_columns' => 'flux-tables::columns.hidden_columns',
                'column_visibility' => 'flux-tables::columns.column_visibility',
            ];
        }

        return $configuration->getColumnTexts();
    }

    /**
     * Get columns that are currently visible
     */
    public function getVisibleColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $visibleColumns = [];
        foreach ($configuration->getColumns() as $column) {
            if ($this->isColumnVisible($column->getName())) {
                $visibleColumns[] = $column;
            }
        }

        return $visibleColumns;
    }

    /**
     * Get columns that are currently hidden
     */
    public function getHiddenColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $hiddenColumns = [];
        foreach ($configuration->getColumns() as $column) {
            if (!$this->isColumnVisible($column->getName())) {
                $hiddenColumns[] = $column;
            }
        }

        return $hiddenColumns;
    }

    /**
     * Get default column order
     */
    public function getDefaultColumnOrder(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getColumnOrder();
    }
}