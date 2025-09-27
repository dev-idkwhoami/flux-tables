<?php

namespace Idkwhoami\FluxTables\Facades;

use Idkwhoami\FluxTables\Services\TableStateService;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing table state management functionality
 *
 * Provides easy access to table state operations like filters, sorting, search, and column toggles.
 *
 * @method static \Idkwhoami\FluxTables\Services\TableStateService forTable(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService make(string $tableId)
 * @method static mixed getFilterValue(string $tableId, string $filterName)
 * @method static array getFilterValues(string $tableId)
 * @method static bool hasActiveFilters(string $tableId)
 * @method static array getActiveFilters(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService setFilterValue(string $tableId, string $filterName, mixed $value)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService resetFilter(string $tableId, string $filterName)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService resetAllFilters(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService setSort(string $tableId, string $column, string $direction = 'asc')
 * @method static string|null getSortColumn(string $tableId)
 * @method static string getSortDirection(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService setSearch(string $tableId, string $query)
 * @method static string getSearch(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService setPerPage(string $tableId, int $perPage)
 * @method static int getPerPage(string $tableId)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService toggleColumn(string $tableId, string $columnName)
 * @method static array getToggledColumns(string $tableId)
 * @method static bool isColumnToggled(string $tableId, string $columnName)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService showColumn(string $tableId, string $columnName)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService hideColumn(string $tableId, string $columnName)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService resetColumnVisibility(string $tableId, array $defaultToggledColumns = null)
 * @method static \Idkwhoami\FluxTables\Services\TableStateService reset(string $tableId)
 * @method static array toArray(string $tableId)
 *
 * @see \Idkwhoami\FluxTables\Services\TableStateService
 */
class TableStateManager extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return 'flux-tables.state-service';
    }
}