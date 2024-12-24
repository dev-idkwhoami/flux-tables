<?php

namespace Idkwhoami\FluxTables\Columns;

class TextColumn extends Column
{
    public static function make(string $name): static
    {
        return new static('columns.text', $name);
    }
}
