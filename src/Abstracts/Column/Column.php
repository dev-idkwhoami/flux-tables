<?php

namespace Idkwhoami\FluxTables\Abstracts\Column;

use Closure;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Contracts\HasContext;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

abstract class Column implements Wireable, HasContext
{
    use WireCompatible;

    protected ?string $table = null;

    /**
     * @var string|null
     */
    protected ?string $label = null;
    /**
     * @var bool
     */
    protected bool $sortable = false;
    /**
     * @var bool
     */
    protected bool $searchable = false;
    /**
     * @var bool
     */
    protected bool $toggleable = true;
    /**
     * @var Closure|bool
     */
    protected Closure|bool $visible = true;

    final protected function __construct(
        protected string $name,
    ) {
    }

    public function tableInitialized(Table $table): void
    {
        $this->table = $table->name;
    }

    /**
     * @param  string|null  $label
     * @return $this
     */
    public function label(?string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param  string  $name
     * @return $this
     */
    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param  bool  $searchable
     * @return $this
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * @param  bool  $sortable
     * @return $this
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @param  bool  $toggleable
     * @return $this
     */
    public function toggleable(bool $toggleable): static
    {
        $this->toggleable = $toggleable;
        return $this;
    }

    public function visible(bool|Closure $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getOrderByColumn(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function contextKey(string $key, mixed $id = null): string
    {
        return "flux-tables::context::columns::{$this->name}::{$key}::value";
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

    /**
     * @param  object  $value
     * @return string|HtmlString|View|null
     */
    abstract public function render(object $value): string|HtmlString|View|null;
}
