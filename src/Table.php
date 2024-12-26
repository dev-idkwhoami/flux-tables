<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Columns\Column;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Wireable;

class Table implements Wireable
{
    protected string $model;

    protected string $name;

    public array $columns = [];

    public array $filters = [];

    public array $actions = [];

    public int $perPage = 10;

    public ?array $perPageOptions = null;

    public ?string $search = null;

    protected ?string $sortColumn = null;

    protected ?string $sortDirection = null;

    public function __construct(string $model)
    {
        if (!class_exists($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Model $model is not a valid model class");
        }

        $this->model = $model;
        $this->name = strtolower(class_basename($model));
    }

    public function fill(array $values): static
    {
        foreach ($values as $key => $value) {
            $this->{$key} = $value;
        }
        return $this;
    }

    public function refillFilters(): void
    {
        foreach ($this->filters as $filter) {
            $filter->loadValueFromSession($this->name);
        }
    }

    public function refillColumnToggleStates(): void
    {
        foreach ($this->getToggleableColumns() as $column) {
            $column->loadToggleStateFromSession($this->name);
        }
    }

    public function resetFilters(): void
    {
        foreach ($this->filters as $filter) {
            $filter->resetValue($this->name);
        }
    }

    public function toggleColumn(int $index): void
    {
        $this->columns[$index]->setToggleStateInSession($this->name);
    }

    public function prepare(): void
    {
        $this->refillFilters();
        $this->refillColumnToggleStates();
        $this->perPage = session("table:{$this->name}:perPage", $this->perPage);
    }

    public function hasActiveFilters(): bool
    {
        return collect($this->filters)->filter->hasValue()->isNotEmpty();
    }

    public function hasToggleableColumns(): bool
    {
        return collect($this->columns)->filter->isToggleable()->isNotEmpty();
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
        if ($this->sortColumn !== $column) {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        } else {
            $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
        }
    }

    public function search(string $search): void
    {
        $this->search = $search;
    }

    public function applySort(Builder $query): Builder
    {
        $query->orderBy($this->sortColumn, $this->sortDirection);

        return $query;
    }

    public function applySearch(Builder $query): Builder
    {
        foreach ($this->columns as /** @var $column Column */ $column) {
            $query->when($column->isSearchable(),
                fn(Builder $query) => $query->orWhere($column->getName(), 'ilike', "%{$this->search}%"));
        }

        return $query;
    }

    public function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters as $filter) {
            $query->when($filter->hasValue(), fn(Builder $query) => $filter->apply($query));
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
            ->when($this->search, fn($query) => $this->applySearch($query))
            ->when($this->sortColumn, fn($query) => $this->applySort($query))
            ->when(!empty($this->filters), fn($query) => $this->applyFilters($query))
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
        $this->filters = $filters;
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

    public function paginationOptions(array $options, $default = null): static
    {
        $this->perPageOptions = $options;
        $this->perPage = $default ?? array_shift($options);
        return $this;
    }

    public function perPage(int $perPage): static
    {
        session()->put("table:{$this->name}:perPage", $perPage);
        $this->perPage = $perPage;
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
            'perPageOptions' => $this->perPageOptions,
            'search' => $this->search,
            'sortColumn' => $this->sortColumn,
            'sortDirection' => $this->sortDirection,
        ];
    }

    public static function fromLivewire($value)
    {
        return \Idkwhoami\FluxTables\Facades\FluxTables::getTable($value['name'])->fill($value);
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getToggleableColumns(): array
    {
        return collect($this->columns)->filter->isToggleable()->toArray();
    }

    public function getFilterIndex(string $name): int
    {
        return collect($this->filters)->search(fn($filter) => $filter->getName() === $name);
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function getSortDirection(): false|string
    {
        return $this->sortDirection ?? false;
    }
}
