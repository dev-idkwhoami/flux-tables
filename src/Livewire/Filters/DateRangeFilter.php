<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;

class DateRangeFilter extends Component
{
    use FilterComponent;

    public function mount(
        string $filterName,
        string $masterFilterId,
        mixed $value = null,
        string $label = '',
        ?string $description = null
    ): void {
        $this->mountFilterComponent($filterName, $masterFilterId, $value, $label, $description);

        // Ensure value is an array with start/end keys
        if (!is_array($this->value)) {
            $this->value = ['start' => null, 'end' => null];
        }
    }

    /**
     * Get the default view name for this filter component
     */
    protected function getDefaultView(): string
    {
        return 'flux-tables::livewire.filters.date-range-filter';
    }

    public function render()
    {
        return view($this->getView());
    }
}
