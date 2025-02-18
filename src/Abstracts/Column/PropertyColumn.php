<?php

namespace Idkwhoami\FluxTables\Abstracts\Column;

use Illuminate\Database\Eloquent\Model;

abstract class PropertyColumn extends Column
{
    public const string RELATION_SPACER = '_';

    protected string $property = '';
    protected string $relation = '';

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

    /**
     * @param  string  $relation
     * @return $this
     */
    public function relation(string $relation): PropertyColumn
    {
        $this->relation = $relation;
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

}
