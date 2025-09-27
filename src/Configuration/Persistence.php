<?php

namespace Idkwhoami\FluxTables\Configuration;

use Closure;
use Carbon\Carbon;

class Persistence
{
    protected string $driver = 'none';
    protected ?int $ttl = null; // seconds
    protected ?string $connection = null;
    protected ?string $table = null;
    protected bool $enableCleanup = false;
    protected mixed $owner = null;
    protected array $metadata = [];

    protected function __construct(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Create session-based persistence configuration
     */
    public static function session(): static
    {
        return new static('session');
    }

    /**
     * Create database-based persistence configuration
     */
    public static function database(): static
    {
        return new static('database');
    }

    /**
     * Create no persistence configuration
     */
    public static function none(): static
    {
        return new static('none');
    }


    /**
     * Set time-to-live in seconds
     */
    public function ttl(int $seconds = null, int $minutes = null, int $hours = null, int $days = null): static
    {
        if ($seconds !== null) {
            $this->ttl = $seconds;
        } elseif ($minutes !== null) {
            $this->ttl = $minutes * 60;
        } elseif ($hours !== null) {
            $this->ttl = $hours * 3600;
        } elseif ($days !== null) {
            $this->ttl = $days * 86400;
        }

        return $this;
    }

    /**
     * Set database connection (for database driver)
     */
    public function connection(string $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set database table name (for database driver)
     */
    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Enable automatic cleanup of expired records
     */
    public function enableCleanup(bool $enabled = true): static
    {
        $this->enableCleanup = $enabled;
        return $this;
    }

    /**
     * Set the owner for state persistence (User, Team, etc.)
     */
    public function owner(mixed $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Add custom metadata
     */
    public function metadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Set custom metadata key-value
     */
    public function meta(string $key, mixed $value): static
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    // Getters

    public function getDriver(): string
    {
        return $this->driver;
    }


    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function getConnection(): ?string
    {
        return $this->connection; // Falls back to Laravel's default connection
    }

    public function getTable(): string
    {
        return $this->table ?? config('flux-tables.persistence.default_table', 'table_state_preferences');
    }

    public function isCleanupEnabled(): bool
    {
        return $this->enableCleanup;
    }

    public function getStateId(): string
    {
        $owner = $this->getOwner();

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
     * Get the configured owner or fallback to authenticated user
     */
    public function getOwner(): mixed
    {
        return $this->owner ?? auth()->user();
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Generate storage key for this configuration
     */
    public function generateStorageKey(string $tableId, string $type = 'column_toggles'): string
    {
        if ($this->driver === 'session') {
            return "flux_tables.{$tableId}.{$type}";
        }

        return "flux_tables_{$tableId}_{$type}";
    }

    /**
     * Check if TTL has expired for given timestamp
     */
    public function isExpired(?Carbon $timestamp): bool
    {
        if (!$this->ttl || !$timestamp) {
            return false;
        }

        return $timestamp->addSeconds($this->ttl)->isPast();
    }

    /**
     * Get expiration timestamp for current time
     */
    public function getExpirationTimestamp(): ?Carbon
    {
        if (!$this->ttl) {
            return null;
        }

        return Carbon::now()->addSeconds($this->ttl);
    }

    /**
     * Validate configuration
     */
    public function validate(): void
    {
        if ($this->driver === 'database' && !$this->getTable()) {
            throw new \InvalidArgumentException('Database persistence requires a table name');
        }

        if ($this->ttl && $this->ttl < 0) {
            throw new \InvalidArgumentException('TTL must be positive');
        }
    }

    /**
     * Convert to array for debugging/logging
     */
    public function toArray(): array
    {
        return [
            'driver' => $this->driver,
            'ttl' => $this->ttl,
            'connection' => $this->getConnection(),
            'table' => $this->getTable(),
            'cleanup_enabled' => $this->enableCleanup,
            'owner' => $this->owner,
            'state_id' => $this->getStateId(),
            'metadata' => $this->metadata,
        ];
    }
}