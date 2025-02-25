<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Idkwhoami\FluxTables\Traits\TableFilter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Deleted extends Component
{
    use TableFilter;

    #[Locked]
    public Filter $filter;

    public DeletionState $state;

    /**
     * @param  DeletionState  $state
     * @return void
     */
    public function updatedState(DeletionState $state): void
    {
        $this->dispatch("flux-tables::table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $state);
    }

    /**
     * @param  Filter  $filter
     * @return void
     */
    public function mount(Filter $filter): void
    {
        $this->filter = $filter;
        $this->state = $this->filter->hasValue()
            ? DeletionState::from($this->filter->getValue()->getValue())
            : DeletionState::tryFrom($this->filter->getDefault()) ?? DeletionState::WithoutDeleted;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('flux-tables::livewire.filters.deleted');
    }

    public function restoreDefault(): void
    {
        $this->state = DeletionState::tryFrom($this->filter->getDefault()) ?? DeletionState::WithoutDeleted;
    }
}
