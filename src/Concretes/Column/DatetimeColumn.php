<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Carbon\Carbon;
use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class DatetimeColumn extends PropertyColumn
{
    protected string $format = 'm/d/Y H:i:s';

    public function getFormat(): string
    {
        return $this->format;
    }

    public function format(string $format): DatetimeColumn
    {
        $this->format = $format;
        return $this;
    }

    public function render(object $value): string|HtmlString|View
    {
        $rawValue = $value->{$this->property};
        if ($rawValue instanceof \DateTimeInterface) {
            return $rawValue->format($this->format);
        }

        return Carbon::parse($rawValue)->format($this->format);
    }
}
