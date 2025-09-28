<?php

namespace Idkwhoami\FluxTables\Columns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class DateColumn extends Column
{
    protected string $format = 'M j, Y';
    protected bool $human = false;
    protected ?string $timezone = null;

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function human(bool $human = true): static
    {
        $this->human = $human;
        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }


    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);

        // Try to parse the search term as a date
        try {
            $date = Carbon::parse($search);
            $query->orWhereDate($column, $date);
        } catch (\Exception $e) {
            // If parsing fails, search as text
            $query->orWhere($column, 'ILIKE', "%{$search}%");
        }
    }

    protected function formatDate(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        try {
            $carbon = Carbon::parse($value);

            if ($this->timezone) {
                $carbon = $carbon->setTimezone($this->timezone);
            }

            if ($this->human) {
                return $carbon->diffForHumans();
            }

            return $carbon->format($this->format);
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * Get the default view name for this column type
     */
    protected function getDefaultView(): string
    {
        return 'flux-tables::columns.date';
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        $formatted = $this->formatDate($value);

        $humanTime = null;
        if ($this->tooltip && !$this->human && $value) {
            try {
                $carbon = Carbon::parse($value);
                if ($this->timezone) {
                    $carbon = $carbon->setTimezone($this->timezone);
                }
                $humanTime = $carbon->diffForHumans();
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        return view($this->getView(), [
            'formatted' => $formatted,
            'value' => $value,
            'tooltip' => $this->tooltip,
            'human' => $this->human,
            'humanTime' => $humanTime,
        ]);
    }
}
