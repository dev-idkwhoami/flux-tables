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

    public string $table;
    protected string $label;
    protected mixed $default = null;

    protected final function __construct(
        protected string $name,
    ) {
    }

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

    public function getLabel(): string
    {
        return $this->label;
    }

    public function label(string $label): Filter
    {
        $this->label = $label;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

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
    public abstract function apply(Builder $query): void;

    /**
     * @return string
     */
    public abstract function component(): string;

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return Session::has($this->filterValueSessionKey());
    }

    public abstract function renderPill(): string|HtmlString|View;

    /**
     * @return FilterValue
     */
    public function getValue(): FilterValue
    {
        return new FilterValue(Session::get($this->filterValueSessionKey()));
    }

    public function setValue(FilterValue $value): void
    {
        if ($value->getValue() === $this->getDefault()) {
            Session::forget($this->filterValueSessionKey());
        } else {
            Session::put($this->filterValueSessionKey(), $value->getValue());
        }
    }

}
