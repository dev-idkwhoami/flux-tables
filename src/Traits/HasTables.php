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
    abstract public function tables(): Table|array;

    /**
     * @param  string  $name
     * @return Table
     */
    public function getTable(string $name): Table
    {
        return collect($this->tables)->firstOrFail(fn ($table) => $table->name === $name);
    }

    /**
     * @return void
     */
    public function mountHasTable(): void
    {
        $concretes = $this->tables();
        $this->tables = is_array($concretes) ? $concretes : [$concretes];
    }

}
