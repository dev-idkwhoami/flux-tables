<?php

namespace Idkwhoami\FluxTables;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Table[] $tables
 */
class FluxTables
{
    protected array $tables = [];

    public function create(string $model, ?string $name = null): Table
    {
        $name ??= strtolower(class_basename($model));
        $this->tables[$name] = new Table($model);

        return $this->getTable($name);
    }

    public function getTable(string $nameOrModelClass): Table
    {
        if (class_exists($nameOrModelClass) && is_subclass_of($nameOrModelClass, Model::class)) {
            return $this->tables[strtolower(class_basename($nameOrModelClass))];
        }

        return $this->tables[$nameOrModelClass];
    }
}
