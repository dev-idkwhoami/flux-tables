<?php

namespace Idkwhoami\FluxTables\Data;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TableDataRepository
{
    protected Builder $baseQuery;
    protected TableQueryBuilder $queryBuilder;

    public function __construct(
        protected readonly string $modelClass
    ) {
        $this->initializeQuery();
    }

    public function query(): TableQueryBuilder
    {
        return $this->queryBuilder->fresh($this->baseQuery->clone());
    }

    public function find(mixed $id): ?Model
    {
        return $this->baseQuery->clone()->find($id);
    }

    public function paginate(TableQueryBuilder $queryBuilder, int $perPage, array $columns = ['*']): LengthAwarePaginator
    {
        return $queryBuilder->getQuery()->paginate($perPage, $columns);
    }

    public function get(TableQueryBuilder $queryBuilder, array $columns = ['*']): Collection
    {
        return $queryBuilder->getQuery()->get($columns);
    }

    public function count(TableQueryBuilder $queryBuilder): int
    {
        return $queryBuilder->getQuery()->count();
    }

    public function with(array $relations): static
    {
        $this->baseQuery->with($relations);
        return $this;
    }

    public function withCount(array $relations): static
    {
        $this->baseQuery->withCount($relations);
        return $this;
    }

    public function scope(string $scopeName, array $parameters = []): static
    {
        $this->baseQuery->{$scopeName}(...$parameters);
        return $this;
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getBaseQuery(): Builder
    {
        return $this->baseQuery->clone();
    }

    protected function initializeQuery(): void
    {
        $this->baseQuery = ($this->modelClass)::query();
        $this->queryBuilder = new TableQueryBuilder($this->baseQuery->clone());
    }
}
