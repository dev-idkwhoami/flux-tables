<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Livewire\SimpleTable;

trait InteractsWithTable
{
    final public function refreshTable(string $tableComponent = SimpleTable::class): void
    {
        $this->dispatch('flux-tables::table:refresh')->to($tableComponent);
    }

}
