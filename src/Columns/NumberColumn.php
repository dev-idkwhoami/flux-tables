<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class NumberColumn extends Column
{
    protected ?int $precision = null;
    protected bool $thousandsSeparator = true;
    protected string $thousandsChar = ',';
    protected string $decimalChar = '.';
    protected bool $percentage = false;
    protected bool $currency = false;
    protected string $currencySymbol = '$';
    protected bool $currencyBefore = true;
    protected bool $abbreviate = false;
    protected array $abbreviations = [
        1000000000 => 'B',
        1000000 => 'M',
        1000 => 'K',
    ];

    public function precision(int $precision): static
    {
        $this->precision = $precision;
        return $this;
    }

    public function thousandsSeparator(bool $enabled = true, string $char = ','): static
    {
        $this->thousandsSeparator = $enabled;
        $this->thousandsChar = $char;
        return $this;
    }

    public function decimalChar(string $char): static
    {
        $this->decimalChar = $char;
        return $this;
    }

    public function percentage(bool $percentage = true): static
    {
        $this->percentage = $percentage;
        $this->currency = false;
        return $this;
    }

    public function currency(string $symbol = '$', bool $before = true): static
    {
        $this->currency = true;
        $this->currencySymbol = $symbol;
        $this->currencyBefore = $before;
        $this->percentage = false;
        if ($this->precision === null) {
            $this->precision = 2;
        }
        return $this;
    }

    public function abbreviate(bool $abbreviate = true, array $abbreviations = []): static
    {
        $this->abbreviate = $abbreviate;
        if (!empty($abbreviations)) {
            $this->abbreviations = $abbreviations;
        }
        return $this;
    }

    public function applySearch(Builder $query, string $search): void
    {
        if (!$this->isSearchable()) {
            return;
        }

        $column = $this->getSearchColumn($query);

        // Clean the search term of formatting
        $cleanSearch = str_replace([$this->thousandsChar, $this->currencySymbol, '%'], '', $search);
        $cleanSearch = str_replace($this->decimalChar, '.', $cleanSearch);

        if (is_numeric($cleanSearch)) {
            $number = (float) $cleanSearch;

            // If it's a percentage search, convert to decimal
            if ($this->percentage && str_contains($search, '%')) {
                $number = $number / 100;
            }

            $query->orWhere($column, $number);
        }
    }

    protected function formatNumber(mixed $value): string
    {
        if (is_null($value) || !is_numeric($value)) {
            return '';
        }

        $number = (float) $value;

        // Handle abbreviation
        if ($this->abbreviate) {
            foreach ($this->abbreviations as $threshold => $suffix) {
                if (abs($number) >= $threshold) {
                    $abbreviated = $number / $threshold;
                    $formatted = number_format(
                        $abbreviated,
                        $this->precision ?? ($abbreviated == floor($abbreviated) ? 0 : 1),
                        $this->decimalChar,
                        $this->thousandsSeparator ? $this->thousandsChar : ''
                    );
                    return $this->wrapWithSymbol($formatted . $suffix);
                }
            }
        }

        // Handle percentage
        if ($this->percentage) {
            $number *= 100;
        }

        // Format the number
        $formatted = number_format(
            $number,
            $this->precision ?? ($number == floor($number) ? 0 : 2),
            $this->decimalChar,
            $this->thousandsSeparator ? $this->thousandsChar : ''
        );

        // Add percentage symbol
        if ($this->percentage) {
            $formatted .= '%';
        }

        return $this->wrapWithSymbol($formatted);
    }

    protected function wrapWithSymbol(string $formatted): string
    {
        if ($this->currency) {
            if ($this->currencyBefore) {
                return $this->currencySymbol . $formatted;
            } else {
                return $formatted . $this->currencySymbol;
            }
        }

        return $formatted;
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        $formatted = $this->formatNumber($value);

        return view('flux-tables::columns.number', [
            'formatted' => $formatted,
            'value' => $value,
        ]);
    }
}
