<?php

namespace Idkwhoami\FluxTables\Concretes\Action;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Abstracts\Action\DirectAction;
use Idkwhoami\FluxTables\Abstracts\Table\TableAction;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class DeleteAction extends TableAction
{
    public function configureAction(DirectAction $action): void
    {
        $action->variant('danger');
    }

    public function hasAccess(?User $user, Model $model): bool
    {
        if (!$user) {
            return false;
        }

        return $user->can('delete', $model);
    }

    public function handle(EloquentTable $table, mixed $id): void
    {
        $model = $table->eloquentModel::findOrFail($id);

        if (Gate::allows('delete', $model)) {
            $model->delete();
        }
    }

    public function render(Action $action, mixed $id): string|HtmlString|View|null
    {
        return view('flux-tables::action.delete', compact(['action', 'id']));
    }

    public function modifyQuery(Builder $query): void
    {
        $column = $query->qualifyColumn('deleted_at');
        if (!str_contains($query->toRawSql(), $column)) {
            $query->selectRaw($column);
        }
    }
}
