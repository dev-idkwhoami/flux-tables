<?php

namespace Idkwhoami\FluxTables\Traits;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

trait HasSearch
{
    #[Locked]
    public string $searchName = 'search';

    #[Url]
    public string $search = '';

    public function queryStringHasSearch(): array
    {
        return [
            'search' => [
                'except' => '',
                'as' => $this->searchName,
                'keep' => false,
                'history' => true,
            ]
        ];
    }

    /**
     * @return void
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

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
    public function getSearchableProperties(Builder $query): array
    {
        return array_map(
            fn(PropertyColumn $c) => $query->qualifyColumn($c->getProperty()),
            array_filter(
                $this->table->getColumns(),
                fn(Column $c) => $c->isSearchable() && $c instanceof PropertyColumn
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
            $query->whereAny($this->getSearchableProperties($query), 'ilike', '%'.$this->search.'%');
        }
    }

}
