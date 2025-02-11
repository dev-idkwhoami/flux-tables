<?php

namespace Idkwhoami\FluxTables\Concretes\Table;

use Idkwhoami\FluxTables\Abstracts\Table\Table;

class EloquentTable extends Table
{

    protected ?string $label = null;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function label(string $label): EloquentTable
    {
        $this->label = $label;
        return $this;
    }

    public function hasLabel(): bool
    {
        return !empty($this->label);
    }


}
