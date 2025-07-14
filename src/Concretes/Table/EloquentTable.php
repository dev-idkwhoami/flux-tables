<?php

namespace Idkwhoami\FluxTables\Concretes\Table;

use Idkwhoami\FluxTables\Abstracts\Table\Table;

class EloquentTable extends Table
{
    public string $eloquentModel = '';
    protected string $label = '';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param  string  $label
     * @return $this
     */
    public function label(string $label): EloquentTable
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        return !empty($this->label);
    }

    /**
     * @return string
     */
    public function getEloquentModel(): string
    {
        return $this->eloquentModel;
    }

    /**
     * @param  string  $eloquentModel
     * @return $this
     */
    public function model(string $eloquentModel): EloquentTable
    {
        $this->eloquentModel = $eloquentModel;
        return $this;
    }

}
