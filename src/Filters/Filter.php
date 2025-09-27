<?php

namespace Idkwhoami\FluxTables\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

abstract class Filter
{
    protected string $name;
    protected string $label;
    protected string $column;
    protected mixed $value = null;
    protected mixed $default = null;
    protected ?string $description = null;
    protected ?Closure $visibility = null;
    protected array $attributes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = str($name)->title()->replace('_', ' ')->toString();
        $this->column = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Set the display label for this filter
     */
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set the database column to filter on
     */
    public function column(string $column): static
    {
        $this->column = $column;
        return $this;
    }


    /**
     * Set the default value for this filter
     */
    public function default(mixed $default): static
    {
        $this->default = $default;
        $this->value = $default;
        return $this;
    }

    /**
     * Set a description for this filter
     */
    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set visibility condition using a closure
     */
    public function visible(bool|Closure $visible = true): static
    {
        if ($visible instanceof Closure) {
            $this->visibility = $visible;
        } else {
            $this->visibility = fn() => $visible;
        }
        return $this;
    }

    /**
     * Set additional attributes for the filter component
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getColumn(): string
    {
        return $this->column;
    }


    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function isVisible(?object $context = null): bool
    {
        if ($this->visibility instanceof Closure) {
            return ($this->visibility)($context);
        }
        return true;
    }


    public function hasValue(): bool
    {
        return $this->value !== null && $this->value !== '' && $this->value !== [];
    }

    public function isActive(): bool
    {
        return $this->hasValue();
    }

    public function reset(): void
    {
        $this->value = $this->default;
    }

    // Abstract methods that must be implemented by concrete filter classes
    abstract public function render(): View|string;

    abstract public function apply(Builder $query): Builder;

    abstract public function getValueDescription(): string;

    abstract public function validate(): bool;

    /**
     * Serialize filter data for storage
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'type' => static::class,
        ];
    }

    /**
     * Restore filter from serialized data
     */
    public function fromArray(array $data): static
    {
        if (isset($data['value'])) {
            $this->value = $data['value'];
        }
        return $this;
    }
}