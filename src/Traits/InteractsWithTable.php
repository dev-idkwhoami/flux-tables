<?php

namespace Idkwhoami\FluxTables\Traits;

use Flux\Flux;
use Idkwhoami\FluxTables\Abstracts\Action\ModalAction;
use Idkwhoami\FluxTables\Livewire\SimpleTable;

trait InteractsWithTable
{
    use InteractsWithData;

    public ModalAction $action;
    public mixed $id;

    public function mountInteractsWithTable(ModalAction $action, mixed $id, mixed ...$args): void
    {
        $this->id = $id;
        $this->action = $action;

        foreach ($args as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    final public function closeModal(): void
    {
        Flux::modal($this->action->modalUniqueName($this->id))->close();
    }

    final public function refreshTable(string $tableComponent = SimpleTable::class): void
    {
        $this->dispatch('flux-tables::table::refresh')->to($tableComponent);
    }

}
