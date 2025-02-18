<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Locked;

trait HasTable
{
    #[Locked]
    public Table $table;

    /**
     * @return Table
     */
    abstract public function table(): Table;

    /**
     * @return Builder
     */
    abstract public function getQuery(): Builder;

    /**
     * @return void
     */
    public function mountHasTable(): void
    {
        $this->table = $this->table();
    }

}
