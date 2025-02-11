<?php

namespace Idkwhoami\FluxTables\Abstracts\Column;

abstract class PropertyColumn extends Column
{
    protected string $property;

    /**
     * @param  string  $property
     * @return $this
     */
    public function property(string $property): PropertyColumn
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

}
