<?php

namespace Idkwhoami\FluxTables\Services;

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

class TableConfigurationManager
{
    protected static string $sessionKey = 'flux_tables_configurations';

    public static function store(TableConfiguration $configuration): string
    {
        $id = uniqid('table_config_');
        $configurations = session()->get(static::$sessionKey, []);
        // Serialize the configuration for session storage (handles closures properly)
        $configurations[$id] = $configuration->toLivewire();
        session()->put(static::$sessionKey, $configurations);
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
}
