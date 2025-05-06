<?php

namespace Idkwhoami\FluxTables\Traits;

use Illuminate\Support\Facades\Session;

trait HasDynamicPagination
{
    protected int $perPage = 10;
    protected string $pageName = 'page';

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
        $this->setPaginationName($this->pageName);
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
     * @return string
     */
    public function paginationPaginationNameSessionKey(): string
    {
        return "flux-tables::table::{$this->table->name}::page-name";
    }

    /**
     * Should be set if more than one table with pagination is used on the same page.
     *
     * @param  string  $pageName
     * @return $this
     */
    public function setPaginationName(string $pageName): static
    {
        $this->pageName = $pageName;
        Session::put($this->paginationPaginationNameSessionKey(), $this->pageName);
        return $this;
    }

    public function getPaginationName(): string
    {
        return Session::get($this->paginationPaginationNameSessionKey(), $this->pageName);
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
