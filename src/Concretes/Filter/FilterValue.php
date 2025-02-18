<?php

namespace Idkwhoami\FluxTables\Concretes\Filter;

use Livewire\Wireable;

class FilterValue implements Wireable
{
    /**
     * @var mixed|null $value
     */
    protected mixed $value;

    /**
     * @param  mixed|null  $value
     */
    final public function __construct(mixed $value = null)
    {
        $this->value = $value;
    }

    /**
     * @return mixed|null
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  mixed|null  $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed[]
     */
    public function toLivewire(): array
    {
        if (!json_validate($this->value)) {
            throw new \InvalidArgumentException("Unable to parse value due to invalid JSON");
        }

        return ['value' => base64_encode(strval(json_encode($this->value)))];
    }

    /**
     * @param  mixed[]  $value
     * @return static
     */
    public static function fromLivewire($value): static
    {
        if (!isset($value['value'])) {
            throw new \InvalidArgumentException("Unable to parse value due to missing 'value' key");
        }

        return new static(json_decode(base64_decode($value["value"]), !is_string($value["value"])));
    }
}
