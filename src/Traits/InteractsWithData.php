<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

trait InteractsWithData
{
    abstract public function retrieveModel(mixed $id): Model;

    protected function uniqueContextModelKey(mixed $id): string
    {
        $model = $this->retrieveModel($id);
        $key = sprintf("flux-tables::context::model::%s::%s::value", class_basename($model), $id);
        Context::addHiddenIf($key, $model);

        return $key;
    }

    protected function getModel(): Model
    {
        return Context::getHidden($this->uniqueContextModelKey($this->id), $this->retrieveModel($this->id));
    }

    public function resetContextModelValue(): void
    {
        Context::forget($this->uniqueContextModelKey($this->id));
    }

}
