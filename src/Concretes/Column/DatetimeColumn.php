<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Carbon\Carbon;
use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class DatetimeColumn extends PropertyColumn
{
    protected string $format = 'm/d/Y H:i:s';
    protected bool $readable = false;

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param  string  $format
     * @return $this
     */
    public function format(string $format): DatetimeColumn
    {
        $this->format = $format;
        return $this;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function humanReadable(bool $readable = true): DatetimeColumn
    {
        $this->readable = $readable;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if(!($value instanceof Model)) {
            throw new \Exception('Unable to render datetime column without a valid value');
        }

        $rawValue = $this->getValue($value);
        if ($rawValue) {
            if ($rawValue instanceof \DateTimeInterface) {
                if ($this->isReadable()) {
                    return \Illuminate\Support\Carbon::parse($rawValue)->diffForHumans();
                }
                return $rawValue->format($this->format);
            }
            return Carbon::parse($rawValue)->format($this->format);
        }

        return $this->getDefault();
    }
}
