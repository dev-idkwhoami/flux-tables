<?php

namespace Idkwhoami\FluxTables\Enums;

enum DeletionState: string
{
    case WithoutDeleted = 'without_deleted';
    case WithDeleted = 'with_deleted';
    case OnlyDeleted = 'only_deleted';

    public function getLabel(): string
    {
        return trans("flux-tables::filter.deleted.{$this->value}", []);
    }
}
