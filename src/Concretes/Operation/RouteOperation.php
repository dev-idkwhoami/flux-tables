<?php

namespace Idkwhoami\FluxTables\Concretes\Operation;

use Closure;
use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Table\Operation;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;

class RouteOperation extends Operation
{
    protected ?Closure $modelQuery = null;
    protected ?Closure $route = null;
    protected bool $navigate = false;
    protected string $target = '_self';

    public function modelQuery(Closure $modelQuery): RouteOperation
    {
        $this->modelQuery = $modelQuery;
        return $this;
    }

    public function route(string|Closure $route): RouteOperation
    {
        $this->route = is_string($route) ? fn () => $route : $route;
        return $this;
    }

    public function opensInNewTab(): RouteOperation
    {
        return $this->target('_blank');
    }

    public function target(string $target): RouteOperation
    {
        $this->target = $target;
        return $this;
    }

    public function navigate(bool $navigate = true): RouteOperation
    {
        $this->navigate = $navigate;
        return $this;
    }

    public function render(Action $action, mixed $id): string|HtmlString|View|null
    {
        return view('flux-tables::operation.route', [
            'action' => $action,
            'id' => $id,
            'route' => $this->route,
            'target' => $this->target,
            'model' => $this->modelQuery?->call($this, $id) ?? null,
            'attributes' => new ComponentAttributeBag(['wire:navigate' => $this->navigate])
        ]);
    }

    public function hasAccess(?User $user, Model $model): bool
    {
        return true;
    }

    public function handle(EloquentTable $table, mixed $id): void
    {
        //
    }

    public function modifyQuery(Builder $query): void
    {
        //
    }
}
