<?php

namespace Idkwhoami\FluxTables\Contracts;

interface TablePreferencePersistence
{
    /**
     * Get the currently toggled (hidden) columns for a table
     */
    public function getToggledColumns(string $tableId): array;

    /**
     * Set the toggled (hidden) columns for a table
     */
    public function setToggledColumns(string $tableId, array $columns): void;

    /**
     * Reset toggled columns to default state for a table
     */
    public function resetToggledColumns(string $tableId, array $defaultColumns = []): void;

    /**
     * Clear all preferences for a table
     */
    public function clearPreferences(string $tableId): void;
}