<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class ViewColumn extends Column
{
    public function view(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    #[Override]
    public function render(Model $row): View
    {
        return view($this->view, ['column' => $this, 'row' => $row]);
    }

    public static function make(string $name, ?string $view = null): static
    {
        return new static($view, $name);
    }
}
