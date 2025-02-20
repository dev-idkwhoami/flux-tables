<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\HasActions;
use Idkwhoami\FluxTables\Traits\HasEloquentTable;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasSearch;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasToggleableColumns;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class SimpleTable extends Component
{
    use HasEloquentTable;
    use HasActions;
    use HasFilters;
    use HasToggleableColumns;
    use HasSearch;
    use HasSorting;
    use WithPagination;

    #[Locked]
    public string $title = '';

    /**
     * @param  string  $title
     * @return void
     */
    public function mount(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('flux-tables::livewire.table.simple');
    }

    /**
     * @return Builder
     * @throws \Exception
     */
    public function getQuery(): Builder
    {
        if (!class_exists($this->eloquentModel)) {
            throw new \Exception("Model {$this->eloquentModel} not found");
        }
        /** @var Builder $query */
        $query = ($this->eloquentModel)::query();

        $this->applyColumns($query);
        $this->applyRelations($query);
        $this->applyActions($query);

        /*TODO default sorting not being applied default toggled columns also not being hidden */

        $this->applyFilters($query);
        $this->applySorting($query);
        $this->applySearch($query);

        $query->dumpRawSql();

        return $query;
    }

    /**
     * @return LengthAwarePaginator<Model>
     * @throws \Exception
     */
    #[Computed]
    public function models(): LengthAwarePaginator
    {
        $sql = $this->getQuery();

        /*$sql->dumpRawSql();*/

        return $sql->paginate();
    }

    /**
     * @param  string  $model
     * @param  Column[]  $columns
     * @param  Filter[]  $filters
     * @return EloquentTable
     */
    public function table(string $model, array $columns = [], array $filters = []): Table
    {
        return EloquentTable::make(strtolower(class_basename($model)))
            ->label($this->title)
            ->model($model)
            ->filters($filters)
            ->columns($columns);
    }

    /**
     * @return string
     */
    public function defaultSortingColumn(): string
    {
        return 'created_at';
    }

    /**
     * @return string[]
     */
    public function defaultToggledColumns(): array
    {
        return [
            'created_at'
        ];
    }
}
