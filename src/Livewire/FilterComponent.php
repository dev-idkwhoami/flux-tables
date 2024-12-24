<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FilterComponent extends Component
{
    public Filter $filter;

    public string $name;

    public function mount(Filter $filter, string $name): void
    {
        $this->filter = $filter;
        $this->name = $name;
    }

    public function render(): View
    {
        return view('flux-tables::'.$this->filter->getView());
    }
}
