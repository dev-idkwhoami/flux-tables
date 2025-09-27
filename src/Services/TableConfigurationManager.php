<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

class TableConfigurationManager
{
    protected static string $sessionKey = 'flux_tables_configurations';

    public static function store(TableConfiguration $configuration): string
    {
        $id = static::generateConfigurationId($configuration);
        $configurations = session()->get(static::$sessionKey, []);

        // Only store if not already present (idempotent)
        if (!isset($configurations[$id])) {
            // Serialize the configuration for session storage (handles closures properly)
            $configurations[$id] = $configuration->toLivewire();
            session()->put(static::$sessionKey, $configurations);
        }

        return $id;
    }

    public static function get(string $id): ?TableConfiguration
    {
        $configurations = session()->get(static::$sessionKey, []);
        $configData = $configurations[$id] ?? null;

        if ($configData === null) {
            return null;
        }

        // Deserialize the configuration from session storage
        return TableConfiguration::fromLivewire($configData);
    }

    public static function has(string $id): bool
    {
        $configurations = session()->get(static::$sessionKey, []);
        return isset($configurations[$id]);
    }

    public static function forget(string $id): void
    {
        $configurations = session()->get(static::$sessionKey, []);
        unset($configurations[$id]);
        session()->put(static::$sessionKey, $configurations);
    }

    public static function clear(): void
    {
        session()->forget(static::$sessionKey);
    }

    public static function count(): int
    {
        $configurations = session()->get(static::$sessionKey, []);
        return count($configurations);
    }

    /**
     * Generate a deterministic configuration ID based on content
     */
    protected static function generateConfigurationId(TableConfiguration $configuration): string
    {
        // Build a unique identifier from configuration properties
        $identifier = static::buildConfigurationIdentifier($configuration);

        // Create a hash of the identifier for consistent ID generation
        $hash = substr(md5($identifier), 0, 12);

        return 'table_config_' . $hash;
    }

    /**
     * Build a unique identifier string for this configuration
     */
    protected static function buildConfigurationIdentifier(TableConfiguration $configuration): string
    {
        $parts = [
            // Core configuration elements
            $configuration->getModelClass() ?? 'unknown',

            // Serialize the configuration to capture all settings
            // This ensures identical configs get identical IDs
            serialize($configuration->toLivewire()),
        ];

        return implode('|', $parts);
    }
}
