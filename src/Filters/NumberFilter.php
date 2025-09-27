<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class NumberFilter extends Filter
{
    protected array $availableOperators = ['=', '>', '<', '>=', '<=', '!='];
    protected array $operatorLabels = [
        '=' => 'Equals',
        '>' => 'Greater than',
        '<' => 'Less than',
        '>=' => 'Greater than or equal',
        '<=' => 'Less than or equal',
        '!=' => 'Not equal'
    ];
    protected ?int $decimals = null;
    protected ?float $min = null;
    protected ?float $max = null;
    protected ?float $step = null;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->value = ['number' => null, 'operator' => '='];
    }

    /**
     * Set available operators for this filter
     */
    public function operators(array $operators): static
    {
        $this->availableOperators = array_intersect($operators, $this->availableOperators);

        // Ensure current operator is still valid
        if (!in_array($this->value['operator'], $this->availableOperators)) {
            $this->value['operator'] = $this->availableOperators[0] ?? '=';
        }

        return $this;
    }

    /**
     * Set custom operator labels
     */
    public function operatorLabels(array $labels): static
    {
        $this->operatorLabels = array_merge($this->operatorLabels, $labels);
        return $this;
    }

    /**
     * Set number of decimal places
     */
    public function decimals(int $decimals): static
    {
        $this->decimals = max(0, $decimals);
        return $this;
    }

    /**
     * Set minimum value
     */
    public function min(float $min): static
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Set maximum value
     */
    public function max(float $max): static
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Set step increment
     */
    public function step(float $step): static
    {
        $this->step = $step;
        return $this;
    }

    /**
     * Configure for integer values only
     */
    public function integer(): static
    {
        $this->decimals = 0;
        $this->step = $this->step ?? 1;
        return $this;
    }

    /**
     * Configure for currency values
     */
    public function currency(): static
    {
        $this->decimals = 2;
        $this->step = 0.01;
        return $this;
    }

    /**
     * Set the number value
     */
    public function number(float|int|null $number): static
    {
        if (!is_array($this->value)) {
            $this->value = ['number' => null, 'operator' => '='];
        }
        $this->value['number'] = $number;
        return $this;
    }

    /**
     * Set the operator
     */
    public function operator(string $operator): static
    {
        if (!is_array($this->value)) {
            $this->value = ['number' => null, 'operator' => '='];
        }

        if (in_array($operator, $this->availableOperators)) {
            $this->value['operator'] = $operator;
        }

        return $this;
    }

    public function render(): View|string
    {
        $numberValue = $this->value['number'] ?? '';
        $operatorValue = $this->value['operator'] ?? '=';

        return view('flux-tables::filters.number', [
            'filter' => $this,
            'numberValue' => $numberValue,
            'operatorValue' => $operatorValue,
            'availableOperators' => $this->availableOperators,
            'operatorLabels' => $this->operatorLabels,
            'decimals' => $this->decimals,
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
        ]);
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $number = $this->value['number'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        if ($number === null || $number === '') {
            return $query;
        }

        // Convert to appropriate number type
        $numericValue = $this->parseNumber($number);

        return $query->where($this->column, $operator, $numericValue);
    }

    public function getValueDescription(): string
    {
        if (!$this->hasValue()) {
            return 'No number specified';
        }

        $number = $this->value['number'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        if ($number === null || $number === '') {
            return 'No number specified';
        }

        $operatorLabel = $this->operatorLabels[$operator] ?? $operator;
        $formattedNumber = $this->formatNumber($number);

        return "{$operatorLabel} {$formattedNumber}";
    }

    public function validate(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $number = $this->value['number'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        // Empty is valid
        if ($number === null || $number === '') {
            return true;
        }

        // Validate that it's a valid number
        if (!is_numeric($number)) {
            return false;
        }

        $numericValue = (float) $number;

        // Validate operator
        if (!in_array($operator, $this->availableOperators)) {
            return false;
        }

        // Validate range
        if ($this->min !== null && $numericValue < $this->min) {
            return false;
        }

        if ($this->max !== null && $numericValue > $this->max) {
            return false;
        }

        return true;
    }

    public function hasValue(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $number = $this->value['number'] ?? null;
        return $number !== null && $number !== '';
    }

    /**
     * Parse number value to appropriate type
     */
    protected function parseNumber(mixed $number): float|int
    {
        $numericValue = (float) $number;

        // Return as integer if no decimals specified
        if ($this->decimals === 0) {
            return (int) $numericValue;
        }

        // Round to specified decimal places
        if ($this->decimals !== null) {
            return round($numericValue, $this->decimals);
        }

        return $numericValue;
    }

    /**
     * Format number for display
     */
    protected function formatNumber(mixed $number): string
    {
        $numericValue = (float) $number;

        if ($this->decimals !== null) {
            return number_format($numericValue, $this->decimals);
        }

        // Auto-detect decimal places needed
        if ($numericValue == floor($numericValue)) {
            return number_format($numericValue, 0);
        }

        return rtrim(rtrim(number_format($numericValue, 8), '0'), '.');
    }

    public function getDecimals(): ?int
    {
        return $this->decimals;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function getStep(): ?float
    {
        return $this->step;
    }

    public function getAvailableOperators(): array
    {
        return $this->availableOperators;
    }

    public function getOperatorLabels(): array
    {
        return $this->operatorLabels;
    }

    /**
     * Get HTML input attributes for the number field
     */
    public function getInputAttributes(): array
    {
        $attributes = [];

        if ($this->min !== null) {
            $attributes['min'] = $this->min;
        }

        if ($this->max !== null) {
            $attributes['max'] = $this->max;
        }

        if ($this->step !== null) {
            $attributes['step'] = $this->step;
        }

        return $attributes;
    }
}