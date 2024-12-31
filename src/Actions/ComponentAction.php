<?php

namespace Idkwhoami\FluxTables\Actions;

use Idkwhoami\FluxTables\Enums\ActionPosition;

class ComponentAction extends Action
{
    public array $form = [];

    public ?string $icon = null;

    public string $variant = 'primary';

    public bool $dismissable = false;

    protected array $rules = [];

    protected function __construct(
        string $name,
        ?string $label = null,
        protected ?string $component = null,
        ?ActionPosition $position = null,
        string $view = 'actions.form',
        ?string $group = null
    ) {
        parent::__construct($name, $view, $label, $position, $group);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function component(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    public function dismissable(bool $dismissable = true): static
    {
        $this->dismissable = $dismissable;

        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function rules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function toLivewire(): array
    {
        return array_merge(parent::toLivewire(), [
            'form' => $this->form,
            'rules' => $this->rules,
            'dismissable' => $this->dismissable,
            'variant' => $this->variant,
            'icon' => $this->icon,
            'component' => $this->component,
        ]);
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['name'] ?? null,
            $value['label'] ?? null,
            $value['component'] ?? null,
            isset($value['position']) ? ActionPosition::from($value['position']) : null,
            $value['view'] ?? null,
            $value['group'] ?? null,
        );
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function isDismissable(): bool
    {
        return $this->dismissable;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
