<?php

namespace Idkwhoami\FluxTables\Concretes\Action;

use Idkwhoami\FluxTables\Abstracts\Action\Action;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Contracts\TableAction;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class RestoreAction implements TableAction
{

    public function hasAccess(?User $user, Model $model): bool
    {
        return $user->can('restore', $model);
    }

    public function handle(EloquentTable $table, mixed $id): void
    {
        $model = $table->eloquentModel::withTrashed()->findorFail($id);

        if (Gate::allows('delete', $model)) {
            $model->restore();
        }
    }

    public function render(Action $action, mixed $id): string|HtmlString|View|null
    {
        if ($action->isLink()) {
            return Blade::render('<flux:button
            variant="ghost"
            size="xs"
            class="hover:text-red-500"
            :key="\'action-{{ $id }}\'"
            icon="{{ $action->getIcon() }}"
            wire:click.prevent="callAction(\'{{ $id }}\', \'{{ base64_encode($action->getAction()) }}\')">
                {{ $action->getLabel() }}
            </flux:button>',
                ['id' => $id, 'action' => $action]
            );
        }
        return Blade::render('not link');
    }

    public function modifyQuery(Builder $query): void
    {
        $column = $query->qualifyColumn('deleted_at');
        if (!str_contains($query->toRawSql(), $column)) {
            $query->selectRaw($column);
        }
    }
}
