<?php

namespace Idkwhoami\FluxTables\Actions;

use Idkwhoami\FluxTables\Enums\ActionPosition;
use Livewire\Wireable;

abstract class Action implements Wireable
{
    public function __construct(
        protected string $name,
        protected string $view,
        protected ?string $label,
        protected ?ActionPosition $position,
        protected ?string $group = null,
    ) {}

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'view' => $this->view,
            'label' => $this->label,
            'position' => $this->position?->value,
            'group' => $this->group,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['name'],
            $value['view'],
            $value['label'],
            isset($value['position']) ? ActionPosition::from($value['position']) : null,
            $value['group'] ?? null,
        );
    }

    public function group(string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function position(ActionPosition $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPosition(): ?ActionPosition
    {
        return $this->position;
    }

    public function getView(): string
    {
        return $this->view;
    }
}
