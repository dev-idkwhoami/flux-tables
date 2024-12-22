<?php

namespace Idkwhoami\FluxTables\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Idkwhoami\FluxTables\FluxTables
 */
class FluxTables extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Idkwhoami\FluxTables\FluxTables::class;
    }
}
