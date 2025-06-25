<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Concretes\Column\JsonColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
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

        $columnAliases = [];
        $columnCasts = [];
        foreach ($this->table->getColumns() as $column) {
            if ($column->isSortable()) {
                $columnAliases[$column->getName()] = $column->getOrderByColumn();
            }
            if ($column instanceof JsonColumn) {
                $columnCasts[$column->getName()] = $column->getType();
            }
        }

        Session::put($this->columnAliasSessionKey(), $columnAliases);
        Session::put($this->columnCastSessionKey(), $columnCasts);
    }

    public function columnAliasSessionKey(): string
    {
        return "flux-tables::table::{$this->table->name}::columns";
    }

    public function columnCastSessionKey(): string
    {
        return "flux-tables::table::{$this->table->name}::casts";
    }

    /**
     * @return string
     */
    public function sortingValueSessionKey(): string
    {
        return "flux-tables::table::{$this->table->name}::sorting";
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    public function applySorting(Builder $query): void
    {
        $columnAliases = Session::get($this->columnAliasSessionKey());
        $sortingColumn = $columnAliases[$this->getSortingColumn()] ?? $this->defaultSortingColumn();
        $columnCasts = Session::get($this->columnCastSessionKey());
        $columnCast = $columnCasts[$this->getSortingColumn()] ?? 'text';

        if (str_contains($sortingColumn, '::json')) {
            $query->orderByRaw(
                sprintf(
                    "%s %s",
                    sprintf("(\"%s\".%s)::%s", $query->getModel()->getTable(), $sortingColumn, $columnCast),
                    $this->getSortingDirection()
                )
            );
            return;
        }

        $qualifiedColumn = $query->qualifyColumn($sortingColumn);
        if (array_filter($query->getQuery()->getColumns(), fn ($column) => preg_match('/^.* as (.+)$/i', $column, $matches) && $matches[1] === $sortingColumn)) {
            $qualifiedColumn = $sortingColumn;
        }

        $query->orderBy($qualifiedColumn, $this->getSortingDirection());
    }

    /**
     * @return string
     */
    abstract public function defaultSortingColumn(): string;

    abstract public function defaultSortingDirection(): string;

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
        return empty($this->sortingDirection) ? $this->defaultSortingDirection() : $this->sortingDirection;
    }

    public function getRawSortingDirection(): ?string
    {
        return $this->sortingDirection;
    }

}
