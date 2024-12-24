<?php

namespace Idkwhoami\FluxTables\Facades;

use Idkwhoami\FluxTables\Table;
use Illuminate\Support\Facades\Facade;

/**
 * @method Table create(string $model, string $name = null)
 * @method Table getTable(string $nameOrModelClass)
 *
 * @see \Idkwhoami\FluxTables\FluxTables
 */
class FluxTables extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Idkwhoami\FluxTables\FluxTables::class;
    }
}
