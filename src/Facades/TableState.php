<?php

namespace Idkwhoami\FluxTables\Facades;

use Illuminate\Support\Facades\Facade;
use Idkwhoami\FluxTables\Support\StatePersistenceManager;

/**
 * @method static void deleteAllForOwner(mixed $owner)
 * @method static void deleteAllForStateId(mixed $stateId)
 * @method static void deleteAllForTable(string $tableId)
 * @method static void deleteStateType(mixed $stateId, string $tableId, \Idkwhoami\FluxTables\Enums\StateType|string $stateType)
 * @method static void deleteStateTypes(mixed $stateId, string $tableId, array $stateTypes)
 * @method static void deletePersistentStates(mixed $stateId, string $tableId)
 * @method static void deleteAll()
 * @method static int cleanupExpired(string $connection = null)
 * @method static array getStatistics(string $connection = null)
 * @method static string generateStateId(mixed $owner)
 * @method static bool hasState(mixed $stateId, string $tableId, \Idkwhoami\FluxTables\Enums\StateType|string $stateType, string $driver = 'session')
 * @method static array debugState(mixed $stateId = null, string $tableId = null)
 *
 * @see \Idkwhoami\FluxTables\Support\StatePersistenceManager
 */
class TableState extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return StatePersistenceManager::class;
    }
}