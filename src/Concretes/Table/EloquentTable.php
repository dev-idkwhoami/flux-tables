<?php

namespace Idkwhoami\FluxTables\Concretes\Table;

use Idkwhoami\FluxTables\Abstracts\Table\Table;

class EloquentTable extends Table
{
    public string $eloquentModel = '';
    protected string $label = '';

    protected ?string $createComponent = null;
    protected ?string $createText = null;

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

    public function createComponent(?string $create): EloquentTable
    {
        $this->createComponent = $create;
        return $this;
    }

    public function getCreateComponent(): ?string
    {
        return $this->createComponent;
    }

    public function getCreateModalName(): string
    {
        return "{$this->name}-modal-create";
    }

    public function getCreateText(): ?string
    {
        return $this->createText
            ?? __(
                'flux-tables::actions/create.label',
                ['model' => \str(class_basename($this->eloquentModel))->singular()->lower()]
            );
    }

    public function createText(?string $createText): EloquentTable
    {
        $this->createText = $createText;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCreate(): bool
    {
        return !empty($this->createComponent);
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
