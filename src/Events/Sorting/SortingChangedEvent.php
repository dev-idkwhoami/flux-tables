<?php

namespace Idkwhoami\FluxTables\Events\Sorting;

class SortingChangedEvent
{
    public function __construct(
        public readonly string $tableId,
        public readonly string $column,
        public readonly string $direction,
        public readonly ?string $previousColumn = null,
        public readonly ?string $previousDirection = null
    ) {
    }
}