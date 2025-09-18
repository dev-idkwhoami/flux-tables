<?php

namespace Idkwhoami\FluxTables\Events\Filter;

class FilterResetEvent
{
    public function __construct(
        public readonly string $tableId,
        public readonly string $filterName
    ) {
    }
}