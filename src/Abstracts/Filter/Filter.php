<?php

namespace Idkwhoami\FluxTables\Abstracts\Filter;

use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

abstract class Filter implements Wireable
{
    use WireCompatible;

    public string $table = '';
    protected string $label = '';
    protected mixed $default = null;

    final protected function __construct(
        protected string $name,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $table
     * @return $this
     */
    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param  string  $label
     * @return $this
     */
    public function label(string $label): Filter
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @param  mixed  $default
     * @return $this
     */
    public function default(mixed $default): Filter
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function filterValueSessionKey(): string
    {
        return "flux-tables::{$this->table}:filters:{$this->name}:value";
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    abstract public function apply(Builder $query): void;

    /**
     * @return string
     */
    abstract public function component(): string;

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return Session::has($this->filterValueSessionKey());
    }

    /**
     * @return string|HtmlString|View
     */
    abstract public function renderPill(): string|HtmlString|View;

    /**
     * @return FilterValue
     */
    public function getValue(): FilterValue
    {
        return new FilterValue(Session::get($this->filterValueSessionKey()));
    }

    /**
     * @param  FilterValue  $value
     * @return void
     */
    public function setValue(FilterValue $value): void
    {
        if ($value->getValue() === $this->getDefault()) {
            Session::forget($this->filterValueSessionKey());
        } else {
            Session::put($this->filterValueSessionKey(), $value->getValue());
        }
    }

}
