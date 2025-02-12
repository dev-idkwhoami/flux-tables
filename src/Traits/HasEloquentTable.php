<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Locked;

trait HasEloquentTable
{
    #[Locked]
    public EloquentTable $table;

    #[Locked]
    public ?string $eloquentModel = null;

    /**
     * @return Table
     */
    public abstract function table(string $model): Table;

    public abstract function getQuery(): Builder;

    public function mountHasEloquentTable(string $model): void
    {
        $this->eloquentModel = $model;
        $this->table = $this->table($model);
    }

}
