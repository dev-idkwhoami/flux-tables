<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Idkwhoami\FluxTables\Traits\InteractsWithTable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Deleted extends Component
{
    use InteractsWithTable;

    #[Locked]
    public Filter $filter;

    public DeletionState $state;

    public function updatedState(DeletionState $state): void
    {
        $this->dispatch("table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $state);
    }

    public function mount(Filter $filter): void
    {
        $this->filter = $filter;
        $this->state = $this->filter->hasValue() ? DeletionState::from($this->filter->getValue()->getValue()) : DeletionState::WithoutDeleted;
    }

    public function render(): View
    {
        return view('flux-tables::livewire.filters.deleted');
    }
}
