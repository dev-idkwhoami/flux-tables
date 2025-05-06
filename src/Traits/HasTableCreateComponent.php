<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
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

}
