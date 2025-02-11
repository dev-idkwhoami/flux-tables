<?php

namespace Idkwhoami\FluxTables\Livewire;

use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn;
use Idkwhoami\FluxTables\Concretes\Column\TextColumn;
use Idkwhoami\FluxTables\Concretes\Filter\DateRangeFilter;
use Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Enums\DeletionState;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasTable;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ExampleTable extends Component
{
    use HasTable, HasFilters;

    public string $eloquentModel;

    public array $hiddenColumns = [];

    public function mount(string $model): void
    {
        $this->eloquentModel = $model;
    }

    public function render(): View
    {
        return view('flux-tables::livewire.table.example');
    }

    public function getQuery(): Builder
    {
        $query = $this->eloquentModel::query();

        if ($this->table->hasFilters()) {
            $query->tap(
                fn(Builder $query) => collect($this->table->getFilters())
                    ->each(fn($filter) => $filter->apply($query))
            );
        }

        return $query;
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        $sql = $this->getQuery();

        $sql->dumpRawSql();

        return $sql->paginate();
    }

    public function table(): Table
    {
        return EloquentTable::make('users')
            ->label('Users')
            ->filters([
                DeletedFilter::make('deleted')
                    ->default(DeletionState::WithoutDeleted->value),
                DateRangeFilter::make('created_at')
                    ->default([null, null])
                    ->property('created_at'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->property('name'),
                TextColumn::make('email')
                    ->label('Email')
                    ->property('email'),
                DatetimeColumn::make('created_at')
                    ->label('Created At')
                    ->property('created_at'),
            ]);
    }
}
