<?php

namespace Idkwhoami\FluxTables\Contracts;

interface HasContext
{
    public function contextKey(string $key, mixed $id = null): string;

}
