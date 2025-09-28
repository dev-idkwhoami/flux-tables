<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;

class DateFilter extends Component
{
    use FilterComponent;

    public array $availableOperators = ['=', '!=', '>', '>=', '<', '<='];
    public array $operatorLabels = [
        '=' => 'On',
        '!=' => 'Not on',
        '>' => 'After',
        '>=' => 'On or after',
        '<' => 'Before',
        '<=' => 'On or before'
    ];

    public string $inputType = 'date';

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
        $this->availableOperators = $config['operators'] ?? $this->availableOperators;
        $this->operatorLabels = array_merge($this->operatorLabels, $config['operator_labels'] ?? []);
        $this->inputType = $config['input_type'] ?? 'date';

        // Ensure value has proper structure
        if (!is_array($this->value)) {
            $this->value = [
                'operator' => $this->availableOperators[0] ?? '=',
                'date' => null
            ];
        }
    }

    /**
     * Get the current operator value
     */
    public function getOperatorValue(): string
    {
        return $this->value['operator'] ?? $this->availableOperators[0] ?? '=';
    }

    /**
     * Get the current date value
     */
    public function getDateValue(): ?string
    {
        return $this->value['date'] ?? null;
    }

    public function render()
    {
        return view('flux-tables::livewire.filters.date-filter', [
            'availableOperators' => $this->availableOperators,
            'operatorLabels' => $this->operatorLabels,
            'operatorValue' => $this->getOperatorValue(),
            'dateValue' => $this->getDateValue(),
            'inputType' => $this->inputType,
        ]);
    }
}
