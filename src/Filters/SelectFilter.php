<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Override;

class SelectFilter extends Filter
{
    protected string $keyColumn;
    protected string $valueColumn;

    protected bool $multiple = false;

    public Collection $options;

    public static function make(string $name): static
    {
        return new static($name, view: 'filters.select', emptyValue: []);
    }

    #[Override]
    public function apply(Builder $query): Builder
    {
        if (!$this->callback) {
            return $query;
        }
        return ($this->callback)($query, collect($this->value)->toArray());
    }

    public function options(array|\Closure $options, string $keyColumn = 'id', string $valueColumn = 'name'): static
    {
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
        $this->options = $this->resolve($options);
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->value = $multiple
            ? $this->value ?? []
            : $this->value ?? null;
        return $this;
    }

    public function getKeyColumn(): string
    {
        return $this->keyColumn;
    }

    public function getValueColumn(): string
    {
        return $this->valueColumn;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function toLivewire(): array
    {
        return array_merge(parent::toLivewire(), [
            'options' => $this->options,
        ]);
    }

    public static function fromLivewire($value): static
    {
        $filter = parent::fromLivewire($value);
        $filter->view = 'filters.select';
        $filter->options = collect($value['options']);

        return $filter;
    }
}
