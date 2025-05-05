<?php

namespace Idkwhoami\FluxTables\Concretes\Action;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Table\TableAction;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;

class RouteAction extends TableAction
{
    protected string $route;

    public function route(string $route): RouteAction
    {
        $this->route = $route;
        return $this;
    }

    public function render(Action $action, mixed $id): string|HtmlString|View|null
    {
        return view('flux-tables::action.route', [
            'action' => $action,
            'id' => $id,
            'route' => $this->route,
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
