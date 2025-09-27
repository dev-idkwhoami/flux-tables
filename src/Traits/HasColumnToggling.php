<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;
use Illuminate\View\Component;

/**
 * @mixin Component
 */
trait HasColumnToggling
{
    public int $columnToggleVersion = 0;

    public function bootedHasColumnToggling(): void
    {
        // Default initialization will happen later in getStateService() after persistence is loaded
    }

    /**
     * Initialize default toggled columns only if no persisted state exists
     */
    protected function initializeDefaultToggledColumns(): void
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return;
        }

        // Check if we have any persisted toggle state for this table
        $hasPersistedState = $this->hasPersistedColumnToggleState();

        // Only initialize defaults if no persisted state exists
        if (!$hasPersistedState) {
            $defaultToggledColumns = $this->getDefaultToggledColumns();
            if (!empty($defaultToggledColumns)) {
                $stateService->resetColumnVisibility($defaultToggledColumns);
            }
        }
    }

    /**
     * Check if there's persisted column toggle state for this table
     */
    protected function hasPersistedColumnToggleState(): bool
    {
        $stateService = method_exists($this, 'getStateService') ? $this->getStateService() : null;
        if (!$stateService) {
            return false;
        }

        // Get the persistence configuration for column toggles
        $persistenceConfig = $stateService->getPersistenceConfig(\Idkwhoami\FluxTables\Enums\StateType::COLUMN_TOGGLES);
        if (!$persistenceConfig || $persistenceConfig->getDriver() === 'none') {
            return false;
        }

        // Check if persisted state exists
        $persistenceService = new \Idkwhoami\FluxTables\Services\StatePersistenceService($this->tableId ?? '');
        $persistedData = $persistenceService->load($persistenceConfig, \Idkwhoami\FluxTables\Enums\StateType::COLUMN_TOGGLES, null);

        return $persistedData !== null;
    }

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

        // Increment version to trigger reactivity
        $this->columnToggleVersion++;
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

        // Get default toggled columns (columns that should be hidden by default)
        $defaultToggledColumns = $this->getDefaultToggledColumns();

        $stateService->resetColumnVisibility($defaultToggledColumns);

        // Clear cached services and computed properties to force refresh
        if (property_exists($this, 'stateService')) {
            $this->stateService = null;
        }
        unset($this->models);

        // Increment version to trigger reactivity
        $this->columnToggleVersion++;
    }

    /**
     * Get columns that should be toggled (hidden) by default
     */
    public function getDefaultToggledColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $defaultToggledColumns = [];
        foreach ($configuration->getColumns() as $column) {
            // Only include columns that are visible and toggleable but set to be toggled by default
            if ($column->isVisible() && $column->isToggleable() && $column->isToggled()) {
                $defaultToggledColumns[] = $column->getName();
            }
        }

        return $defaultToggledColumns;
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
     * Check if all toggleable columns are currently toggled
     */
    public function hasAllToggleableColumnsToggled(): bool
    {
        $toggleableColumns = $this->getToggleableColumns();
        $toggledColumns = $this->getToggledColumns();

        if (empty($toggleableColumns)) {
            return false;
        }

        $toggleableColumnNames = array_map(fn($column) => $column->getName(), $toggleableColumns);
        return empty(array_diff($toggleableColumnNames, $toggledColumns));
    }

    /**
     * Get all toggleable columns (only includes visible columns)
     */
    public function getToggleableColumns(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        $toggleableColumns = [];
        foreach ($configuration->getColumns() as $column) {
            // Only include columns that are both visible and toggleable
            if ($column->isToggleable() && $column->isVisible()) {
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

        // First check if column is visible (handles permissions, business logic)
        // Note: visibility is evaluated at table level, not per record
        if (!$column->isVisible()) {
            return false;
        }

        // Then check if column is toggled off by user
        $toggledColumns = $this->getToggledColumns();
        if (in_array($columnName, $toggledColumns)) {
            return false;
        }

        return true;
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
