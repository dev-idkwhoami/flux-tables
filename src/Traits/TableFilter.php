<?php

namespace Idkwhoami\FluxTables\Traits;

/**
 * @property array $listeners
 */
trait TableFilter
{
    /**
     * @return string[]
     */
    public function getListeners(): array
    {
        return array_merge($this->listeners, [
            'flux-tables::table:{filter.table}:filter:reset' => 'filterReset'
        ]);
    }

    /**
     * @return void
     */
    public function filterReset(): void
    {
        $this->reset(array_keys($this->except(['filter', 'table'])));
        $this->restoreDefault();
    }

    abstract public function restoreDefault(): void;

}
