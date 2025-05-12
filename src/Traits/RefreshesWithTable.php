<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

/**
 * @property array $listeners
 */
trait RefreshesWithTable
{
    /**
     * @return array
     */
    protected function getListeners(): array
    {
        return array_merge($this->listeners, [
            'flux-tables::table::refresh' => '$refresh'
        ]);
    }

    #[On('flux-tables::table::refresh')]
    abstract public function handleRefresh(): void;

    public function uniqueContextModelKey(Model $model): string
    {
        return sprintf("flux-tables::context::model::%s::%s::value", class_basename($model), $model->{$model->getKeyName()});
    }

    final public function refreshTable(string $tableComponent = SimpleTable::class): void
    {
        $this->dispatch('flux-tables::table::refresh')->to($tableComponent);
    }

}
