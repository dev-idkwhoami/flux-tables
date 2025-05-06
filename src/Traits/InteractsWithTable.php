<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Action\ModalAction;
use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithTable
{
    public ModalAction $action;
    public mixed $id;

    public function mountInteractsWithTable(ModalAction $action, mixed $id): void
    {
        $this->id = $id;
        $this->action = $action;
    }

    public abstract function getModel(): Model;

    final public function refreshTable(string $tableComponent = SimpleTable::class): void
    {
        $this->dispatch('flux-tables::table:refresh')->to($tableComponent);
    }

}
