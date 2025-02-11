<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Locked;

trait HasTable
{
    #[Locked]
    public Table $table;

    /**
     * @return Table
     */
    public abstract function table(): Table;

    public abstract function getQuery(): Builder;

    public function mountHasTable(): void
    {
        $this->table = $this->table();
    }

}
