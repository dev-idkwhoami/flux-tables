<?php

namespace Idkwhoami\FluxTables\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * @method array validate(array $rules = [], array $messages = [], array $attributes = [])
 * @method array only(array|mixed $keys)
 * @method array all()
 */
trait TableForm
{
    abstract public function configureModel(): string;

    public function rulesForAction(Model $model = null, string $action = null): array
    {
        if (!$action) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            if (isset($trace[1]['function']) && is_string($trace[1]['function'])) {
                $action = $trace[1]['function'];
            }
        }

        $ruleFunction = sprintf('rules%s', Str::studly($action));

        if (method_exists($this, $ruleFunction)) {
            return $this->{$ruleFunction}($model) ?? [];
        }

        return [];
    }

    public function validatedForAction(Model $model = null, string $action = null): array
    {
        if (!$action) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            if (isset($trace[1]['function']) && is_string($trace[1]['function'])) {
                $action = $trace[1]['function'];
            }
        }
        $rules = $this->rulesForAction($model, $action);
        $attributes = array_unique(
            array_map(
                fn (string $property) => strpos($property, '.') ? strstr($property, '.', true) : $property,
                array_keys($rules)
            )
        );

        return $this->validate(rules: $rules, attributes: $attributes);
    }

    public function setup(array $dataMapping = []): void
    {
        foreach ($dataMapping as $key => $value) {
            if ($value instanceof Closure) {
                /** @var Closure $value */
                /* fn(User $user, array $data) */
                $value = $value->call($this, Auth::user(), $this->all());
            }

            $this->$key = $value;
        }
    }

    public function store(): ?Model
    {
        Gate::authorize('create', $this->configureModel());

        return $this->configureModel()::query()->create($this->validatedForAction(action: 'store'));
    }

    public function update(Model $model): bool
    {
        Gate::authorize('update', $model);

        return $model->update($this->validatedForAction($model, action: 'update'));
    }

    public function delete(Model $model): bool
    {
        Gate::authorize('delete', $model);

        $this->validate($this->rulesForAction($model, action: 'delete'));

        return $model->delete();
    }

    public function restore(Model $model): bool
    {
        Gate::authorize('restore', $model);

        $this->validate($this->rulesForAction($model, action: 'restore'));

        if (!in_array(SoftDeletes::class, class_uses($model))) {
            throw new \Exception('Model must use SoftDeletes trait.');
        }

        return $model->restore();
    }

    public function forceDelete(Model $model): bool
    {
        Gate::authorize('forceDelete', $model);

        $this->validate($this->rulesForAction($model, action: 'forceDelete'));

        return $model->forceDelete();
    }

}
