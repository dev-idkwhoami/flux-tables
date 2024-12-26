<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Facades\FluxTables;
use Idkwhoami\FluxTables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class TableComponent extends Component
{
    use WithPagination;

    public ?Table $table = null;

    public function mount(string $model): void
    {
        $this->table = FluxTables::getTable($model);
        $this->table->prepare();
    }

    public function sort(string $column): void
    {
        $this->table->sort($column);
    }

    public function updating(string $property, mixed $value): void
    {
        if (str_starts_with($property, 'table.filters')
            || str_starts_with($property, 'table.search')) {
            $this->resetPage();
        }

        if (str_starts_with($property, 'table.perPage')) {
            $this->resetPage();
            $this->table->refillFilters();
            $this->table->perPage($value);
        }
    }

    public function updated(string $property, mixed $value): void
    {
        if (str_starts_with($property, 'table.filters')) {

            $index = str($property)->after('table.filters.')->before('.value')->value();
            $this->table->filters[$index]->setValueInSession($this->table->getName());
        }

        if (str_starts_with($property, 'table.columns')) {
            $this->table->toggleColumn(intval(str($property)->after('table.columns.')->before('.toggled')->value()));
        }
    }

    public function resetFilters(): void
    {
        $this->table->resetFilters();
        $this->resetPage();
    }

    #[Computed]
    public function models()
    {
        return $this->table->getPaginatedModels();
    }

    public function render(): View
    {
        return view('flux-tables::livewire.table');
    }
}
