<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Illuminate\Support\Facades\Session;

trait HasToggleableColumns
{
    public array $toggledColumns = [];

    public function mountHasToggleableColumns(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }

        if (!Session::has($this->toggleableColumnsValueSessionKey())) {
            Session::put($this->toggleableColumnsValueSessionKey(), $this->defaultToggledColumns());
        }

        $this->toggledColumns = Session::get($this->toggleableColumnsValueSessionKey());
    }

    public function getToggleableColumns(): array
    {
        return array_filter($this->table->getColumns(), fn(Column $c) => $c->isToggleable());
    }

    public function toggleableColumnsValueSessionKey(): string
    {
        return "table:{$this->table->name}:toggledColumns";
    }

    abstract public function defaultToggledColumns(): array;

    public function toggle(string $column): void
    {
        if (!in_array($column, $this->toggledColumns)) {
            $this->toggledColumns[] = $column;
        } else {
            $this->toggledColumns = array_diff($this->toggledColumns, [$column]);
        }

        Session::put($this->toggleableColumnsValueSessionKey(), $this->toggledColumns);
    }

    public function resetColumns(): void
    {
        $this->toggledColumns = $this->defaultToggledColumns();
        Session::put($this->toggleableColumnsValueSessionKey(), $this->toggledColumns);
    }

    public function isColumnToggled(string $column): bool
    {
        return in_array($column, $this->toggledColumns);
    }
}
