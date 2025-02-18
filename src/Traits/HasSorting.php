<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Session;

trait HasSorting
{
    public ?string $sortingColumn = null;
    public ?string $sortingDirection = null;

    /**
     * @return void
     * @throws \Exception
     */
    public function mountHasSorting(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }

        [$column, $direction] = Session::get($this->sortingValueSessionKey(), [null, null]);
        $this->sortingColumn = $column;
        $this->sortingDirection = $direction;
    }

    /**
     * @return string
     */
    public function sortingValueSessionKey(): string
    {
        return "table:{$this->table->name}:sorting";
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    public function applySorting(Builder $query): void
    {
        if (!empty($this->sortingDirection)) {
            $query->orderBy($query->qualifyColumn($this->getSortingColumn()), $this->getSortingDirection());
        }
    }

    /**
     * @return string
     */
    abstract public function defaultSortingColumn(): string;

    /**
     * @param  string  $column
     * @return void
     */
    public function sort(string $column): void
    {
        if (!$column) {
            return;
        }

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

    /**
     * @return void
     */
    public function resetSorting(): void
    {
        $this->sortingColumn = null;
        $this->sortingDirection = null;

        Session::put($this->sortingValueSessionKey(), [
            $this->sortingColumn,
            $this->sortingDirection,
        ]);
    }

    /**
     * @return string
     */
    public function getSortingColumn(): string
    {
        return empty($this->sortingColumn) ? $this->defaultSortingColumn() : $this->sortingColumn;
    }

    /**
     * @return string|null
     */
    public function getRawSortingColumn(): ?string
    {
        return $this->sortingColumn;
    }

    /**
     * @return string
     */
    public function getSortingDirection(): string
    {
        return $this->sortingDirection ?? 'asc';
    }

}
