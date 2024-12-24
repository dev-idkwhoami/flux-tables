<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Facades\FluxTables;
use Idkwhoami\FluxTables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TableComponent extends Component
{
    public Table $table;

    public function mount(string $model): void
    {
        $this->table = FluxTables::getTable($model);
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
