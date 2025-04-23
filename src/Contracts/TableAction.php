<?php

namespace Idkwhoami\FluxTables\Contracts;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;

interface TableAction
{
    public function modifyQuery(Builder $query): void;
    public function hasAccess(?User $user, Model $model): bool;
    public function handle(EloquentTable $table, mixed $id): void;
    public function render(Action $action, mixed $id): string|HtmlString|View|null;

}
