<?php

namespace Idkwhoami\FluxTables\Concretes\Column;

use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ListColumn extends PropertyColumn
{
    public const string JSON_AGG_EMPTY_VALUE = '[null]';

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function render(object $value): string|HtmlString|View|null
    {
        if(!($value instanceof Model)) {
            throw new \Exception('Unable to render list column without a valid value');
        }

        $effectiveValue = $this->getValue($value);

        if ($effectiveValue === self::JSON_AGG_EMPTY_VALUE) {
            return '';
        }

        if (!json_validate($effectiveValue)) {
            throw new \Exception('Unable to display list column due to invalid JSON');
        }


        return join(', ', json_decode($effectiveValue));
    }
}
