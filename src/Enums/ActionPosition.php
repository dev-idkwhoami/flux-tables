<?php

namespace Idkwhoami\FluxTables\Enums;

enum ActionPosition: int
{
    case TITLE_INLINE = 0;
    case ABOVE_TOOLBAR = 1;
    case TOOLBAR_LEFT = 2;
    case TOOLBAR_RIGHT = 3;
    case BELOW_TOOLBAR = 4;
}
