<?php

namespace Idkwhoami\FluxTables\Columns;

use Idkwhoami\FluxTables\Enums\ColumnAlignment;

class BooleanColumn extends Column
{

    public function __construct(
        ?string $view = null,
        ?string $name = null,
        ?string $column = null,
        mixed $placeholder = null,
        protected ?string $iconTrue = null,
        protected ?string $iconFalse = null,
        protected ?string $colorTrue = null,
        protected ?string $colorFalse = null,
        ?\Closure $transform = null,
        ?string $label = null,
        bool $sortable = false,
        bool $searchable = false,
        bool $toggleable = false,
        bool $toggled = false,
        ColumnAlignment $alignment = ColumnAlignment::Left
    ) {
        parent::__construct($view, $name, $column, $placeholder, $transform, $label, $sortable, $searchable,
            $toggleable, $toggled, $alignment);
    }

    public function useIcons(): bool
    {
        return $this->iconTrue !== null && $this->iconFalse !== null;
    }

    public function getColorFalse(): ?string
    {
        return $this->colorFalse;
    }

    public function getColorTrue(): ?string
    {
        return $this->colorTrue;
    }

    public function getIconFalse(): ?string
    {
        return $this->iconFalse;
    }

    public function getIconTrue(): ?string
    {
        return $this->iconTrue;
    }

    public function icons(string $iconTrue = 'circle-check', string $iconFalse = 'circle-x'): static
    {
        $this->iconTrue = $iconTrue;
        $this->iconFalse = $iconFalse;
        return $this;
    }

    public function colors(string $colorTrue = 'text-green-400', string $colorFalse = 'text-red-400'): static
    {
        $this->colorTrue = $colorTrue;
        $this->colorFalse = $colorFalse;
        return $this;
    }

    public function toLivewire(): array
    {
        return array_merge(parent::toLivewire(), [
            'iconTrue' => $this->iconTrue,
            'iconFalse' => $this->iconFalse,
            'colorTrue' => $this->colorTrue,
            'colorFalse' => $this->colorFalse,
        ]);
    }

    public static function fromLivewire($value): static
    {
        return parent::fromLivewire($value)->fill(__CLASS__, $value);
    }

    public static function make(string $name): static
    {
        return new static('columns.boolean', $name);
    }
}
