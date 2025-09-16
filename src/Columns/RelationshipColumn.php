<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Contracts\View\View;

class RelationshipColumn extends Column
{
    protected string $relationship;
    protected string $attribute = 'name';
    protected string $separator = ', ';
    protected ?int $limit = null;
    protected bool $withCount = false;
    protected ?string $countSuffix = null;
    protected bool $clickable = false;
    protected ?string $routeName = null;
    protected ?string $routeKey = 'id';
    protected bool $tooltip = false;
    protected array $eagerLoad = [];

    public function relationship(string $relationship): static
    {
        $this->relationship = $relationship;
        return $this;
    }

    public function attribute(string $attribute): static
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function separator(string $separator): static
    {
        $this->separator = $separator;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function withCount(bool $withCount = true, ?string $suffix = null): static
    {
        $this->withCount = $withCount;
        $this->countSuffix = $suffix ?? ' items';
        return $this;
    }

    public function clickable(string $routeName, string $routeKey = 'id'): static
    {
        $this->clickable = true;
        $this->routeName = $routeName;
        $this->routeKey = $routeKey;
        return $this;
    }

    public function tooltip(bool $tooltip = true): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function eagerLoad(array $relations): static
    {
        $this->eagerLoad = $relations;
        return $this;
    }

    public function applySort(Builder $query, string $direction): void
    {
        if (!$this->isSortable()) {
            return;
        }

        // Join the related table for sorting
        $relation = $this->getRelationInstance($query);

        if (!$relation) {
            return;
        }

        $relatedTable = $relation->getRelated()->getTable();
        $relatedKey = $relation->getRelated()->getKeyName();
        $foreignKey = $this->getForeignKey($relation);
        $localKey = $this->getLocalKey($relation, $query);

        // Create an alias to avoid conflicts
        $alias = $this->relationship . '_for_sort';

        // Check if join already exists
        $existingJoin = collect($query->getQuery()->joins ?? [])->first(function ($join) use ($alias) {
            return $join->table === $alias;
        });

        if (!$existingJoin) {
            $query->leftJoin("{$relatedTable} as {$alias}", $localKey, '=', "{$alias}.{$relatedKey}");
        }

        $query->orderBy("{$alias}.{$this->attribute}", $direction);
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $query->orWhereHas($this->relationship, function (Builder $relationQuery) use ($search) {
            $relationQuery->where($this->attribute, 'ILIKE', "%{$search}%");

            // Also search in eager loaded relationships if specified
            foreach ($this->eagerLoad as $eagerRelation) {
                $relationQuery->orWhereHas($eagerRelation, function (Builder $eagerQuery) use ($search) {
                    $eagerQuery->where($this->attribute, 'ILIKE', "%{$search}%");
                });
            }
        });
    }

    protected function getRelationInstance(Builder $query): ?Relation
    {
        $model = $query->getModel();

        if (!method_exists($model, $this->relationship)) {
            return null;
        }

        return $model->{$this->relationship}();
    }

    protected function getForeignKey(Relation $relation): string
    {
        if (method_exists($relation, 'getForeignKeyName')) {
            return $relation->getForeignKeyName();
        }

        if (method_exists($relation, 'getForeignKey')) {
            return $relation->getForeignKey();
        }

        return 'id';
    }

    protected function getLocalKey(Relation $relation, Builder $query): string
    {
        $table = $query->getModel()->getTable();

        if (method_exists($relation, 'getLocalKeyName')) {
            return "{$table}." . $relation->getLocalKeyName();
        }

        if (method_exists($relation, 'getOwnerKey')) {
            return "{$table}." . $relation->getOwnerKey();
        }

        return "{$table}.id";
    }

    protected function getRelatedValues(object $record): array
    {
        $related = $this->getNestedValue($record, $this->relationship);

        if (is_null($related)) {
            return [];
        }

        // Handle single relationship
        if (!is_iterable($related)) {
            $value = $this->getNestedValue($related, $this->attribute);
            return $value ? [$value] : [];
        }

        // Handle collection of relationships
        $values = [];
        foreach ($related as $item) {
            $value = $this->getNestedValue($item, $this->attribute);
            if ($value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    protected function getNestedValue(mixed $object, string $attribute): mixed
    {
        $keys = explode('.', $attribute);
        $current = $object;

        foreach ($keys as $key) {
            if (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
            } elseif (is_object($current) && method_exists($current, $key)) {
                $current = $current->$key;
            } elseif (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    protected function formatRelationshipValues(array $values, object $record): string
    {
        if (empty($values)) {
            return '<span class="text-gray-400">—</span>';
        }

        // Apply limit if specified
        $displayValues = $this->limit ? array_slice($values, 0, $this->limit) : $values;
        $hasMore = $this->limit && count($values) > $this->limit;

        $formattedValues = array_map('htmlspecialchars', $displayValues);

        if ($this->clickable && $this->routeName) {
            $formattedValues = array_map(function ($value) use ($record) {
                $related = $this->getNestedValue($record, $this->relationship);
                $routeKey = $this->getNestedValue($related, $this->routeKey) ?? $related->id ?? null;

                if ($routeKey) {
                    $url = route($this->routeName, [$routeKey]);
                    return sprintf('<a href="%s" class="text-blue-600 hover:text-blue-800 underline">%s</a>', $url, $value);
                }

                return $value;
            }, $formattedValues);
        }

        $result = implode($this->separator, $formattedValues);

        if ($hasMore) {
            $remaining = count($values) - $this->limit;
            $result .= sprintf(' <span class="text-gray-500 text-sm">+%d more</span>', $remaining);
        }

        if ($this->withCount) {
            $count = count($values);
            $result .= sprintf(' <span class="text-gray-500 text-sm">(%d%s)</span>', $count, $this->countSuffix);
        }

        if ($this->tooltip && $hasMore) {
            $allValues = implode($this->separator, array_map('htmlspecialchars', $values));
            return sprintf('<span title="%s">%s</span>', $allValues, $result);
        }

        return $result;
    }

    public function render(object $record): View
    {
        $values = $this->getRelatedValues($record);

        if (empty($values)) {
            return view('flux-tables::columns.relationship', ['values' => []]);
        }

        // Apply limit if specified
        $displayValues = $this->limit ? array_slice($values, 0, $this->limit) : $values;
        $hasMore = $this->limit && count($values) > $this->limit;
        $remaining = $hasMore ? count($values) - $this->limit : 0;

        // Prepare route keys if clickable
        $routeKeys = [];
        if ($this->clickable && $this->routeName) {
            foreach ($displayValues as $index => $value) {
                $related = $this->getNestedValue($record, $this->relationship);
                $routeKeys[$index] = $this->getNestedValue($related, $this->routeKey) ?? $related->id ?? null;
            }
        }

        return view('flux-tables::columns.relationship', [
            'values' => $values,
            'formattedValues' => $displayValues,
            'hasMore' => $hasMore,
            'remaining' => $remaining,
            'totalCount' => count($values),
            'separator' => $this->separator,
            'clickable' => $this->clickable,
            'routeName' => $this->routeName,
            'routeKeys' => $routeKeys,
            'withCount' => $this->withCount,
            'countSuffix' => $this->countSuffix,
        ]);
    }
}
