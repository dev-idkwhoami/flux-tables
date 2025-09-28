<?php

namespace Idkwhoami\FluxTables\Livewire\Filters;

use Livewire\Component;
use Idkwhoami\FluxTables\Traits\FilterComponent;

class NumberFilter extends Component
{
    use FilterComponent;

    public array $availableOperators = ['=', '!=', '>', '>=', '<', '<='];
    public array $operatorLabels = [
        '=' => 'Equals',
        '!=' => 'Not equals',
        '>' => 'Greater than',
        '>=' => 'Greater than or equal',
        '<' => 'Less than',
        '<=' => 'Less than or equal'
    ];

    public ?float $min = null;
    public ?float $max = null;
    public ?float $step = null;

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
        $this->min = $config['min'] ?? null;
        $this->max = $config['max'] ?? null;
        $this->step = $config['step'] ?? null;

        // Ensure value has proper structure
        if (!is_array($this->value)) {
            $this->value = [
                'operator' => $this->availableOperators[0] ?? '=',
                'number' => null
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
     * Get the current number value
     */
    public function getNumberValue(): ?float
    {
        return $this->value['number'] ?? null;
    }

    public function render()
    {
        return view('flux-tables::livewire.filters.number-filter', [
            'availableOperators' => $this->availableOperators,
            'operatorLabels' => $this->operatorLabels,
            'operatorValue' => $this->getOperatorValue(),
            'numberValue' => $this->getNumberValue(),
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
        ]);
    }
}
