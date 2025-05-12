<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * @method array validate(array $rules = [], array $messages = [], array $attributes = [])
 * @method array only(array|mixed $keys)
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

    public function store(): ?Model
    {
        Gate::authorize('create', $this->configureModel());

        return $this->configureModel()::query()->create($this->validatedForAction());
    }

    public function update(Model $model): bool
    {
        Gate::authorize('update', $model);

        return $model->update($this->validatedForAction($model));
    }

    public function delete(Model $model): bool
    {
        Gate::authorize('delete', $model);

        $this->validate($this->rulesForAction($model));

        return $model->delete();
    }

    public function restore(Model $model): bool
    {
        Gate::authorize('restore', $model);

        $this->validate($this->rulesForAction($model));

        if (!in_array(SoftDeletes::class, class_uses($model))) {
            throw new \Exception('Model must use SoftDeletes trait.');
        }

        return $model->restore();
    }

    public function forceDelete(Model $model): bool
    {
        Gate::authorize('forceDelete', $model);

        return $model->forceDelete();
    }

}
