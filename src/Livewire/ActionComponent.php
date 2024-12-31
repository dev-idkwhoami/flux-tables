<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Actions\Action;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ActionComponent extends Component
{
    public Action $action;

    public int $index;

    public function mount(Action $action, int $index): void
    {
        $this->action = $action;
        $this->index = $index;
    }

    public function render(): View
    {
        return view('flux-tables::'.$this->action->getView());
    }
}
