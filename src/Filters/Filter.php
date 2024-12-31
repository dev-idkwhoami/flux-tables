<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Contracts\Database\Query\Builder;
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
        public mixed $emptyValue = null,
    ) {}

    public function getFilterValueSessionKey(string $table): string
    {
        $class = strtolower(class_basename($this));

        return "table:$table:filters:$class:value:$this->name";
    }

    public function loadValueFromSession(string $table): void
    {
        $this->value = session($this->getFilterValueSessionKey($table), $this->value);
    }

    public function setValueInSession(string $table): void
    {
        session()->put($this->getFilterValueSessionKey($table), $this->value);
    }

    public function resetValue(string $table): void
    {
        $this->value = $this->emptyValue;
        session()->forget($this->getFilterValueSessionKey($table));
    }

    public function apply(Builder $query): \Illuminate\Contracts\Database\Query\Builder
    {
        if (! $this->callback) {
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
        return ! empty($this->value);
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

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'view' => $this->view,
            'callback' => serialize(new SerializableClosure($this->callback)),
            'value' => $this->value,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['name'],
            $value['label'],
            $value['view'],
            unserialize($value['callback'])->getClosure(),
            $value['value']
        );
    }

    protected function resolve($value): mixed
    {
        if ($value instanceof \Closure) {
            return $value();
        }

        return $value;
    }
}
