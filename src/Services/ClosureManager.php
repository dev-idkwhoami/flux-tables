<?php

namespace Idkwhoami\FluxTables\Services;

use Laravel\SerializableClosure\SerializableClosure;
use Closure;

class ClosureManager
{
    protected static string $sessionKey = 'flux_tables_closures';

    public static function store(Closure $closure): string
    {
        $id = uniqid('closure_');
        $serialized = new SerializableClosure($closure);

        $closures = session()->get(static::$sessionKey, []);
        $closures[$id] = serialize($serialized);
        session()->put(static::$sessionKey, $closures);

        return $id;
    }

    public static function get(string $id): ?Closure
    {
        $closures = session()->get(static::$sessionKey, []);

        if (!isset($closures[$id])) {
            return null;
        }

        $serialized = unserialize($closures[$id]);
        return $serialized->getClosure();
    }

    public static function has(string $id): bool
    {
        $closures = session()->get(static::$sessionKey, []);
        return isset($closures[$id]);
    }

    public static function forget(string $id): void
    {
        $closures = session()->get(static::$sessionKey, []);
        unset($closures[$id]);
        session()->put(static::$sessionKey, $closures);
    }

    public static function clear(): void
    {
        session()->forget(static::$sessionKey);
    }

    public static function storeIfClosure(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return ['__closure_id' => static::store($value)];
        }

        return $value;
    }

    public static function restoreIfClosure(mixed $value): mixed
    {
        if (is_array($value) && isset($value['__closure_id'])) {
            return static::get($value['__closure_id']);
        }

        return $value;
    }
}
