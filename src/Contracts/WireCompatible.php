<?php

namespace Idkwhoami\FluxTables\Contracts;

use Laravel\SerializableClosure\SerializableClosure;

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

            /*if(!$this->isPropertyInitialized($property)) {
                throw new \InvalidArgumentException("Property {$property} is not initialized. All properties on WireCompatible classes must be initialized.");
            }*/

            if ($this->isPropertyClosure($property)) {
                $deserialized = unserialize($value);
                if (!$deserialized) {
                    continue;
                }
                $value = $deserialized->getClosure();
            }

            $this->{$property} = $value;
        }
        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    private function isPropertyClosure(string $property): bool
    {
        $reflectionProperty = new \ReflectionProperty(static::class, $property);
        $type = $reflectionProperty->getType();

        if (!($type instanceof \ReflectionNamedType)) {
            throw new \InvalidArgumentException("Property {$property} does not have a typehint");
        }

        return $type->getName() === \Closure::class;
    }

    /**
     * @return mixed[]
     */
    public function toLivewire(): array
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if ($value instanceof \Closure) {
                $vars[$key] = serialize(new SerializableClosure($value));
            }
        }

        return $vars;
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
