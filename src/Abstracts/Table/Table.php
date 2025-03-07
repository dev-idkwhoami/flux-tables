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
        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
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
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

}
