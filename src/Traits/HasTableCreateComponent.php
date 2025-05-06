<?php

namespace Idkwhoami\FluxTables\Traits;

use Flux\Flux;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Livewire\Attributes\Locked;

trait HasTableCreateComponent
{
    #[Locked]
    public string $modal = '';
    #[Locked]
    public EloquentTable $table;

    public function mountHasTableCreateComponent(EloquentTable $table, string $modal): void
    {
        $this->table = $table;
        $this->modal = $modal;
    }

    final public function closeModal(): void
    {
        Flux::modal($this->modal)->close();
    }

    final public function refreshTable(string $tableComponent = SimpleTable::class): void
    {
        $this->dispatch('flux-tables::table:refresh')->to($tableComponent);
    }

}
