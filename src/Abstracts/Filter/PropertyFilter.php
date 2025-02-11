<?php

namespace Idkwhoami\FluxTables\Abstracts\Filter;

abstract class PropertyFilter extends Filter
{
    protected string $property;

    /**
     * @param  string  $property
     * @return $this
     */
    public function property(string $property): PropertyFilter
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
