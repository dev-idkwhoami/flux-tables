<?php

namespace Idkwhoami\FluxTables\Columns;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class TextColumn extends Column
{
    protected ?int $limit = null;
    protected bool $words = false;
    protected bool $wrap = false;
    protected bool $uppercase = false;
    protected bool $lowercase = false;
    protected bool $capitalize = false;
    protected bool $autolink = true;
    protected bool $copyable = false;
    protected string $truncateEnd = '...';

    public function limit(int $limit, bool $words = false): static
    {
        $this->limit = $limit;
        $this->words = $words;
        return $this;
    }

    public function words(int $words): static
    {
        $this->limit = $words;
        $this->words = true;
        return $this;
    }

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function uppercase(bool $uppercase = true): static
    {
        $this->uppercase = $uppercase;
        $this->lowercase = false;
        $this->capitalize = false;
        return $this;
    }

    public function lowercase(bool $lowercase = true): static
    {
        $this->lowercase = $lowercase;
        $this->uppercase = false;
        $this->capitalize = false;
        return $this;
    }

    public function capitalize(bool $capitalize = true): static
    {
        $this->capitalize = $capitalize;
        $this->uppercase = false;
        $this->lowercase = false;
        return $this;
    }

    public function autolink(bool $autolink = true): static
    {
        $this->autolink = $autolink;
        return $this;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    public function truncateEnd(string $end): static
    {
        $this->truncateEnd = $end;
        return $this;
    }

    protected function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        $text = (string) $value;

        // Only handle word truncation (character truncation is handled by CSS)
        if ($this->limit && $this->words) {
            $text = Str::words($text, $this->limit, $this->truncateEnd);
        }

        return $text;
    }

    /**
     * Get the default view name for this column type
     */
    protected function getDefaultView(): string
    {
        return 'flux-tables::columns.text';
    }

    public function render(object $record): View
    {
        $value = $this->getValue($record);
        $formatted = $this->formatValue($value);

        return view($this->getView(), [
            'formatted' => $formatted,
            'copyable' => $this->copyable,
            'copyId' => 'copy_' . Str::random(8),
            'wrap' => $this->wrap,
            'uppercase' => $this->uppercase,
            'lowercase' => $this->lowercase,
            'capitalize' => $this->capitalize,
            'limit' => $this->limit,
            'words' => $this->words,
        ]);
    }

}
