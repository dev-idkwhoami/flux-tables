<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class ViewColumn extends Column
{
    public function toLivewire(): array
    {
        return [
            'view' => $this->view,
            'name' => $this->name,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'alignment' => $this->alignment,
        ];
    }

    public static function fromLivewire($value): static
    {
        return new static(
            $value['view'],
            $value['name'],
            null,
            $value['label'],
            $value['sortable'],
            $value['searchable'],
            $value['alignment'],
        );
    }

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
