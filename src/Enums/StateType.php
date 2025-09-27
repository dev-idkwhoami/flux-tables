<?php

namespace Idkwhoami\FluxTables\Enums;

enum StateType: string
{
    case COLUMN_TOGGLES = 'column_toggles';
    case FILTERS = 'filters';
    case SORTING = 'sorting';
    case SEARCH = 'search';
    case PAGINATION = 'pagination';

    /**
     * Get all state type values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all state type names
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::COLUMN_TOGGLES => 'Column Visibility',
            self::FILTERS => 'Filter Values',
            self::SORTING => 'Sort Preferences',
            self::SEARCH => 'Search Queries',
            self::PAGINATION => 'Pagination Settings',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::COLUMN_TOGGLES => 'User preferences for which columns are visible or hidden',
            self::FILTERS => 'Applied filter values and criteria',
            self::SORTING => 'Current sort column and direction preferences',
            self::SEARCH => 'Search query terms and history',
            self::PAGINATION => 'Items per page and pagination preferences',
        };
    }

    /**
     * Check if this state type is typically persistent by default
     */
    public function isPersistentByDefault(): bool
    {
        return match($this) {
            self::COLUMN_TOGGLES, self::PAGINATION => true,
            self::FILTERS, self::SORTING, self::SEARCH => false,
        };
    }

    /**
     * Get default persistence driver for this state type
     */
    public function defaultPersistenceDriver(): string
    {
        return match($this) {
            self::COLUMN_TOGGLES, self::PAGINATION => 'session',
            self::FILTERS, self::SORTING, self::SEARCH => 'none',
        };
    }

    /**
     * Create from string value (case-insensitive)
     */
    public static function fromString(string $value): ?self
    {
        $value = strtolower($value);

        return match($value) {
            'column_toggles', 'columns', 'toggle' => self::COLUMN_TOGGLES,
            'filters', 'filter' => self::FILTERS,
            'sorting', 'sort' => self::SORTING,
            'search' => self::SEARCH,
            'pagination', 'paging', 'per_page' => self::PAGINATION,
            default => null,
        };
    }
}