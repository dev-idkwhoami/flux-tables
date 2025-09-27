<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Configuration\Persistence;
use Idkwhoami\FluxTables\Enums\StateType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class StatePersistenceService
{
    public function __construct(
        protected readonly string $tableId
    ) {
    }

    /**
     * Save state data using the provided persistence configuration
     */
    public function save(Persistence $config, StateType|string $stateType, mixed $data): void
    {
        if ($config->getDriver() === 'none') {
            return;
        }

        $stateTypeString = is_string($stateType) ? $stateType : $stateType->value;

        switch ($config->getDriver()) {
            case 'session':
                $this->saveToSession($config, $stateTypeString, $data);
                break;
            case 'database':
                $this->saveToDatabase($config, $stateTypeString, $data);
                break;
        }
    }

    /**
     * Load state data using the provided persistence configuration
     */
    public function load(Persistence $config, StateType|string $stateType, mixed $default = null): mixed
    {
        if ($config->getDriver() === 'none') {
            return $default;
        }

        $stateTypeString = is_string($stateType) ? $stateType : $stateType->value;

        return match ($config->getDriver()) {
            'session' => $this->loadFromSession($config, $stateTypeString, $default),
            'database' => $this->loadFromDatabase($config, $stateTypeString, $default),
            default => $default,
        };
    }

    /**
     * Delete state data using the provided persistence configuration
     */
    public function delete(Persistence $config, StateType|string $stateType): void
    {
        if ($config->getDriver() === 'none') {
            return;
        }

        $stateTypeString = is_string($stateType) ? $stateType : $stateType->value;

        switch ($config->getDriver()) {
            case 'session':
                $this->deleteFromSession($config, $stateTypeString);
                break;
            case 'database':
                $this->deleteFromDatabase($config, $stateTypeString);
                break;
        }
    }

    /**
     * Save data to session
     */
    protected function saveToSession(Persistence $config, string $stateType, mixed $data): void
    {
        $key = $config->generateStorageKey($this->tableId, $stateType);

        $payload = [
            'data' => $data,
            'timestamp' => Carbon::now()->toISOString(),
            'metadata' => $config->getMetadata(),
        ];

        Session::put($key, $payload);
    }

    /**
     * Load data from session
     */
    protected function loadFromSession(Persistence $config, string $stateType, mixed $default): mixed
    {
        $key = $config->generateStorageKey($this->tableId, $stateType);
        $payload = Session::get($key);

        if (!$payload || !is_array($payload)) {
            return $default;
        }

        // Check TTL if configured
        if ($config->getTtl() && isset($payload['timestamp'])) {
            $timestamp = Carbon::parse($payload['timestamp']);
            if ($config->isExpired($timestamp)) {
                Session::forget($key);
                return $default;
            }
        }

        return $payload['data'] ?? $default;
    }

    /**
     * Delete data from session
     */
    protected function deleteFromSession(Persistence $config, string $stateType): void
    {
        $key = $config->generateStorageKey($this->tableId, $stateType);
        Session::forget($key);
    }

    /**
     * Save data to database
     */
    protected function saveToDatabase(Persistence $config, string $stateType, mixed $data): void
    {
        $stateId = $config->getStateId();
        $table = $config->getTable();
        $connection = $config->getConnection();

        $payload = [
            'state_id' => $stateId,
            'table_id' => $this->tableId,
            'state_type' => $stateType,
            'state_data' => json_encode($data),
            'updated_at' => Carbon::now(),
            'metadata' => json_encode($config->getMetadata()),
        ];

        // Add expiration if TTL is configured
        if ($config->getTtl()) {
            $payload['expires_at'] = $config->getExpirationTimestamp();
        }

        DB::connection($connection)
            ->table($table)
            ->updateOrInsert(
                [
                    'state_id' => $stateId,
                    'table_id' => $this->tableId,
                    'state_type' => $stateType,
                ],
                $payload
            );
    }

    /**
     * Load data from database
     */
    protected function loadFromDatabase(Persistence $config, string $stateType, mixed $default): mixed
    {
        $stateId = $config->getStateId();
        $table = $config->getTable();
        $connection = $config->getConnection();

        $record = DB::connection($connection)
            ->table($table)
            ->where('state_id', $stateId)
            ->where('table_id', $this->tableId)
            ->where('state_type', $stateType)
            ->first();

        if (!$record) {
            return $default;
        }

        // Check TTL if configured
        if ($config->getTtl() && $record->expires_at) {
            $expiresAt = Carbon::parse($record->expires_at);
            if ($expiresAt->isPast()) {
                // Delete expired record
                $this->deleteFromDatabase($config, $stateType);
                return $default;
            }
        }

        return json_decode($record->state_data, true) ?? $default;
    }

    /**
     * Delete data from database
     */
    protected function deleteFromDatabase(Persistence $config, string $stateType): void
    {
        $stateId = $config->getStateId();
        $table = $config->getTable();
        $connection = $config->getConnection();

        DB::connection($connection)
            ->table($table)
            ->where('state_id', $stateId)
            ->where('table_id', $this->tableId)
            ->where('state_type', $stateType)
            ->delete();
    }

    // Utility methods (moved from StatePersistenceManager)

    /**
     * Delete all state persistence for a specific state ID
     */
    public function deleteAllForStateId(mixed $stateId, string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)
            ->where('state_id', $stateId)
            ->delete();

        // Also clear session data for this table
        $this->clearSessionForTable($this->tableId);
    }

    /**
     * Delete all state persistence for this table
     */
    public function deleteAllForTable(string $connection = null): void
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        DB::connection($connection)->table($table)
            ->where('table_id', $this->tableId)
            ->delete();

        // Also clear session data
        $this->clearSessionForTable($this->tableId);
    }

    /**
     * Delete expired database records for this table
     */
    public function cleanupExpired(string $connection = null): int
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        $query = DB::connection($connection)->table($table)
            ->where('table_id', $this->tableId)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());

        $deletedCount = $query->count();
        $query->delete();

        return $deletedCount;
    }

    /**
     * Check if state exists for given parameters
     */
    public function hasState(mixed $stateId, StateType|string $stateType, string $driver = 'session'): bool
    {
        $stateTypeValue = $stateType instanceof StateType ? $stateType->value : $stateType;

        if ($driver === 'session') {
            $key = "flux_tables.{$this->tableId}.{$stateTypeValue}";
            return Session::has($key);
        }

        if ($driver === 'database') {
            $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

            return DB::table($table)
                ->where('state_id', $stateId)
                ->where('table_id', $this->tableId)
                ->where('state_type', $stateTypeValue)
                ->exists();
        }

        return false;
    }

    /**
     * Get statistics about stored state data for this table
     */
    public function getStatistics(string $connection = null): array
    {
        $table = config('flux-tables.persistence.database.table', 'table_state_preferences');

        $stats = [
            'total_records' => 0,
            'expired_records' => 0,
            'state_types' => [],
            'unique_state_ids' => 0,
        ];

        try {
            $query = DB::connection($connection)->table($table)
                ->where('table_id', $this->tableId);

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

        } catch (\Exception $e) {
            // Table might not exist yet
        }

        return $stats;
    }

    /**
     * Clear session data for this table
     */
    protected function clearSessionForTable(string $tableId): void
    {
        $sessionKeys = $this->getSessionKeysForTable($tableId);

        foreach ($sessionKeys as $key) {
            Session::forget($key);
        }
    }

    /**
     * Get session keys for this table
     */
    protected function getSessionKeysForTable(string $tableId): array
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
}