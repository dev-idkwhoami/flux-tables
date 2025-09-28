<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;

class BasicFilter extends Component
{
    use FilterComponent;

    public object $filter;

    public function mount(
        string $filterName,
        string $masterFilterId,
        mixed $value = null,
        string $label = '',
        ?string $description = null,
        object $filter = null
    ): void {
        $this->mountFilterComponent($filterName, $masterFilterId, $value, $label, $description);
        $this->filter = $filter;

        // Set the filter's value
        if ($this->filter) {
            $this->filter->setValue($this->value);
        }
    }

    /**
     * Override updatedValue to also update the filter object
     */
    public function updatedValue(): void
    {
        if ($this->filter) {
            $this->filter->setValue($this->value);
        }

        parent::updatedValue();
    }

    /**
     * Get the filter's rendered content
     */
    public function getFilterContent(): string
    {
        if (!$this->filter) {
            return '<div class="text-gray-500">Filter not available</div>';
        }

        // Update filter value before rendering
        $this->filter->setValue($this->value);

        return $this->filter->render();
    }

    public function render()
    {
        return view('flux-tables::livewire.filters.basic-filter', [
            'label' => $this->label,
            'description' => $this->description,
            'filterContent' => $this->getFilterContent(),
        ]);
    }
}
