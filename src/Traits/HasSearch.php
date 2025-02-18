<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\Database\Query\Builder;
use Livewire\Attributes\Url;

trait HasSearch
{
    #[Url(as: 's', except: '')]
    public string $search = '';

    /**
     * @return void
     * @throws \Exception
     */
    public function mountHasSearch(): void
    {
        if (!property_exists($this, 'table')) {
            throw new \Exception(__CLASS__.' must have a table property');
        }
    }

    /**
     * @return string[]
     */
    public function getSearchableProperties(): array
    {
        return array_map(
            fn (PropertyColumn $c) => $c->getProperty(),
            array_filter(
                $this->table->getColumns(),
                fn (Column $c) => $c->isSearchable() && $c instanceof PropertyColumn
            )
        );
    }

    /**
     * @param  Builder  $query
     * @return void
     */
    public function applySearch(Builder $query): void
    {
        if (!empty($this->search)) {
            $query->whereAny($this->getSearchableProperties(), 'ilike', '%'.$this->search.'%');
        }
    }

}
