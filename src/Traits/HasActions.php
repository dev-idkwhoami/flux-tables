<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

trait HasActions
{
    public array $selectedRows = [];

    /**
     * Toggle row selection
     */
    public function toggleRowSelection($rowId): void
    {
        if (in_array($rowId, $this->selectedRows)) {
            $this->selectedRows = array_filter($this->selectedRows, fn($id) => $id !== $rowId);
        } else {
            $this->selectedRows[] = $rowId;
        }
    }

    /**
     * Select all rows on current page
     */
    public function selectAllRows(array $rowIds): void
    {
        $this->selectedRows = array_unique(array_merge($this->selectedRows, $rowIds));
    }

    /**
     * Deselect all rows
     */
    public function deselectAllRows(): void
    {
        $this->selectedRows = [];
    }

    /**
     * Check if a specific row is selected
     */
    public function isRowSelected($rowId): bool
    {
        return in_array($rowId, $this->selectedRows);
    }

    /**
     * Get count of selected rows
     */
    public function getSelectedRowCount(): int
    {
        return count($this->selectedRows);
    }

    /**
     * Check if any rows are selected
     */
    public function hasSelectedRows(): bool
    {
        return !empty($this->selectedRows);
    }

    /**
     * Execute a bulk action on selected rows
     */
    public function executeBulkAction(string $actionName): void
    {
        if (empty($this->selectedRows)) {
            return;
        }

        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return;
        }

        $bulkActions = $configuration->getActionBulkActions();
        $action = $bulkActions[$actionName] ?? null;

        if (!$action || !is_callable($action)) {
            return;
        }

        // Execute the bulk action with selected row IDs
        $action($this->selectedRows, $this);

        // Clear selection after action
        $this->selectedRows = [];

        // Refresh the component
        $this->dispatch('$refresh');
    }

    /**
     * Check if actions are enabled based on configuration
     */
    public function hasActions(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getActionEnabled() && $configuration->hasActions();
    }

    /**
     * Get all available row actions from configuration
     */
    public function getRowActions(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getActions();
    }

    /**
     * Get all available bulk actions from configuration
     */
    public function getBulkActions(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [];
        }

        return $configuration->getActionBulkActions();
    }

    /**
     * Check if bulk actions should be shown
     */
    public function showBulkActions(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getActionShowBulkActions() && !empty($configuration->getActionBulkActions());
    }

    /**
     * Get action position (left, right, etc.)
     */
    public function getActionPosition(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'right';
        }

        return $configuration->getActionPosition();
    }

    /**
     * Check if actions column should be sticky
     */
    public function areActionsSticky(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return false;
        }

        return $configuration->getActionSticky();
    }

    /**
     * Check if destructive actions should require confirmation
     */
    public function shouldConfirmDestructiveActions(): bool
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return true;
        }

        return $configuration->getActionConfirmDestructive();
    }

    /**
     * Get action texts for localization
     */
    public function getActionTexts(): array
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return [
                'actions' => 'flux-tables::actions.actions',
                'edit' => 'flux-tables::actions.edit',
                'delete' => 'flux-tables::actions.delete',
                'view' => 'flux-tables::actions.view',
                'bulk_actions' => 'flux-tables::actions.bulk_actions',
                'select_all' => 'flux-tables::actions.select_all',
                'select_none' => 'flux-tables::actions.select_none',
                'selected_items' => 'flux-tables::actions.selected_items',
            ];
        }

        return $configuration->getActionTexts();
    }

    /**
     * Get confirmation modal variant for destructive actions
     */
    public function getConfirmationModalVariant(): string
    {
        $configuration = $this->getConfiguration();
        if (!$configuration) {
            return 'danger';
        }

        return $configuration->getActionConfirmationModalVariant();
    }

    /**
     * Check if all rows on current page are selected
     */
    public function areAllRowsSelected(array $currentPageRowIds): bool
    {
        if (empty($currentPageRowIds)) {
            return false;
        }

        return empty(array_diff($currentPageRowIds, $this->selectedRows));
    }

    /**
     * Toggle selection for all rows on current page
     */
    public function toggleAllRowsSelection(array $currentPageRowIds): void
    {
        if ($this->areAllRowsSelected($currentPageRowIds)) {
            // Deselect all rows from current page
            $this->selectedRows = array_diff($this->selectedRows, $currentPageRowIds);
        } else {
            // Select all rows from current page
            $this->selectAllRows($currentPageRowIds);
        }
    }
}