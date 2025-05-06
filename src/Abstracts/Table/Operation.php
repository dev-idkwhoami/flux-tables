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
use Illuminate\Support\Facades\Session;
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

    public abstract function modifyQuery(Builder $query): void;

    public abstract function hasAccess(?User $user, Model $model): bool;

    public abstract function handle(EloquentTable $table, mixed $id): void;

    public abstract function render(Action $action, mixed $id): string|HtmlString|View|null;

    final public function sessionKey(): string
    {
        return "flux-tables::table::operations::{$this->name}::context";
    }

    final public function uniqueId(): string
    {
        return encrypt($this->sessionKey(), false);
    }

    final public static function store(Operation $operation): void
    {
        Session::put($operation->sessionKey(), $operation);
    }

    final public static function get(string $operation): static
    {
        $operation = decrypt($operation, false);

        if(Session::has($operation) === false) {
            throw new \Exception("Operation {$operation} not found");
        }

        return Session::get($operation);
    }

}
