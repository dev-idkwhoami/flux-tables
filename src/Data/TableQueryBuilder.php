<?php

namespace Idkwhoami\FluxTables\Data;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TableQueryBuilder
{
    protected Builder $query;
    protected array $appliedFilters = [];
    protected array $appliedSorts = [];
    protected array $appliedScopes = [];
    protected array $joinedRelations = [];
    protected Model $model;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->model = $query->getModel();
    }

    public function fresh(Builder $query): static
    {
        return new static($query);
    }

    public function applyFilter(string $filterName, callable $filterCallback, mixed $value): static
    {
        if (empty($value)) {
            return $this;
        }

        $clone = clone $this;
        $clone->appliedFilters[$filterName] = $value;
        $filterCallback($clone->query, $value);
        return $clone;
    }

    public function applyFilters(array $filters, array $values): static
    {
        $clone = clone $this;
        foreach ($filters as $filter) {
            $filterName = method_exists($filter, 'getName') ? $filter->getName() : (string) $filter;
            if (isset($values[$filterName]) && !empty($values[$filterName])) {
                $clone = $clone->applyFilter(
                    $filterName,
                    fn ($query, $value) => $filter->apply($query, $value),
                    $values[$filterName]
                );
            }
        }
        return $clone;
    }

    public function applySort(string $column, string $direction = 'asc'): static
    {
        $clone = clone $this;
        $clone->appliedSorts[] = [$column, $direction];

        // Check if column requires a relationship join
        if ($this->isRelationshipColumn($column)) {
            $clone = $clone->ensureRelationshipJoined($column);
        }

        $clone->query->orderBy($column, $direction);
        return $clone;
    }

    public function applySearch(array $searchableColumns, string $search): static
    {
        if (empty($search)) {
            return $this;
        }

        $clone = clone $this;

        // Ensure all relationship columns needed for search are joined
        foreach ($searchableColumns as $column) {
            if ($this->isRelationshipColumn($column)) {
                $clone = $clone->ensureRelationshipJoined($column);
            }
        }

        $clone->query->where(function (Builder $query) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                if ($this->isJsonColumn($column)) {
                    // PostgreSQL JSON search optimization
                    $query->orWhereRaw("({$column})::text ILIKE ?", ["%{$search}%"]);
                } else {
                    $query->orWhere($column, 'ILIKE', "%{$search}%");
                }
            }
        });

        return $clone;
    }

    public function applyScope(string $scope, array $parameters = []): static
    {
        $clone = clone $this;
        $clone->appliedScopes[$scope] = $parameters;
        $clone->query->{$scope}(...$parameters);
        return $clone;
    }

    protected function isRelationshipColumn(string $column): bool
    {
        return str_contains($column, '.');
    }

    protected function isJsonColumn(string $column): bool
    {
        return str_contains($column, '->') || str_contains($column, '->>');
    }

    protected function ensureRelationshipJoined(string $column): static
    {
        $parts = explode('.', $column);
        $relationName = $parts[0];

        if (isset($this->joinedRelations[$relationName])) {
            return $this;
        }

        if (!method_exists($this->model, $relationName)) {
            return $this;
        }

        $relation = $this->model->{$relationName}();

        return match (true) {
            $relation instanceof BelongsTo => $this->joinBelongsTo($relation, $relationName),
            $relation instanceof HasOne => $this->joinHasOne($relation, $relationName),
            $relation instanceof HasMany => $this->joinHasMany($relation, $relationName),
            $relation instanceof BelongsToMany => $this->joinBelongsToMany($relation, $relationName),
            default => $this
        };
    }

    protected function joinBelongsTo(BelongsTo $relation, string $relationName): static
    {
        $clone = clone $this;
        $relatedTable = $relation->getRelated()->getTable();
        $foreignKey = $relation->getForeignKeyName();
        $ownerKey = $relation->getOwnerKeyName();

        $alias = $this->getUniqueTableAlias($relatedTable, $relationName);

        $clone->query->leftJoin(
            "{$relatedTable} as {$alias}",
            "{$alias}.{$ownerKey}",
            '=',
            $this->query->qualifyColumn($foreignKey)
        );

        $clone->joinedRelations[$relationName] = $alias;
        return $clone;
    }

    protected function joinHasOne(HasOne $relation, string $relationName): static
    {
        $clone = clone $this;
        $relatedTable = $relation->getRelated()->getTable();
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getLocalKeyName();

        $alias = $this->getUniqueTableAlias($relatedTable, $relationName);

        $clone->query->leftJoin(
            "{$relatedTable} as {$alias}",
            "{$alias}.{$foreignKey}",
            '=',
            $this->query->qualifyColumn($localKey)
        );

        $clone->joinedRelations[$relationName] = $alias;
        return $clone;
    }

    protected function joinHasMany(HasMany $relation, string $relationName): static
    {
        $clone = clone $this;
        $relatedTable = $relation->getRelated()->getTable();
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getLocalKeyName();

        $alias = $this->getUniqueTableAlias($relatedTable, $relationName);

        $clone->query->leftJoin(
            "{$relatedTable} as {$alias}",
            "{$alias}.{$foreignKey}",
            '=',
            $this->query->qualifyColumn($localKey)
        );

        // Use JSON aggregation for PostgreSQL to avoid GROUP BY issues
        $clone->query->selectRaw("json_agg({$alias}.*) as {$relationName}_data");
        $clone->query->groupBy($this->query->qualifyColumn($this->model->getKeyName()));

        $clone->joinedRelations[$relationName] = $alias;
        return $clone;
    }

    protected function joinBelongsToMany(BelongsToMany $relation, string $relationName): static
    {
        $clone = clone $this;
        $pivotTable = $relation->getTable();
        $relatedTable = $relation->getRelated()->getTable();

        $pivotAlias = $this->getUniqueTableAlias($pivotTable, $relationName . '_pivot');
        $relatedAlias = $this->getUniqueTableAlias($relatedTable, $relationName);

        // Join pivot table
        $clone->query->leftJoin(
            "{$pivotTable} as {$pivotAlias}",
            $relation->getQualifiedParentKeyName(),
            '=',
            "{$pivotAlias}.{$relation->getForeignPivotKeyName()}"
        );

        // Join related table
        $clone->query->leftJoin(
            "{$relatedTable} as {$relatedAlias}",
            "{$pivotAlias}.{$relation->getRelatedPivotKeyName()}",
            '=',
            "{$relatedAlias}.{$relation->getRelatedKeyName()}"
        );

        // Use JSON aggregation for PostgreSQL
        $clone->query->selectRaw("json_agg({$relatedAlias}.*) as {$relationName}_data");
        $clone->query->groupBy($this->query->qualifyColumn($this->model->getKeyName()));

        $clone->joinedRelations[$relationName] = $relatedAlias;
        return $clone;
    }

    protected function getUniqueTableAlias(string $tableName, string $context): string
    {
        $baseAlias = $context . '_' . $tableName;
        $counter = 1;
        $alias = $baseAlias;

        while (in_array($alias, array_values($this->joinedRelations))) {
            $alias = $baseAlias . '_' . $counter;
            $counter++;
        }

        return $alias;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function toSql(): string
    {
        return $this->query->toSql();
    }

    public function getBindings(): array
    {
        return $this->query->getBindings();
    }

    public function clone(): static
    {
        $clone = new static($this->query->clone());
        $clone->appliedFilters = $this->appliedFilters;
        $clone->appliedSorts = $this->appliedSorts;
        $clone->appliedScopes = $this->appliedScopes;
        $clone->joinedRelations = $this->joinedRelations;
        $clone->model = $this->model;
        return $clone;
    }
}
