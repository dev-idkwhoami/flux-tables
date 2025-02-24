<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Traits\InteractsWithTable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DateRange extends Component
{
    use InteractsWithTable;

    #[Locked]
    public Filter $filter;

    public \Flux\DateRange $range;

    public function updatedRange(): void
    {
        $this->dispatch("flux-tables::table:{$this->filter->getTable()}:filter:update", $this->filter->getName(), $this->range);
    }

    public function mount(Filter $filter): void
    {
        $this->filter = $filter;

        if ($this->filter->hasValue()) {
            $value = $this->filter->getValue();
            if ($value->getValue() instanceof \Flux\DateRange) {
                $this->range = $value->getValue();
            }
        }
    }

    #[Computed]
    public function weekStartDay(): int
    {
        return Carbon::getWeekStartsAt(app()->getLocale());
    }

    public function render(): View
    {
        return view('flux-tables::livewire.filters.date-range');
    }

    public function restoreDefault(): void
    {
        $default = $this->filter->getDefault() ?? [];
        if (!empty($default)) {
            $this->range = new \Flux\DateRange(array_shift($default), array_pop($default));
        }
    }
}
