<?php

namespace Idkwhoami\FluxTables\Events\Filter;

class AllFiltersResetEvent
{
    public function __construct(
        public readonly string $tableId
    ) {
    }
}