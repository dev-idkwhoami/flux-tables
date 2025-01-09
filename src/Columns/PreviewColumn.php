<?php

namespace Idkwhoami\FluxTables\Columns;


class PreviewColumn extends Column
{
    public function __construct(
        ?string $name = null,
        protected ?string $disk = null,
        protected bool $truncateFilename = false,
    ) {
        parent::__construct('columns.preview', $name);
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function truncateFilename(bool $truncateFilename = true): static
    {
        $this->truncateFilename = $truncateFilename;
        return $this;
    }

    public function useTruncatedFilename(): bool
    {
        return $this->truncateFilename;
    }

    public function getDisk(): ?string
    {
        return $this->disk;
    }

    public static function fromLivewire($value): static
    {
        return self::make($value['name'])->fill(__CLASS__, $value);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }
}
