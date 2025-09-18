<?php

namespace Idkwhoami\FluxTables\Events\Filter;

class FilterUpdatedEvent
{
    public function __construct(
        public readonly string $tableId,
        public readonly string $filterName,
        public readonly mixed $value,
        public readonly mixed $previousValue = null
    ) {
    }
}