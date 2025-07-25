<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Abstracts\Column\Column;
use Idkwhoami\FluxTables\Abstracts\Filter\Filter;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Column\ComponentColumn;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\HasActions;
use Idkwhoami\FluxTables\Traits\HasDynamicPagination;
use Idkwhoami\FluxTables\Traits\HasEloquentTable;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasSearch;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasTableCreate;
use Idkwhoami\FluxTables\Traits\HasToggleableColumns;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Idkwhoami\FluxTables\Concretes\Table\SimpleTable as BaseTable;

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
    use HasTableCreate;

    /** @var array<string, string> $listeners */
    protected $listeners = [
        'flux-tables::table::refresh' => '$refresh'
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
    public bool $createDismissible = false;

    /**
     * @param  string  $title
     * @param  string  $pageName
     * @param  string  $searchName
     * @param  string  $defaultSortingColumn
     * @param  string[]  $defaultToggledColumns
     * @param  string  $defaultSortingDirection
     * @param  string|null  $create
     * @param  string|null  $createText
     * @param  bool  $verbose
     * @return void
     */
    public function mount(
        string $title,
        string $pageName = 'page',
        string $searchName = 'search',
        string $defaultSortingColumn = 'created_at',
        array $defaultToggledColumns = ['deleted_at', 'created_at'],
        string $defaultSortingDirection = 'desc',
        ?string $create = null,
        ?string $createText = null,
        string $createModalClasses = 'w-full',
        string $createModalVariant = 'default',
        ?string $createModalPosition = null,
        bool $createDismissible = false,
        bool $verbose = false
    ): void {
        $this->title = $title;
        $this->pageName = $pageName;
        $this->searchName = $searchName;
        $this->defaultSortingColumn = $defaultSortingColumn;
        $this->defaultToggledColumns = $defaultToggledColumns;
        $this->defaultSortingDirection = $defaultSortingDirection;
        $this->createComponent = $create;
        $this->createText = $createText;
        $this->createModalClasses = $createModalClasses;
        $this->createModalVariant = $createModalVariant;
        $this->createModalPosition = $createModalPosition;
        $this->createDismissible = $createDismissible;
        $this->verbose = $verbose;
    }

    #[On('flux-tables::table::refresh')]
    public function propagateTableReload(): void
    {
        $componentColumns = array_filter(
            $this->table->getColumns(),
            fn (Column $column) => $column instanceof ComponentColumn
        );
        /** @var ComponentColumn $column */
        foreach ($componentColumns as $column) {
            $this->dispatch('flux-tables::table::refresh')->component($column->getComponent());
        }
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
            $component->dispatch('flux-tables::table::refresh')->to(SimpleTable::class);
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
        return BaseTable::make(strtolower(class_basename($model)))
            ->label($this->title)
            ->model($model)
            ->createComponent($this->createComponent)
            ->createText($this->createText)
            ->createModalClasses($this->createModalClasses)
            ->createModalFlyoutPosition($this->createModalPosition)
            ->createModalVariant($this->createModalVariant)
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
