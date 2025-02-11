<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Traits\InteractsWithTable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DateRange extends Component
{
    use InteractsWithTable;

    #[Locked]
    public Filter $filter;

    public ?string $start = null;
    public ?string $end = null;

    public function updated(string $property, mixed $value): void
    {
        $this->$property = $value === "" ? null : $value;

        $state = [$this->start, $this->end];
        $this->dispatch("table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $state);
    }

    public function mount(Filter $filter): void
    {
        $this->filter = $filter;

        $value = $this->filter->getValue();
        $range = array_slice($value->getValue() ?? [], 0, 2);

        if (count($range) < 2) {
            $range = array_fill(0, 2, null);
        }

        $this->start = $range[0];
        $this->end = $range[1];
    }


    public function render(): View
    {
        return view('flux-tables::livewire.filters.date-range');
    }
}
