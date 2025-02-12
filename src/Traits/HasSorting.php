<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Session;

trait HasSorting
{

    public ?string $sortingColumn = null;
    public ?string $sortingDirection = null;

    public function mountHasSorting(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }

        [$column, $direction] = Session::get($this->sortingValueSessionKey(), [null, null]);
        $this->sortingColumn = $column;
        $this->sortingDirection = $direction;
    }

    public function sortingValueSessionKey(): string
    {
        return "table:{$this->table->name}:sorting";
    }

    public function applySorting(Builder $query): void
    {
        if (!empty($this->sortingColumn) && !empty($this->sortingDirection)) {
            $query->orderBy($this->defaultSortingColumn(), $this->getSortingDirection());
        }
    }

    public abstract function defaultSortingColumn(): string;

    public function sort(string $column): void
    {
        /* three states: asc, desc, and reset */

        if ($this->sortingColumn === $column) {
            if ($this->sortingDirection === 'asc') {
                $this->sortingDirection = 'desc';
            } elseif ($this->sortingDirection === 'desc') {
                $this->resetSorting();
            }
        } else {
            $this->sortingColumn = $column;
            $this->sortingDirection = 'asc';
        }

        Session::put($this->sortingValueSessionKey(), [
            $this->sortingColumn,
            $this->sortingDirection,
        ]);
    }

    public function resetSorting(): void
    {
        $this->sortingColumn = null;
        $this->sortingDirection = null;

        Session::put($this->sortingValueSessionKey(), [
            $this->sortingColumn,
            $this->sortingDirection,
        ]);
    }

    public function getSortingColumn(): ?string
    {
        return empty($this->sortingColumn) ? $this->defaultSortingColumn() : $this->sortingColumn;
    }

    public function getSortingDirection(): ?string
    {
        return $this->sortingDirection;
    }

}
