<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

class TextColumn extends PropertyColumn
{
    public function render(object $value): string|HtmlString|View
    {
        return $value->{$this->property};
    }
}
