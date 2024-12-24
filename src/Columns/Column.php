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
        protected bool $toggleable = false,
        public bool $toggled = false,
        protected ColumnAlignment $alignment = ColumnAlignment::Left,
    ) {}

    public function render(Model $row): ?View
    {
        if (! $this->isToggleable() || $this->isToggled()) {
            return view("flux-tables::{$this->view}", ['column' => $this, 'row' => $row]);
        }

        return null;
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

    public function toggleable(bool $toggleable = true, $default = true): static
    {
        $this->toggleable = $toggleable;
        $this->toggled = $default;

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

    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function isToggled(): bool
    {
        return $this->toggled;
    }

    public function loadToggleStateFromSession(string $table): void
    {
        $this->toggled = session("table:$table:column:$this->name", $this->toggled);
    }

    public function setToggleStateInSession(string $table): void
    {
        session()->put("table:$table:column:$this->name", $this->toggled);
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

    public function toLivewire(): array
    {
        return [
            'view' => $this->view,
            'name' => $this->name,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'toggleable' => $this->toggleable,
            'toggled' => $this->toggled,
            'alignment' => $this->alignment,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['view'],
            $value['name'],
            null,
            $value['label'],
            $value['sortable'],
            $value['searchable'],
            $value['toggleable'],
            $value['toggled'],
            $value['alignment'],
        );
    }
}
