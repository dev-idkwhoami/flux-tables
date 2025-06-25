<?php

namespace Idkwhoami\FluxTables\Abstracts\Table;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Livewire\Wireable;

abstract class Table implements Wireable
{
    use WireCompatible;

    /**
     * @var Column[] $columns
     */
    protected array $columns = [];

    /**
     * @var Filter[] $filters
     */
    protected array $filters = [];

    final protected function __construct(
        public string $name,
    ) {
    }

    /**
     * @param  Column[]  $columns
     * @return $this
     */
    public function columns(array $columns): Table
    {
        $this->columns = $columns;
        array_walk($this->columns, fn (Column $column) => $column->tableInitialized($this));
        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return array_filter($this->columns, fn (Column $c) => $c->shouldBeVisible($this));
    }

    public function getColumn(string $key): Column
    {
        $filtered = array_filter($this->columns, fn (Column $c) => $c->getName() === $key);

        if (empty($filtered)) {
            throw new \Exception("Column with key {$key} not found");
        }
        return array_shift($filtered);
    }

    /**
     * @return bool
     */
    public function hasColumns(): bool
    {
        return !empty($this->columns);
    }

    /**
     * @param  Filter[]  $filters
     * @return $this
     */
    public function filters(array $filters): Table
    {
        $this->filters = array_map(fn ($filter) => $filter->table($this->name), $filters);
        return $this;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return array_filter($this->filters, fn (Filter $f) => $f->shouldBeVisible($this));
    }

    /**
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

}
