<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Columns\Column;
use Idkwhoami\FluxTables\Enums\SortDirection;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Wireable;

class Table implements Wireable
{
    protected string $model;

    protected string $name;

    protected array $columns = [];

    public array $filters = [];

    protected array $actions = [];

    public $perPage = 10;

    public $page = 1;

    public $perPageOptions = [10, 25, 50, 100];

    public $search = null;

    protected ?string $sortColumn = null;

    protected SortDirection $sortDirection = SortDirection::Descending;

    public function __construct(string $model)
    {
        if (! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Model $model is not a valid model class");
        }

        $this->model = $model;
        $this->name = strtolower(class_basename($model));
    }

    public function hasSearchable(): bool
    {
        return collect($this->columns)->filter->isSearchable()->isNotEmpty();
    }

    public function isSorted(string $column): bool
    {
        return $this->sortColumn === $column;
    }

    public function sort(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === SortDirection::Ascending ? SortDirection::Descending : SortDirection::Ascending;
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = SortDirection::Ascending;
        }
    }

    public function search(string $search): void
    {
        $this->search = $search;
    }

    public function applySort(Builder $query): Builder
    {
        $query->orderBy($this->sortColumn, $this->sortDirection->value);

        return $query;
    }

    public function applySearch(Builder $query): Builder
    {
        foreach ($this->columns as /** @var $column Column */ $column) {
            $query->when($column->isSearchable(),
                fn (Builder $query) => $query->where($column->getName(), 'ilike', "%{$this->search}%"));
        }

        return $query;
    }

    public function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters as $name => $filter) {
            dump($filter);
            $query->when($filter->hasValue(), fn (Builder $query) => $filter->apply($query));
        }

        return $query;
    }

    public function getQuery(): Builder
    {
        return ($this->model)::query();
    }

    public function getPaginatedModels(): LengthAwarePaginator
    {
        return $this->getQuery()
            ->when($this->search, fn ($query) => $this->applySearch($query))
            ->when($this->sortColumn, fn ($query) => $this->applySort($query))
            ->tap(fn ($query) => $this->applyFilters($query))
            ->paginate($this->perPage);
    }

    public function table(\Closure $table): static
    {
        return $table($this);
    }

    public function columns(array $columns = []): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function filters(array $filters = []): static
    {
        $this->filters = collect($filters)->mapWithKeys(fn ($filter) => [$filter->getName() => $filter])->toArray();

        return $this;
    }

    public function actions(array $actions = []): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function defaultSortColumn(string $column): static
    {
        $this->sortColumn = $column;

        return $this;
    }

    public function paginationOptions(array $options = []): static
    {
        $this->perPageOptions = $options;

        return $this;
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->model,
            'model' => $this->model,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'actions' => $this->actions,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'perPageOptions' => $this->perPageOptions,
            'search' => $this->search,
            'sortColumn' => $this->sortColumn,
            'sortDirection' => $this->sortDirection,
        ];
    }

    public static function fromLivewire($value)
    {
        return \Idkwhoami\FluxTables\Facades\FluxTables::getTable($value['name']);
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    public function getSearch(): null
    {
        return $this->search;
    }

    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function getSortDirection(): SortDirection
    {
        return $this->sortDirection;
    }
}
