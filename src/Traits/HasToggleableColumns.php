<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Illuminate\Support\Facades\Session;

trait HasToggleableColumns
{
    /**
     * @var string[]
     */
    public array $toggledColumns = [];

    /**
     * @return void
     * @throws \Exception
     */
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

    /**
     * @return Column[]
     */
    public function getToggleableColumns(): array
    {
        return array_filter($this->table->getColumns(), fn (Column $c) => $c->isToggleable());
    }

    /**
     * @return string
     */
    public function toggleableColumnsValueSessionKey(): string
    {
        return "flux-tables::table:{$this->table->name}:toggledColumns";
    }

    /**
     * A string array with the column's names which should be toggled by default.
     * These names are the 'name' property each column has set through the ```Column::make(string $name)``` function.
     *
     * @return string[]
     */
    abstract public function defaultToggledColumns(): array;

    /**
     * @param  string  $column
     * @return void
     */
    public function toggle(string $column): void
    {
        if (!in_array($column, $this->toggledColumns)) {
            $this->toggledColumns[] = $column;
        } else {
            $this->toggledColumns = array_diff($this->toggledColumns, [$column]);
        }

        Session::put($this->toggleableColumnsValueSessionKey(), $this->toggledColumns);
    }

    /**
     * @return void
     */
    public function resetColumns(): void
    {
        $this->toggledColumns = $this->defaultToggledColumns();
        Session::put($this->toggleableColumnsValueSessionKey(), $this->toggledColumns);
    }

    /**
     * @param  string  $column
     * @return bool
     */
    public function isColumnToggled(string $column): bool
    {
        return in_array($column, $this->toggledColumns);
    }
}
