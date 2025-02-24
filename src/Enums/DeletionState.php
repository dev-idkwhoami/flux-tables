<?php

namespace Idkwhoami\FluxTables\Enums;

enum DeletionState: string
{
    case WithoutDeleted = 'without_deleted';
    case WithDeleted = 'with_deleted';
    case OnlyDeleted = 'only_deleted';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return trans("flux-tables::filters/deleted.{$this->value}", []);
    }
}
