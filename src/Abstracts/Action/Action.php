<?php

namespace Idkwhoami\FluxTables\Abstracts\Action;

use Closure;
use Idkwhoami\FluxTables\Contracts\HasContext;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Wireable;

abstract class Action implements Wireable, HasContext
{
    use WireCompatible;

    protected string $label = '';
    protected string $icon = '';

    protected bool $link = false;

    protected Closure|bool $visible = true;
    protected Closure|bool $access = true;

    final protected function __construct(
        protected string $name,
    ) {
    }

    public function visible(Closure|bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function shouldBeVisible(Model $model): bool
    {
        if ($this->visible instanceof Closure) {
            if (!Context::hasHidden($this->contextKey('visible', $model->{$model->getKeyName()}))) {
                Context::addHidden($this->contextKey('visible', $model->{$model->getKeyName()}), $this->visible->call($this, $model, $this));
            }

            return Context::getHidden($this->contextKey('visible', $model->{$model->getKeyName()}));
        }

        return $this->visible;
    }

    public function access(Closure|bool $access = true): static
    {
        $this->access = $access;
        return $this;
    }

    public function hasAccess(?User $user, Model $model): bool
    {
        if ($this->access instanceof Closure) {
            if (!Context::hasHidden($this->contextKey('access', $model->{$model->getKeyName()}))) {
                Context::addHidden($this->contextKey('access', $model->{$model->getKeyName()}), $this->access->call($this, $user, $model, $this));
            }
            return Context::getHidden($this->contextKey('access', $model->{$model->getKeyName()}));
        }

        return $this->access;
    }

    public function contextKey(string $key, mixed $id = null): string
    {
        $id ??= Str::random(6);
        return "flux-tables::context::actions::{$this->name}::{$key}::{$id}::value";
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

    public function link(bool $link = true): static
    {
        $this->link = $link;
        return $this;
    }

    abstract public function render(mixed $id): string|HtmlString|View|null;

}
