<?php

namespace Idkwhoami\FluxTables\Abstracts\Filter;

use Closure;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Filter\FilterValue;
use Idkwhoami\FluxTables\Contracts\HasContext;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

abstract class Filter implements Wireable, HasContext
{
    use WireCompatible;

    public string $table = '';
    protected string $label = '';
    protected mixed $default = null;
    /**
     * @var Closure|bool
     */
    protected Closure|bool $visible = true;

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

    public function visible(Closure|bool $visible): Filter
    {
        $this->visible = $visible;
        return $this;
    }

    public function getVisible(): bool|Closure
    {
        return $this->visible;
    }

    public function shouldBeVisible(Table $table): bool
    {
        if ($this->visible instanceof Closure) {
            if (!Context::hasHidden($this->contextKey('visible'))) {
                Context::addHidden($this->contextKey('visible'), $this->visible->call($this, $table, $this));
            }
            return Context::getHidden($this->contextKey('visible'));
        }

        return $this->visible;
    }

    public function contextKey(string $key, mixed $id = null): string
    {
        return "flux-tables::context::filters::{$this->name}::{$key}::value";
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
