<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Table\Table;

trait HasTables
{
    /**
     * @var Table[]
     */
    protected array $tables = [];

    /**
     * @return Table|Table[]
     */
    public abstract function tables(): Table|array;

    public function getTable(string $name): Table
    {
        return collect($this->tables)->firstOrFail(fn($table) => $table->name === $name);
    }

    public function mountHasTable(): void
    {
        $concretes = $this->tables();
        $this->tables = is_array($concretes) ? $concretes : [$concretes];
    }

}
