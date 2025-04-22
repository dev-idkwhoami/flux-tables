<?php

namespace Idkwhoami\FluxTables\Contracts;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;

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

            if ($this->isPropertyClosure($property) && is_string($value)) {
                try {
                    $deserialized = unserialize($value);
                    if ($deserialized) {
                        $value = $deserialized->getClosure();
                    }
                } catch (\Throwable) {
                    $this->{$property} = $value;
                    continue;
                }
            }

            $this->{$property} = $value;
        }
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function isPropertyClosure(string $property): bool
    {
        $reflectionProperty = new \ReflectionProperty(static::class, $property);
        $type = $reflectionProperty->getType();

        if ($type instanceof ReflectionNamedType) {
            return $type->getName() === Closure::class;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType && $type->getName() === Closure::class) {
                    return true;
                }
            }
        }

        return false;
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
