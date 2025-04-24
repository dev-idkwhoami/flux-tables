<?php

namespace Idkwhoami\FluxTables\Abstracts\Column;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class PropertyColumn extends Column
{
    public const string RELATION_SPACER = '_';

    protected ?Closure $transform = null;

    protected bool $count = false;

    protected string $property = '';
    protected string $relation = '';

    protected string $default = '';

    /**
     * @param  string  $property
     * @return $this
     */
    public function property(string $property): PropertyColumn
    {
        if (str_contains($property, '.')) {
            throw new \InvalidArgumentException("Property {$property} cannot contain a dot");
        }

        $this->property = $property;
        return $this;
    }

    public function transform(?Closure $transform): PropertyColumn
    {
        $this->transform = $transform;
        return $this;
    }

    public function getTransform(): ?Closure
    {
        return $this->transform;
    }

    /**
     * @param  string  $relation
     * @return $this
     */
    public function relation(string $relation): PropertyColumn
    {
        $this->relation = $relation;
        return $this;
    }

    public function count(bool $count = true): PropertyColumn
    {
        $this->count = $count;
        return $this;
    }

    public function hasCount(): bool
    {
        return $this->count;
    }

    public function getIdColumn(string $table, Model $model): string
    {
        return sprintf('"%s".%s', $table, $model->getKeyName());
    }

    public function getCountProperty(): string
    {
        return sprintf("%s_count", $this->getRelationName());
    }

    public function default(string $default): PropertyColumn
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @param  Model  $model
     * @return mixed
     */
    public function getRelationValue(Model $model): mixed
    {
        return $model->{$this->getSortableProperty()};
    }

    public function getValue(Model $model): mixed
    {
        $rawValue = $this->hasRelation() && !$this->hasCount()
            ? $this->getRelationValue($model)
            : $model->{$this->property};

        if ($this->transform) {
            return $this->transform->call($this, $rawValue, $model);
        }

        return $rawValue;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getSortableProperty(): string
    {
        return $this->hasRelation()
            ? strtolower($this->relation.self::RELATION_SPACER.strtolower($this->property))
            : strtolower($this->property);
    }

    /**
     * @return bool
     */
    public function hasRelation(): bool
    {
        return !empty($this->relation);
    }

    /**
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relation;
    }

    public function getDefault(): string
    {
        return $this->default ?? '';
    }

}
