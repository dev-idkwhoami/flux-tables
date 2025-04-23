<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\PropertyFilter;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\TableFilter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Boolean extends Component
{
    use TableFilter;

    #[Locked]
    public EloquentTable $table;

    #[Locked]
    public PropertyFilter $filter;

    public int $state = 0;

    public function updatedState(): void
    {
        $this->dispatch("flux-tables::table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $this->state);
    }

    public function mount(EloquentTable $table, PropertyFilter $filter): void
    {
        $this->table = $table;
        $this->filter = $filter;

        $this->state = $this->filter->hasValue() ? $this->filter->getValue()->getValue() : 0;
    }

    public function render(): View
    {
        return view('flux-tables::livewire.filters.boolean');
    }

    public function restoreDefault(): void
    {
        $this->state = $this->filter->getDefault() ?? 0;
    }
}
