<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class DateFilter extends Filter
{
    protected ?string $dateFormat = null;
    protected bool $includeTime = false;
    protected array $availableOperators = ['=', '>', '<', '>=', '<=', '!='];
    protected array $operatorLabels = [
        '=' => 'Equals',
        '>' => 'After',
        '<' => 'Before',
        '>=' => 'On or after',
        '<=' => 'On or before',
        '!=' => 'Not equal'
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->value = ['date' => null, 'operator' => '='];
        $this->dateFormat = 'Y-m-d';
    }

    /**
     * Set the date format for display and parsing
     */
    public function format(string $format): static
    {
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * Include time in the date filter
     */
    public function includeTime(bool $includeTime = true): static
    {
        $this->includeTime = $includeTime;
        if ($includeTime && $this->dateFormat === 'Y-m-d') {
            $this->dateFormat = 'Y-m-d H:i:s';
        }
        return $this;
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
     * Set the date value
     */
    public function date(?string $date): static
    {
        if (!is_array($this->value)) {
            $this->value = ['date' => null, 'operator' => '='];
        }
        $this->value['date'] = $date;
        return $this;
    }

    /**
     * Set the operator
     */
    public function operator(string $operator): static
    {
        if (!is_array($this->value)) {
            $this->value = ['date' => null, 'operator' => '='];
        }

        if (in_array($operator, $this->availableOperators)) {
            $this->value['operator'] = $operator;
        }

        return $this;
    }

    public function render(): View|string
    {
        $dateValue = $this->value['date'] ?? '';
        $operatorValue = $this->value['operator'] ?? '=';
        $inputType = $this->includeTime ? 'datetime-local' : 'date';

        return view('flux-tables::filters.date', [
            'filter' => $this,
            'dateValue' => $dateValue,
            'operatorValue' => $operatorValue,
            'availableOperators' => $this->availableOperators,
            'operatorLabels' => $this->operatorLabels,
            'inputType' => $inputType,
        ]);
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $date = $this->value['date'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        if (!$date) {
            return $query;
        }

        $parsedDate = $this->parseDate($date, $operator);

        return $query->where($this->column, $operator, $parsedDate);
    }

    public function getValueDescription(): string
    {
        if (!$this->hasValue()) {
            return 'No date selected';
        }

        $date = $this->value['date'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        if (!$date) {
            return 'No date selected';
        }

        $operatorLabel = $this->operatorLabels[$operator] ?? $operator;

        return "{$operatorLabel} {$date}";
    }

    public function validate(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $date = $this->value['date'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        // Empty is valid
        if (!$date) {
            return true;
        }

        // Validate date format
        if (!$this->isValidDate($date)) {
            return false;
        }

        // Validate operator
        if (!in_array($operator, $this->availableOperators)) {
            return false;
        }

        return true;
    }

    public function hasValue(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $date = $this->value['date'] ?? null;
        return $date !== null && $date !== '';
    }

    /**
     * Parse date string to proper format for database query
     */
    protected function parseDate(string $date, string $operator): string
    {
        try {
            $carbon = Carbon::createFromFormat($this->getParseFormat(), $date);

            if (!$this->includeTime) {
                // For date-only comparisons, adjust the time based on operator
                switch ($operator) {
                    case '>':
                        // Greater than date means start of next day
                        $carbon->endOfDay();
                        break;
                    case '>=':
                        // Greater than or equal means start of day
                        $carbon->startOfDay();
                        break;
                    case '<':
                        // Less than date means start of day
                        $carbon->startOfDay();
                        break;
                    case '<=':
                        // Less than or equal means end of day
                        $carbon->endOfDay();
                        break;
                    case '=':
                    case '!=':
                        // For equality, we need to handle this differently in apply()
                        // For now, use start of day
                        $carbon->startOfDay();
                        break;
                }
            }

            return $carbon->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Fallback to original string
            return $date;
        }
    }

    /**
     * Check if a date string is valid
     */
    protected function isValidDate(string $date): bool
    {
        try {
            Carbon::createFromFormat($this->getParseFormat(), $date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the format string for parsing dates
     */
    protected function getParseFormat(): string
    {
        return $this->dateFormat ?? ($this->includeTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function isIncludingTime(): bool
    {
        return $this->includeTime;
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
     * Handle equality comparisons for date-only fields
     */
    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $date = $this->value['date'] ?? null;
        $operator = $this->value['operator'] ?? '=';

        if (!$date) {
            return $query;
        }

        if (!$this->includeTime && ($operator === '=' || $operator === '!=')) {
            // For date-only equality, we need to use date range
            try {
                $carbon = Carbon::createFromFormat($this->getParseFormat(), $date);
                $startOfDay = $carbon->copy()->startOfDay()->format('Y-m-d H:i:s');
                $endOfDay = $carbon->copy()->endOfDay()->format('Y-m-d H:i:s');

                if ($operator === '=') {
                    return $query->whereBetween($this->column, [$startOfDay, $endOfDay]);
                } else {
                    return $query->where(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where($this->column, '<', $startOfDay)
                              ->orWhere($this->column, '>', $endOfDay);
                    });
                }
            } catch (\Exception $e) {
                // Fallback to simple comparison
                return $query->where($this->column, $operator, $date);
            }
        }

        $parsedDate = $this->parseDate($date, $operator);
        return $query->where($this->column, $operator, $parsedDate);
    }

    /**
     * Get configuration for the Livewire component
     */
    public function getComponentConfig(): array
    {
        return [
            'operators' => $this->availableOperators,
            'operator_labels' => $this->operatorLabels,
            'input_type' => $this->inputType,
        ];
    }
}
