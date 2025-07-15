<?php

namespace Idkwhoami\FluxTables\Traits;

trait HasTableCreate
{
    protected ?string $createComponent = null;
    protected ?string $createText = null;

    protected string $createModalClasses = 'w-full';
    protected ?string $createModalVariant = 'default';
    protected ?string $createModalPosition = null;
    protected bool $createModalDismissible = false;

    public function getCreateComponent(): ?string
    {
        return $this->createComponent;
    }

    public function getCreateModalName(): string
    {
        return "{$this->name}-modal-create";
    }

    public function isCreateModalDismissible(): bool
    {
        return $this->createModalDismissible;
    }

    public function getCreateModalClasses(): string
    {
        return $this->createModalClasses;
    }

    public function getCreateModalPosition(): ?string
    {
        return $this->createModalPosition;
    }

    public function getCreateModalVariant(): ?string
    {
        return $this->createModalVariant;
    }

    public function getCreateText(): ?string
    {
        return $this->createText
            ?? __(
                'flux-tables::actions/create.label',
                ['model' => \str(class_basename($this->eloquentModel))->singular()->lower()]
            );
    }

    public function createComponent(?string $create): static
    {
        $this->createComponent = $create;
        return $this;
    }

    public function createText(?string $createText): static
    {
        $this->createText = $createText;
        return $this;
    }

    public function createModalClasses(string $classes): static
    {
        $this->createModalClasses = $classes;
        return $this;
    }

    public function createDismissible(bool $dismissible = true): static
    {
        $this->createModalDismissible = $dismissible;
        return $this;
    }

    public function createModalFlyoutPosition(?string $position): static
    {
        if (!in_array($position, ['left', 'right', 'top', 'bottom'])) {
            throw new \Exception('Flyout position must be left, right, top or bottom.');
        }

        $this->createModalPosition = $position;
        return $this;
    }

    public function createModalVariant(?string $variant): static
    {
        $this->createModalVariant = $variant;
        return $this;
    }

    public function createModalVariantFlyout(): static
    {
        $this->createModalVariant = 'flyout';
        return $this;
    }

    public function createModalVariantBare(): static
    {
        $this->createModalVariant = 'bare';
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCreate(): bool
    {
        return !empty($this->createComponent);
    }

}
