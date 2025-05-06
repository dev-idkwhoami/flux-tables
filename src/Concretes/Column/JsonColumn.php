<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Idkwhoami\FluxTables\Enums\JsonPropertyType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class JsonColumn extends PropertyColumn
{

    protected string $propertyType = 'text';
    protected array $jsonPath = [];

    public function path(array|string $path): JsonColumn
    {
        $this->jsonPath = is_string($path) ? explode('.', $path) : $path;
        return $this;
    }

    public function type(JsonPropertyType $type): JsonColumn
    {
        $this->propertyType = $type->value;
        return $this;
    }

    public function getType(): string
    {
        return $this->propertyType;
    }

    public function getJsonPropertyAlias(): string
    {
        return sprintf("%s_%s", $this->property, join('_', $this->jsonPath));
    }

    public function jsonProperty(): string
    {
        return sprintf("%s::json#>>'{%s}'", $this->property, implode(',', $this->jsonPath));
    }

    public function getProperty(): string
    {
        return sprintf("%s as \"%s\"", $this->jsonProperty(), $this->getJsonPropertyAlias());
    }

    public function getOrderByColumn(): string
    {
        return $this->jsonProperty();
    }

    public function getSortableProperty(): string
    {
        return $this->hasRelation()
            ? strtolower($this->relation.self::RELATION_SPACER.strtolower($this->getProperty()))
            : strtolower($this->getProperty());
    }

    public function getValue(Model $model): mixed
    {
        $rawValue = $this->hasRelation() && !$this->hasCount()
            ? $this->getRelationValue($model)
            : $model->{$this->getJsonPropertyAlias()};

        if ($this->transform) {
            return $this->transform->call($this, $rawValue, $model);
        }

        return $rawValue;
    }

    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if (!($value instanceof Model)) {
            throw new \Exception('Unable to render text column without a valid value');
        }

        /*dump([
            $value,
            $this->getValue($value)
        ]);*/

        return $this->getValue($value) ?? '';
    }
}
