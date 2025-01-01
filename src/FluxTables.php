<?php

namespace Idkwhoami\FluxTables;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

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

    public function refreshTable(Component $component, string $nameOrModelClass): void
    {
        $table = class_exists($nameOrModelClass) && is_subclass_of($nameOrModelClass, Model::class)
            ? strtolower(class_basename($nameOrModelClass))
            : $nameOrModelClass;

        $component->dispatch("table.$table.refresh");
    }

    public function getTable(string $nameOrModelClass): Table
    {
        if (class_exists($nameOrModelClass) && is_subclass_of($nameOrModelClass, Model::class)) {
            return $this->tables[strtolower(class_basename($nameOrModelClass))];
        }

        return $this->tables[$nameOrModelClass];
    }
}
