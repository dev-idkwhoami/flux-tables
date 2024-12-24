<?php

namespace Idkwhoami\FluxTables\Columns;

use Idkwhoami\FluxTables\Enums\ColumnAlignment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Wireable;

abstract class Column implements Wireable
{
    protected function __construct(
        protected ?string $view = null,
        protected ?string $name = null,
        protected ?\Closure $transform = null,
        protected ?string $label = null,
        protected bool $sortable = false,
        protected bool $searchable = false,
        protected ColumnAlignment $alignment = ColumnAlignment::Left,
    ) {}

    public function render(Model $row): View
    {
        return view("flux-tables::{$this->view}", ['column' => $this, 'row' => $row]);
    }

    public function alignment(ColumnAlignment $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function transform(\Closure $transform): static
    {
        $this->transform = $transform;

        return $this;
    }

    public function getAlignment(): ColumnAlignment
    {
        return $this->alignment;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function hasTransform(): bool
    {
        return $this->transform !== null;
    }

    public function getTransform(): ?\Closure
    {
        return $this->transform;
    }

    public function getView(): string
    {
        return $this->view;
    }
}
