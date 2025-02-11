<?php

namespace Idkwhoami\FluxTables\Traits;

/**
 * @property array $listeners
 */
trait InteractsWithTable
{

    public function getListeners(): array
    {
        return array_merge($this->listeners, [
            'table:{filter.table}:filter:reset' => 'filterReset'
        ]);
    }

    public function filterReset(): void
    {
        $this->reset(array_keys($this->except('filter')));
    }

}
