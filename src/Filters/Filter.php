<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

abstract class Filter implements Wireable
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $label = null,
        protected ?string $view = null,
        protected ?\Closure $callback = null,
        public mixed $value = null,
    ) {
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->callback) {
            return $query;
        }

        return ($this->callback)($query, $this->value);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function hasValue(): bool
    {
        return !empty($this->value);
    }

    public function callback(\Closure $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    public function getCallback(): ?\Closure
    {
        return $this->callback;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
