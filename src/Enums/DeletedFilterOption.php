<?php

namespace Idkwhoami\FluxTables\Enums;

enum DeletedFilterOption: int
{
    case WITHOUT_DELETED = 0;
    case WITH_DELETED = 1;
    case ONLY_DELETED = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::WITHOUT_DELETED => __('Without deleted records'),
            self::WITH_DELETED => __('With deleted records'),
            self::ONLY_DELETED => __('Only deleted records'),
        };
    }

}
