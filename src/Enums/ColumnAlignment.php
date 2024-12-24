<?php

namespace Idkwhoami\FluxTables\Enums;

enum ColumnAlignment: string
{
    case Left = 'left';
    case Right = 'right';

    public function asCellAlignment(): string
    {
        return match ($this) {
            self::Left => 'start',
            self::Right => 'end',
        };
    }
}
