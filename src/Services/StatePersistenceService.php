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
}