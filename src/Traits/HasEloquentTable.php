<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Locked;

trait HasEloquentTable
{
    #[Locked]
    public EloquentTable $table;

    #[Locked]
    public string $eloquentModel = '';

    #[Locked]
    public bool $optimizeSelects = false;

    /**
     * @param  string  $model
     * @param  Column[]  $columns
     * @param  Filter[]  $filters
     * @return Table
     */
    abstract public function table(string $model, array $columns = [], array $filters = []): Table;

    /**
     * @return Builder
     */
    abstract public function getQuery(): Builder;

    /**
     * @param  bool  $optimize
     * @return $this
     */
    public function optimizeSelects(bool $optimize = true): static
    {
        $this->optimizeSelects = $optimize;
        return $this;
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    public function applyRelations(Builder $query): void
    {
        if ($this->table->hasColumns()) {
            /** @var Model $model */
            $model = new $this->eloquentModel();

            $relationColumns = $this->getRelationColumns();

            if (!empty($relationColumns)) {
                $joinedTables = [];

                /** @var PropertyColumn $column */
                foreach ($relationColumns as $column) {
                    if (!method_exists($model, $column->getRelationName())) {
                        continue;
                    }

                    /** @var Relation $relation */
                    $relation = $model->{$column->getRelationName()}();

                    if (!$relation) {
                        continue;
                    }

                    if ($relation instanceof BelongsTo) {
                        $joinedTables[] = $this->applyBelongsToRelation(
                            $query,
                            $relation,
                            $column,
                            $model,
                            $joinedTables
                        );
                    }

                    if ($relation instanceof HasOne) {
                        $joinedTables[] = $this->applyHasOneRelation(
                            $query,
                            $relation,
                            $column,
                            $model,
                            $joinedTables
                        );
                    }

                    if ($relation instanceof BelongsToMany) {
                        $column->sortable(false);
                        $joinedTables = array_merge(
                            $joinedTables,
                            $this->applyBelongsToManyRelation($query, $relation, $column, $model, $joinedTables)
                        );
                    }

                    if ($relation instanceof HasMany) {
                        $column->sortable(false);
                        $joinedTables[] = $this->applyHasManyRelation(
                            $query,
                            $relation,
                            $column,
                            $model,
                            $joinedTables
                        );
                    }
                }

                $query->groupByRaw(
                    $query->qualifyColumn($model->getKeyName())
                );
            }

        }
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    public function applyColumns(Builder $query): void
    {
        /** @var Model $model */
        $model = new $this->eloquentModel();

        $nonRelationColumns = array_filter(
            $this->table->getColumns(),
            fn(Column $c) => !$c instanceof PropertyColumn || !$c->hasRelation()
        );

        if (!empty($nonRelationColumns)) {
            foreach ($nonRelationColumns as $column) {
                if ($column instanceof PropertyColumn) {
                    $query->selectRaw($query->qualifyColumn($column->getSortableProperty()));
                }
            }
        }

        $query->selectRaw($query->qualifyColumn($model->getKeyName()));
    }

    /**
     * @param  string  $model
     * @param  Column[]  $columns
     * @param  Filter[]  $filters
     * @return void
     */
    public function mountHasEloquentTable(string $model, array $columns = [], array $filters = []): void
    {
        $this->eloquentModel = $model;
        $this->table = $this->table($model, $columns, $filters);
    }

    /**
     * @return Column[]
     */
    public function getRelationColumns(): array
    {
        return array_filter(
            $this->table->getColumns(),
            fn(Column $c) => $c instanceof PropertyColumn && $c->hasRelation()
        );
    }

    /**
     * @param  Builder  $query
     * @param  BelongsTo  $relation
     * @param  PropertyColumn  $column
     * @param  Model  $model
     * @param  string[]  $joinedTables
     * @return string
     */
    public function applyBelongsToRelation(
        Builder $query,
        BelongsTo $relation,
        PropertyColumn $column,
        Model $model,
        array &$joinedTables = []
    ): string {
        $table = in_array($relation->getRelated()->getTable(), $joinedTables)
            ? sprintf(
                '%s:%s',
                $relation->getRelated()->getTable(),
                $column->getRelationName()
            )
            : $relation->getRelated()->getTable();

        $tableAlias = sprintf('%s as %s', $relation->getRelated()->getTable(), $table);

        $query->join(
            $tableAlias,
            sprintf('%s.%s', $table, $relation->getOwnerKeyName()),
            '=',
            $relation->getQualifiedForeignKeyName(),
            'left'
        );

        $query->selectRaw(sprintf('"%s".%s', $table, $column->getProperty()).' as '.$column->getSortableProperty());

        $query->groupByRaw(
            sprintf('"%s".%s', $table, $relation->getOwnerKeyName())
        );

        return $table;
    }

    /**
     * @param  Builder  $query
     * @param  HasOne  $relation
     * @param  PropertyColumn  $column
     * @param  Model  $model
     * @param  string[]  $joinedTables
     * @return string
     */
    public function applyHasOneRelation(
        Builder $query,
        HasOne $relation,
        PropertyColumn $column,
        Model $model,
        array &$joinedTables = []
    ): string {
        $table = in_array($relation->getRelated()->getTable(), $joinedTables)
            ? sprintf(
                '%s:%s',
                $relation->getRelated()->getTable(),
                $column->getRelationName()
            )
            : $relation->getRelated()->getTable();

        $tableAlias = sprintf('%s as %s', $relation->getRelated()->getTable(), $table);

        $query->join(
            $tableAlias,
            sprintf('%s.%s', $table, $relation->getLocalKeyName()),
            '=',
            $relation->getQualifiedForeignKeyName(),
            'left'
        );

        $query->selectRaw(sprintf('"%s".%s', $table, $column->getProperty()).' as '.$column->getSortableProperty());

        $query->groupByRaw(
            sprintf('"%s".%s', $table, $relation->getLocalKeyName())
        );

        return $table;
    }

    /**
     * @param  Builder  $query
     * @param  BelongsToMany  $relation
     * @param  PropertyColumn  $column
     * @param  Model  $model
     * @param  string[]  $joinedTables
     * @return string[]
     */
    public function applyBelongsToManyRelation(
        Builder $query,
        BelongsToMany $relation,
        PropertyColumn $column,
        Model $model,
        array &$joinedTables = []
    ): array {
        /** @var BelongsToMany $relation */

        $intermediateTable = in_array($relation->getTable(), $joinedTables)
            ? sprintf(
                '%s:%s',
                $relation->getTable(),
                $column->getRelationName()
            )
            : $relation->getTable();

        $intermediateTableAlias = sprintf('%s as %s', $relation->getTable(), $intermediateTable);

        $query->join(
            $intermediateTableAlias,
            $relation->getQualifiedParentKeyName(),
            '=',
            sprintf('%s.%s', $intermediateTable, $relation->getForeignPivotKeyName()),
            'left'
        );

        $table = in_array($relation->getRelated()->getTable(), $joinedTables)
            ? sprintf(
                '%s:%s',
                $relation->getRelated()->getTable(),
                $column->getRelationName()
            )
            : $relation->getRelated()->getTable();

        $tableAlias = sprintf('%s as %s', $relation->getRelated()->getTable(), $table);

        $query->join(
            $tableAlias,
            sprintf('%s.%s', $intermediateTable, $relation->getRelatedPivotKeyName()),
            '=',
            sprintf('%s.%s', $table, $relation->getRelatedKeyName()),
            'left'
        );

        if ($column->hasCount()) {
            $this->addCountSelect($query, $column, $table, $model);
        } else {
            $query->selectRaw(
                sprintf(
                    'json_agg(%s) as %s',
                    sprintf('"%s".%s', $table, $column->getProperty()),
                    $column->getSortableProperty()
                )
            );
        }

        return [$intermediateTable, $table];
    }

    /**
     * @param  Builder  $query
     * @param  HasMany  $relation
     * @param  PropertyColumn  $column
     * @param  Model  $model
     * @param  string[]  $joinedTables
     * @return string
     */
    public function applyHasManyRelation(
        Builder $query,
        HasMany $relation,
        PropertyColumn $column,
        Model $model,
        array &$joinedTables = []
    ): string {
        /** @var HasMany $relation */

        $table = in_array($relation->getRelated()->getTable(), $joinedTables)
            ? sprintf(
                '%s:%s',
                $relation->getRelated()->getTable(),
                $column->getRelationName()
            )
            : $relation->getRelated()->getTable();

        $tableAlias = sprintf('%s as %s', $relation->getRelated()->getTable(), $table);

        $query->join(
            $tableAlias,
            $relation->getQualifiedParentKeyName(),
            '=',
            sprintf('%s.%s', $table, $relation->getForeignKeyName()),
            'left'
        );

        if ($column->hasCount()) {
            $this->addCountSelect($query, $column, $table, $model);
        } else {
            $query->selectRaw(
                sprintf(
                    'json_agg(%s) as %s',
                    sprintf('"%s".%s', $table, $column->getProperty()),
                    $column->getSortableProperty()
                )
            );
        }

        $query->groupByRaw(
            sprintf('"%s".%s', $table, $relation->getForeignKeyName())
        );

        return $table;
    }

    /**
     * @param  Builder  $query
     * @param  PropertyColumn  $column
     * @param  string  $table
     * @param  Model  $model
     * @return void
     */
    public function addCountSelect(Builder $query, PropertyColumn $column, string $table, Model $model): void
    {
        $query->selectRaw(
            sprintf(
                "COUNT(%s) as %s",
                $column->getIdColumn($table, $model),
                $column->getCountProperty()
            )
        );
    }

}
