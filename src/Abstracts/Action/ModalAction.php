<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class ModalAction extends Action
{
    protected string $modalVariant = 'bare';
    protected string $modalClasses = 'max-w-3/5';
    protected string $modalPosition = 'right';
    protected string $component = '';

    public function modalUniqueName(mixed $id): string
    {
        return str($this->label)->lower()->snake()->append('-', md5($this->component), '-', strval($id), '-modal')->toString();
    }

    public function component(string $component): static
    {
        $this->component = $component;
        return $this;
    }

    public function modalClasses(string $classes): static
    {
        $this->modalClasses = $classes;
        return $this;
    }

    public function flyoutPosition(string $position): static
    {
        if (!in_array($position, ['left', 'right', 'top', 'bottom'])) {
            throw new \Exception('Flyout position must be left, right, top or bottom.');
        }

        $this->modalPosition = $position;
        return $this;
    }

    public function variantFlyout(): static
    {
        $this->modalVariant = 'flyout';
        return $this;
    }

    public function variantBare(): static
    {
        $this->modalVariant = 'bare';
        return $this;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function render(mixed $id): string|HtmlString|View|null
    {
        return view('flux-tables::modal.index', ['action' => $this, 'id' => $id]);
    }

}
