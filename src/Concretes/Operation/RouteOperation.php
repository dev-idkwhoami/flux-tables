<?php

namespace Idkwhoami\FluxTables\Concretes\Operation;

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
    protected string $route;
    protected bool $navigate = false;

    public function route(string $route): RouteOperation
    {
        $this->route = $route;
        return $this;
    }

    public function navigate(bool $navigate = true): RouteOperation
    {
        $this->navigate = $navigate;
        return $this;
    }

    public function render(Action $action, mixed $id): string|HtmlString|View|null
    {
        return view('flux-tables::action.route', [
            'action' => $action,
            'id' => $id,
            'route' => $this->route,
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
