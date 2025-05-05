<?php

namespace Idkwhoami\FluxTables\Abstracts\Table;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Action\DirectAction;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Contracts\WireCompatible;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\HtmlString;
use Livewire\Wireable;

abstract class TableAction implements Wireable
{
    use WireCompatible;

    final protected function __construct(
        public string $name,
    ) {
    }

    public function configureAction(DirectAction $action): void
    {
        //
    }

    public abstract function modifyQuery(Builder $query): void;
    public abstract function hasAccess(?User $user, Model $model): bool;
    public abstract function handle(EloquentTable $table, mixed $id): void;
    public abstract function render(Action $action, mixed $id): string|HtmlString|View|null;

}
