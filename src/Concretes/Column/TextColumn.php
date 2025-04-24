<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class TextColumn extends PropertyColumn
{
    /**
     * @inheritDoc
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if (!($value instanceof Model)) {
            throw new \Exception('Unable to render text column without a valid value');
        }

        return $this->getValue($value) ?? '';
    }
}
