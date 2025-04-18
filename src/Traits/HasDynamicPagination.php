<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Support\Facades\Session;

trait HasDynamicPagination
{
    protected int $perPage = 10;

    /**
     * @return void
     * @throws \Exception
     */
    public function mountHasDynamicPagination(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }

        $this->perPage = $this->getPaginationValue();
    }

    /**
     * @return string
     */
    public function paginationOptionValueSessionKey(): string
    {
        return "flux-tables::table:{$this->table->name}:per-page";
    }

    public function setPaginationValue(int $perPage): static
    {
        $this->perPage = $perPage;
        Session::put($this->paginationOptionValueSessionKey(), $this->perPage);

        return $this;
    }

    /**
     * @return int
     */
    public function getPaginationValue(): int
    {
        return Session::get($this->paginationOptionValueSessionKey(), $this->defaultPaginationValue());
    }

    /**
     * @return int[]
     */
    abstract public function getPaginationOptions(): array;

    /**
     * @return int
     */
    abstract public function defaultPaginationValue(): int;

}
