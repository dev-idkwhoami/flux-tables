<?php

namespace Idkwhoami\FluxTables\Support;

use Idkwhoami\FluxTables\Enums\StateType;
use Idkwhoami\FluxTables\Services\StatePersistenceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Static utility class for managing table state persistence across multiple tables
 *
 * For single table operations, use StatePersistenceService directly.
 * This class provides convenience methods for bulk operations and global state management.
 */
class StatePersistenceManager
{
    /**
     * Delete all state persistence for a specific owner across all tables
     */
    public static function deleteAllForOwner(mixed $owner): void
    {
        $stateId = StatePersistenceService::generateStateId($owner);

        // Clear database data for this state ID across all tables
        static::clearDatabaseForStateId($stateId);

        // Clear all session data (since session is not scoped by state ID)
        static::clearAllSessions();
    }

    /**
     * Delete all state persistence for a specific state ID across all tables
     */
    public static function deleteAllForStateId(mixed $stateId): void
    {
        // Clear database data for this state ID across all tables
        static::clearDatabaseForStateId($stateId);

        // Clear all session data (since session is not scoped by state ID)
        static::clearAllSessions();
    }

    /**
     * Delete all state persistence for a specific table (delegates to StatePersistenceService)
     */
    public static function deleteAllForTable(string $tableId): void
    {
        $service = new StatePersistenceService($tableId);
        $service->deleteAllForTable();
    }

    /**
     * Delete specific state type for a state ID (delegates to StatePersistenceService)
     */
    public static function deleteStateType(mixed $stateId, string $tableId, StateType|string $stateType): void
    {
        $service = new StatePersistenceService($tableId);
        $service->delete(
            new \Idkwhoami\FluxTables\Configuration\Persistence('database'),
            $stateType
        );
    }

    /**
     * Delete multiple state types for a state ID
     */
    public static function deleteStateTypes(mixed $stateId, string $tableId, array $stateTypes): void
    {
        $service = new StatePersistenceService($tableId);
        foreach ($stateTypes as $stateType) {
            $service->delete(
                new \Idkwhoami\FluxTables\Configuration\Persistence('database'),
                $stateType
            );
        }
    }

    /**
     * Delete all persistent state types (keeps non-persistent ones)
     */
    public static function deletePersistentStates(mixed $stateId, string $tableId): void
    {
        // This method would need StateType enum to have an isPersistentByDefault method
        // For now, delete all common persistent state types
        $persistentTypes = [
            StateType::COLUMN_TOGGLES,
            StateType::FILTERS,
            StateType::SORTING,
            StateType::SEARCH,
            StateType::PAGINATION,
        ];

        static::deleteStateTypes($stateId, $tableId, $persistentTypes);
    }

    /**
     * Delete all state persistence everywhere (nuclear option)
     */
    public static function deleteAll(): void
    {
        // Clear all flux-tables session data
        static::clearAllSessions();

        // Clear all flux-tables database data
        static::clearAllDatabase();
    }

