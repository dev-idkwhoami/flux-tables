<?php

namespace Idkwhoami\FluxTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class DateRangeFilter extends Filter
{
    protected ?string $dateFormat = null;
    protected bool $includeTime = false;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->value = ['start' => null, 'end' => null];
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
     * Include time in the date range filter
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
     * Set the from date
     */
    public function from(?string $date): static
    {
        if (!is_array($this->value)) {
            $this->value = ['start' => null, 'end' => null];
        }
        $this->value['start'] = $date;
        return $this;
    }

    /**
     * Set the to date
     */
    public function to(?string $date): static
    {
        if (!is_array($this->value)) {
            $this->value = ['start' => null, 'end' => null];
        }
        $this->value['end'] = $date;
        return $this;
    }

    public function render(): View|string
    {
        return view('flux-tables::filters.date-range', [
            'filter' => $this,
        ]);
    }

    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        $from = $this->value['start'] ?? null;
        $to = $this->value['end'] ?? null;

        if ($from && $to) {
            // Both dates provided - use whereBetween
            return $query->whereBetween($this->column, [
                $this->parseDate($from, true),
                $this->parseDate($to, false)
            ]);
        } elseif ($from) {
            // Only from date - greater than or equal
            return $query->where($this->column, '>=', $this->parseDate($from, true));
        } elseif ($to) {
            // Only to date - less than or equal
            return $query->where($this->column, '<=', $this->parseDate($to, false));
        }

        return $query;
    }

    public function getValueDescription(): string
    {
        if (!$this->hasValue()) {
            return 'No date range selected';
        }

        $from = $this->value['start'] ?? null;
        $to = $this->value['end'] ?? null;

        if ($from && $to) {
            return "Between {$from} and {$to}";
        } elseif ($from) {
            return "From {$from}";
        } elseif ($to) {
            return "Until {$to}";
        }

        return 'No date range selected';
    }

    public function validate(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $from = $this->value['start'] ?? null;
        $to = $this->value['end'] ?? null;

        // At least one date must be provided
        if (!$from && !$to) {
            return true; // Empty is valid
        }

        // Validate date formats if provided
        if ($from && !$this->isValidDate($from)) {
            return false;
        }

        if ($to && !$this->isValidDate($to)) {
            return false;
        }

        // If both dates are provided, from should be before or equal to to
        if ($from && $to) {
            try {
                $fromDate = Carbon::createFromFormat($this->getParseFormat(), $from);
                $toDate = Carbon::createFromFormat($this->getParseFormat(), $to);
                return $fromDate->lte($toDate);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    public function hasValue(): bool
    {
        if (!is_array($this->value)) {
            return false;
        }

        $from = $this->value['start'] ?? null;
        $to = $this->value['end'] ?? null;

        return ($from !== null && $from !== '') || ($to !== null && $to !== '');
    }

    /**
     * Parse date string to proper format for database query
     */
    protected function parseDate(string $date, bool $isFrom): string
    {
        try {
            $carbon = Carbon::createFromFormat($this->getParseFormat(), $date);

            if (!$this->includeTime) {
                if ($isFrom) {
                    // Set to start of day for 'from' date
                    $carbon->startOfDay();
                } else {
                    // Set to end of day for 'to' date
                    $carbon->endOfDay();
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
}