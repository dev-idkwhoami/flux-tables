<?php

namespace Idkwhoami\FluxTables\Abstracts\Column;

use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

abstract class Column implements Wireable
{
    use WireCompatible;

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

    final protected function __construct(
        protected string $name,
    ) {
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

    /**
     * @return bool
     */
    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    /**
     * @param  object  $value
     * @return string|HtmlString|View|null
     */
    abstract public function render(object $value): string|HtmlString|View|null;
}
