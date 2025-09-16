<?php

namespace Idkwhoami\FluxTables\Columns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class DateTimeColumn extends Column
{
    protected string $format = 'M j, Y g:i A';
    protected bool $human = false;
    protected ?string $timezone = null;
    protected bool $seconds = false;

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


    public function seconds(bool $seconds = true): static
    {
        $this->seconds = $seconds;
        if ($seconds && $this->format === 'M j, Y g:i A') {
            $this->format = 'M j, Y g:i:s A';
        }
        return $this;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);

        // Try to parse the search term as a date/datetime
        try {
            $date = Carbon::parse($search);

            // Check if the search includes time information
            if (strlen($search) > 10 && (str_contains($search, ':') || str_contains($search, 'T'))) {
                // DateTime search - match exact datetime or within same minute
                $query->orWhereBetween($column, [
                    $date->startOfMinute(),
                    $date->endOfMinute()
                ]);
            } else {
                // Date search - match any time on that date
                $query->orWhereDate($column, $date);
            }
        } catch (\Exception $e) {
            // If parsing fails, search as text
            $query->orWhere($column, 'ILIKE', "%{$search}%");
        }
    }

    protected function formatDateTime(mixed $value): string
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

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        $formatted = $this->formatDateTime($value);

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

        return view('flux-tables::columns.datetime', [
            'formatted' => $formatted,
            'value' => $value,
            'tooltip' => $this->tooltip,
            'human' => $this->human,
            'humanTime' => $humanTime,
        ]);
    }
}
