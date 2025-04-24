<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class BooleanColumn extends PropertyColumn
{
    protected string $trueIcon = 'circle-check';
    protected string $trueColorClass = 'text-green-500';
    protected string $falseIcon = 'circle-x';
    protected string $falseColorClass = 'text-red-500';

    public function falseColorClass(string $falseColorClass): BooleanColumn
    {
        $this->falseColorClass = $falseColorClass;
        return $this;
    }

    public function getFalseColorClass(): string
    {
        return $this->falseColorClass;
    }

    public function falseIcon(string $falseIcon): BooleanColumn
    {
        $this->falseIcon = $falseIcon;
        return $this;
    }

    public function getFalseIcon(): string
    {
        return $this->falseIcon;
    }

    public function trueColorClass(string $trueColorClass): BooleanColumn
    {
        $this->trueColorClass = $trueColorClass;
        return $this;
    }

    public function getTrueColorClass(): string
    {
        return $this->trueColorClass;
    }

    public function trueIcon(string $trueIcon): BooleanColumn
    {
        $this->trueIcon = $trueIcon;
        return $this;
    }

    public function getTrueIcon(): string
    {
        return $this->trueIcon;
    }

    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if (!($value instanceof Model)) {
            throw new \Exception('Unable to render boolean column without a valid value');
        }

        return view('flux-tables::column.boolean', ['value' => $this->getValue($value), 'column' => $this]);
    }
}
