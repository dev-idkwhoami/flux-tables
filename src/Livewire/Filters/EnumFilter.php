<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;

class EnumFilter extends Component
{
    use FilterComponent;

    public array $options = [];
    public array $selectedValues = [];
    public bool $multiple = false;
    public string $displayMode = 'select'; // 'select', 'radio', 'checkbox'

    public function mount(
        string $filterName,
        string $masterFilterId,
        mixed $value = null,
        string $label = '',
        ?string $description = null,
        array $config = []
    ): void {
        $this->mountFilterComponent($filterName, $masterFilterId, $value, $label, $description);

        // Apply configuration
        $this->options = $config['options'] ?? [];
        $this->multiple = $config['multiple'] ?? false;
        $this->displayMode = $config['display_mode'] ?? ($this->multiple ? 'checkbox' : 'select');

        // Initialize value format
        if (!$this->multiple && is_array($this->value)) {
            $this->value = $this->value[0] ?? null;
        } elseif ($this->multiple && !is_array($this->value)) {
            $this->value = $this->value ? [$this->value] : [];
        }

        $this->updateSelectedValues();
    }

    /**
     * Override updatedValue to update selected values array
     */
    public function updatedValue(): void
    {
        $this->updateSelectedValues();
        parent::updatedValue();
    }

    /**
     * Update the selected values array for display
     */
    protected function updateSelectedValues(): void
    {
        if ($this->multiple) {
            $this->selectedValues = is_array($this->value) ? $this->value : [];
        } else {
            $this->selectedValues = $this->value ? [$this->value] : [];
        }
    }

    public function render()
    {
        return view('flux-tables::livewire.filters.enum-filter', [
            'options' => $this->options,
            'selectedValues' => $this->selectedValues,
            'multiple' => $this->multiple,
            'displayMode' => $this->displayMode,
        ]);
    }
}
