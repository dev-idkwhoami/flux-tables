<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn;
use Idkwhoami\FluxTables\Concretes\Column\TextColumn;
use Idkwhoami\FluxTables\Concretes\Filter\DateRangeFilter;
use Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter;
use Idkwhoami\FluxTables\Concretes\Filter\SelectFilter;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Idkwhoami\FluxTables\Traits\HasEloquentTable;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasSearch;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasTable;
use Idkwhoami\FluxTables\Traits\HasToggleableColumns;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ExampleTable extends Component
{
    use HasEloquentTable, HasFilters, HasToggleableColumns, HasSearch, HasSorting, WithPagination;

    public function mount(): void
    {
    }

    public function render(): View
    {
        return view('flux-tables::livewire.table.example');
    }

    public function getQuery(): Builder
    {
        /** @var Builder $query */
        $query = $this->eloquentModel::query();

        if ($this->table->hasFilters()) {
            $query->tap(
                fn(Builder $query) => collect($this->table->getFilters())
                    ->each(fn($filter) => $filter->apply($query))
            );
        }

        $this->applySorting($query);
        $this->applySearch($query);

        return $query;
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        $sql = $this->getQuery();

        $sql->dumpRawSql();

        return $sql->paginate();
    }

    public function table(string $model): Table
    {
        return EloquentTable::make('users')
            ->label('Users')
            ->model($model)
            ->filters([
                DeletedFilter::make('deleted')
                    ->label('Deleted')
                    ->default(DeletionState::WithoutDeleted->value),
                DateRangeFilter::make('created_at')
                    ->default([null, null])
                    ->label('Created At')
                    ->property('created_at'),
                SelectFilter::make('email')
                    ->default([])
                    ->label('Email')
                    ->property('email'),
                SelectFilter::make('name')
                    ->default([])
                    ->label('Username')
                    ->property('name'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Name')
                    ->property('name'),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label('Email')
                    ->property('email'),
                DatetimeColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->property('created_at'),
            ]);
    }

    public function defaultSortingColumn(): string
    {
        return 'created_at';
    }

    public function defaultToggledColumns(): array
    {
        return [
            'created_at'
        ];
    }
}
