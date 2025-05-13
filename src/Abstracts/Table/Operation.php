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
use Illuminate\Support\Str;
use Livewire\Wireable;

abstract class Operation implements Wireable
{
    use WireCompatible;

    final public function __construct(
        public ?string $name = null,
    ) {
        $this->name ??= Str::snake(class_basename($this));
    }

    public function configureAction(DirectAction $action): void
    {
        //
    }

    abstract public function modifyQuery(Builder $query): void;

    abstract public function hasAccess(?User $user, Model $model): bool;

    abstract public function handle(EloquentTable $table, mixed $id): void;

    abstract public function render(Action $action, mixed $id): string|HtmlString|View|null;

}
