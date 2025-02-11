<?php

namespace Idkwhoami\FluxTables\Contracts;

trait WireCompatible
{
    /**
     * @param  string  $name
     * @param  mixed[]  ...$properties
     * @return static
     */
    public static function make(string $name, array $properties = []): static
    {
        return (new static($name))->fill($properties);
    }

    /**
     * @param  mixed[]  $properties
     * @return $this
     */
    protected function fill(array $properties): static
    {
        foreach ($properties as $property => $value) {
            if (!property_exists(static::class, $property)) {
                throw new \InvalidArgumentException("Property {$property} does not exist");
            }
            $this->{$property} = $value;
        }
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toLivewire(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param  array{name: string}  $value
     * @return static
     */
    public static function fromLivewire($value): static
    {
        $name = $value['name'];

        return self::make($name, array_diff_key($value, ['name' => null]));
    }

}
