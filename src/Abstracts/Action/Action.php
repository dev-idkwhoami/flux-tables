<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

class Action implements Wireable
{
    use WireCompatible;

    protected string $action = '';
    protected string $label = '';
    protected string $icon = '';

    protected bool $link = true;

    protected ?\Closure $visibleClosure = null;

    protected function __construct(
        protected string $name,
    ) {
    }

    public function visible(\Closure $visible): static
    {
        $this->visibleClosure = $visible;
        return $this;
    }

    public function isVisible(Model $model): bool
    {
        if (!isset($this->visibleClosure)) {
            return true;
        }

        return ($this->visibleClosure)($model);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function action(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function isLink(): bool
    {
        return $this->link;
    }

    public function link(bool $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function hasAccess(?User $user, Model $model): bool
    {
        return (new $this->action)->hasAccess($user, $model);
    }

    public function render(mixed $id): string|HtmlString|View|null
    {
        return (new $this->action)->render($this, $id);
    }

}
