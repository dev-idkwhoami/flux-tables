<?php

namespace Idkwhoami\FluxTables\Traits;

/**
 * @property array $listeners
 */
trait InteractsWithTable
{
    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        return array_merge($this->listeners, [
            'table:{filter.table}:filter:reset' => 'filterReset'
        ]);
    }

    /**
     * @return void
     */
    public function filterReset(): void
    {
        $this->reset(array_keys($this->except(['filter', 'table'])));
    }

}
