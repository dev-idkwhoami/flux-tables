<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\HasActions;
use Idkwhoami\FluxTables\Traits\HasDynamicPagination;
use Idkwhoami\FluxTables\Traits\HasEloquentTable;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasSearch;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasToggleableColumns;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
    use HasDynamicPagination;
    use WithPagination;

    /** @var array<string, string> $listeners */
    protected $listeners = [
        'flux-tables::table:refresh' => '$refresh'
    ];

    #[Locked]
    protected bool $verbose = false;

    #[Locked]
    public string $title = '';

    #[Locked]
    public string $defaultSortingColumn = '';
    #[Locked]
    public string $defaultSortingDirection = '';

    /** @var string[] $defaultToggledColumns */
    #[Locked]
    public array $defaultToggledColumns = [];
    #[Locked]
    public ?string $createText = null;
    #[Locked]
    public ?string $create = null;

    /**
     * @param  string  $title
     * @param  string  $defaultSortingColumn
     * @param  string[]  $defaultToggledColumns
     * @param  string  $defaultSortingDirection
     * @param  bool  $verbose
     * @return void
     */
    public function mount(
        string $title,
        string $pageName = 'page',
        string $defaultSortingColumn = 'created_at',
        array $defaultToggledColumns = ['deleted_at', 'created_at'],
        string $defaultSortingDirection = 'desc',
        ?string $create = null,
        ?string $createText = null,
        bool $verbose = false
    ): void {
        $this->title = $title;
        $this->pageName = $pageName;
        $this->defaultSortingColumn = $defaultSortingColumn;
        $this->defaultToggledColumns = $defaultToggledColumns;
        $this->defaultSortingDirection = $defaultSortingDirection;
        $this->create = $create;
        $this->createText = $createText;
        $this->verbose = $verbose;
    }

    public static function reload(Component $component = null): void
    {
        if (!$component) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            if (isset($trace[1]['object']) && $trace[1]['object'] instanceof Component) {
                $component = $trace[1]['object'];
            }
        }

        if ($component) {
            $component->dispatch('flux-tables::table:refresh')->to(SimpleTable::class);
        }
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

        if ($this->verbose) {
            $sql->dumpRawSql();
        }

        return $sql->paginate($this->getPaginationValue(), pageName: $this->getPaginationName());
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
            ->createComponent($this->create)
            ->createText($this->createText)
            ->filters($filters)
            ->columns($columns);
    }

    /**
     * @return string
     */
    public function defaultSortingColumn(): string
    {
        return $this->defaultSortingColumn;
    }

    /**
     * @return string[]
     */
    public function defaultToggledColumns(): array
    {
        return $this->defaultToggledColumns;
    }

    public function defaultSortingDirection(): string
    {
        return $this->defaultSortingDirection;
    }

    public function getPaginationOptions(): array
    {
        return [
            10, 15, 25, 50
        ];
    }

    public function defaultPaginationValue(): int
    {
        return 10;
    }
}