    /**
     * Delete expired database records across all tables
     */
    public static function cleanupExpired(string $connection = null): int
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        $query = DB::connection($connection)->table($table)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());

        $deletedCount = $query->count();
        $query->delete();

        return $deletedCount;
    }

    /**
     * Get statistics about stored state data across all tables
     */
    public static function getStatistics(string $connection = null): array
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        $stats = [
            'total_records' => 0,
            'expired_records' => 0,
            'state_types' => [],
            'unique_state_ids' => 0,
            'unique_table_ids' => 0,
        ];

        try {
            $query = DB::connection($connection)->table($table);

            $stats['total_records'] = $query->count();

            $stats['expired_records'] = $query->clone()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count();

            $stats['state_types'] = $query->clone()
                ->select('state_type')
                ->groupBy('state_type')
                ->pluck('state_type')
                ->toArray();

            $stats['unique_state_ids'] = $query->clone()
                ->distinct('state_id')
                ->count();

            $stats['unique_table_ids'] = $query->clone()
                ->distinct('table_id')
                ->count();

        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return $stats;
    }

    // Session management methods

    /**
     * Clear session data for specific state ID (limited scope)
     */
    protected static function clearSessionForStateId(mixed $stateId): void
    {
        // Session data is not typically scoped by state ID,
        // so we clear all flux-tables session data
        static::clearAllSessions();
    }

    /**
     * Clear session data for specific table
     */
    protected static function clearSessionForTable(string $tableId): void
    {
        $sessionKeys = static::getSessionKeysForTable($tableId);

        foreach ($sessionKeys as $key) {
            Session::forget($key);
        }
    }

    /**
     * Clear specific state type from session
     */
    protected static function clearSessionStateType(string $tableId, string $stateType): void
    {
        $key = "flux_tables.{$tableId}.{$stateType}";
        Session::forget($key);
    }

    /**
     * Clear all flux-tables session data
     */
    protected static function clearAllSessions(): void
    {
        $allSessionData = Session::all();

        foreach ($allSessionData as $key => $value) {
            if (str_starts_with($key, 'flux_tables.')) {
                Session::forget($key);
            }
        }
    }

    /**
     * Get session keys for a specific table
     */
    protected static function getSessionKeysForTable(string $tableId): array
    {
        $prefix = "flux_tables.{$tableId}.";
        $keys = [];

        foreach (Session::all() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    // Database management methods

    /**
     * Clear database data for specific state ID
     */
    protected static function clearDatabaseForStateId(mixed $stateId, string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)
            ->where('state_id', $stateId)
            ->delete();
    }

    /**
     * Clear database data for specific table
     */
    protected static function clearDatabaseForTable(string $tableId, string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)
            ->where('table_id', $tableId)
            ->delete();
    }

    /**
     * Clear specific state type from database
     */
    protected static function clearDatabaseStateType(mixed $stateId, string $tableId, string $stateType, string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)
            ->where('state_id', $stateId)
            ->where('table_id', $tableId)
            ->where('state_type', $stateType)
            ->delete();
    }

    /**
     * Clear all database data
     */
    protected static function clearAllDatabase(string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)->delete();
    }

    // Utility methods

    /**
     * Generate a composite state ID from an owner (following Laravel Pennant pattern)
     */
    public static function generateStateId(mixed $owner): string
    {
        if (is_object($owner) && method_exists($owner, 'getKey')) {
            // Eloquent model - use class name and primary key
            $modelClass = get_class($owner);
            $modelId = $owner->getKey();
            return "{$modelClass}|{$modelId}";
        }

        if (is_string($owner) || is_numeric($owner)) {
            // Simple ID - use session-based fallback
            return "session|{$owner}";
        }

        // Fallback: use authenticated user ID or session ID
        $fallbackOwner = auth()->user();
        if ($fallbackOwner && method_exists($fallbackOwner, 'getKey')) {
            $modelClass = get_class($fallbackOwner);
            $modelId = $fallbackOwner->getKey();
            return "{$modelClass}|{$modelId}";
        }

        return "session|" . session()->getId();
    }

    /**
     * Check if state exists for given parameters
     */
    public static function hasState(mixed $stateId, string $tableId, StateType|string $stateType, string $driver = 'session'): bool
    {
        $stateTypeValue = $stateType instanceof StateType ? $stateType->value : $stateType;

        if ($driver === 'session') {
            $key = "flux_tables.{$tableId}.{$stateTypeValue}";
            return Session::has($key);
        }

        if ($driver === 'database') {
            $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

            return DB::table($table)
                ->where('state_id', $stateId)
                ->where('table_id', $tableId)
                ->where('state_type', $stateTypeValue)
                ->exists();
        }

        return false;
    }

    /**
     * Get all state data for debugging
     */
    public static function debugState(mixed $stateId = null, string $tableId = null): array
    {
        $debug = [
            'session' => [],
            'database' => [],
        ];

        // Get session data
        foreach (Session::all() as $key => $value) {
            if (str_starts_with($key, 'flux_tables.')) {
                if (!$tableId || str_contains($key, "flux_tables.{$tableId}.")) {
                    $debug['session'][$key] = $value;
                }
            }
        }

        // Get database data
        try {
            $table = config('flux-tables.persistence.database.table', 'table_state_preferences');
            $query = DB::table($table);

            if ($stateId) {
                $query->where('state_id', $stateId);
            }

            if ($tableId) {
                $query->where('table_id', $tableId);
            }

            $debug['database'] = $query->get()->toArray();
        } catch (\Exception $e) {
            $debug['database'] = ['error' => $e->getMessage()];
        }

        return $debug;
    }
}