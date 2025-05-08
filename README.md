# Flux Tables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idkwhoami/flux-tables.svg?style=flat-square)](https://packagist.org/packages/idkwhoami/flux-tables)

This package is a simple wrapper around [Flux UI](http://fluxui.dev) for a quick and customizable way to create tables.

> [!IMPORTANT]
> This package is NOT "in development". If I need to extend it i will do so. Feel free to fork it or use it as is.
But feature requests are probably being ignored. Same for pull requests

> [!WARNING]
> This package is meant to be used in combination with a PostgreSQL database as it provides utils that most databases dont have. For example json path walking & vectors.

## Installation

You can install the package via composer:

```bash
composer require idkwhoami/flux-tables
```

Now this artisan command
```bash
php artisan flux-tables:install
```

This will use the `flux:icon` artisan command to fetch the [Lucide](https://lucide.dev/) icons used in the package.

## Usage

The basic usage example is as follows:

```php
$filters = [
    \Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter::make('deleted')
        ->label('Deletion State')
        ->default(\Idkwhoami\FluxTables\Enums\DeletionState::WithoutDeleted->value),
    \Idkwhoami\FluxTables\Concretes\Filter\DateRangeFilter::make('created')
        ->property('created_at')
        ->label('Created'),
    \Idkwhoami\FluxTables\Concretes\Filter\ValuePresentFilter::make('email_verified')
        ->property('email_verified_at')
        ->label('Exclude unverified')
        ->description('Hide all users that haven\'t verified their email address.')
        ->pillContent('Unverified excluded'),
    \Idkwhoami\FluxTables\Concretes\Filter\BooleanFilter::make('banned')
        ->property('banned'),
];

$columns = [
    \Idkwhoami\FluxTables\Concretes\Column\ComponentColumn::make('name')
        ->label('Username')
        ->sortable()
        ->searchable()
        ->component('columns.user-name-input')
        ->property('name'),
    \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('created')
        ->humanReadable()
        ->label("Created")
        ->sortable()
        ->property('created_at'),
    \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('posts')
        ->count()
        ->label('Posts')
        ->relation('posts')
        ->property('posts_count'),
    \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('email_verified')
        ->label("Email Verified At")
        ->sortable()
        ->property('email_verified_at'),
    \Idkwhoami\FluxTables\Concretes\Column\BooleanColumn::make('banned')
        ->label('Banned')
        ->property('banned'),
    \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('deleted')
        ->label("Deleted")
        ->default('n/a')
        ->property('deleted_at'),
    \Idkwhoami\FluxTables\Concretes\Column\ActionColumn::make('actions')
        ->actions([
            Idkwhoami\FluxTables\Abstracts\Action\ModalAction::make('open')
                ->label('Open')
                ->icon('arrow-top-right-on-square')
                ->link()
                ->component('user-delete-confirmation'),
            Idkwhoami\FluxTables\Abstracts\Action\DirectAction::make('delete')
                ->visible(fn(\Illuminate\Database\Eloquent\Model $model) => auth()->user()->isNot($model) && !$model->deleted_at)
                ->label('Delete')
                ->icon('trash-2')
                ->operation(\Idkwhoami\FluxTables\Concretes\Operation\DeleteOperation::make('delete')),
            Idkwhoami\FluxTables\Abstracts\Action\DirectAction::make('restore')
                ->visible(fn(\Illuminate\Database\Eloquent\Model $model) => auth()->user()->isNot($model) && $model->deleted_at)
                ->label('Restore')
                ->icon('rotate-ccw')
                ->operation(\Idkwhoami\FluxTables\Concretes\Operation\RestoreOperation::make('restore')),
        ]),
];
```

```bladehtml
<livewire:flux-simple-table create="user-create-form" title="Users" :model="\App\Models\User::class" :default-toggled-columns="['created']" :$filters :$columns />
```

This will use the "generic" table component supplied by the package.

Although the heart of this package lies within the numerous traits to allow you to build YOUR table component very quickly.

This is the component class you can simply extend the functionality using traits like `HasSorting`, `HasFilters`, etc.
```php
class MyTable extends Component
{
    use HasEloquentTable;
    use HasSearch;
    use WithPagination;

    public function render(): View
    {
        return view('livewire.my-table');
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        return $this->getQuery()->paginate();
    }

    #[Computed]
    public function columns(): array
    {
        return [
            \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('name')
                ->label('Username')
                ->property('name')
                ->searchable()
                ->sortable(),
        ];
    }

    #[Computed]
    public function filters(): array
    {
        return [
            \Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter::make('deleted')
                ->label('Deleted')
                ->default(\Idkwhoami\FluxTables\Enums\DeletionState::WithoutDeleted->value),
        ];
    }

    public function table(string $model, array $columns = [], array $filters = []): Table
    {
        return EloquentTable::make('mytable')
            ->model($model)
            ->columns($this->columns)
            ->filters($this->filters);
    }

    public function getQuery(): Builder
    {
        $sql =  $this->eloquentModel::query();

        $this->applySearch($sql);
        $this->applyRelations($sql);

        $sql->dumpRawSql();

        return $sql;
    }
}
```
This is the component blade view. Depending on the features you can design them where and however you need.
```bladehtml
<div class="flex w-full flex-col space-y-2">
    <div class="flex flex-col gap-y-2">
        <div class="flex gap-x-3">
            @if($this->table->hasLabel())
                <flux:heading class="content-center" level="1" size="xl">
                    {{ $this->table->getLabel() }}
                </flux:heading>
            @endif
            <flux:spacer/>
            <div class="w-42">
                <flux:input clearable size="sm" type="text" icon="search" wire:model.live.debounce="search"/>
            </div>
        </div>
    </div>

    <flux:table :paginate="$this->models">

        <flux:table.columns>
            @foreach($this->table->getColumns() as $column)
                <flux:table.column :key="$column->getName()">
                    {{ $column->getLabel() }}
                </flux:table.column>
            @endforeach
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->models as $model)
                <flux:table.row
                    wire:loading.class="animate-pulse"
                    :key="$model->getKey()">
                    @foreach($this->table->getColumns() as $column)
                        <flux:table.cell>
                            {{ $column->render($model) }}
                        </flux:table.cell>
                    @endforeach
                </flux:table.row>
            @endforeach
        </flux:table.rows>

    </flux:table>

</div>

```

## TODO

- Nothing for now

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see fork the project and adjust as much as u want to. But please dont expect me to answer to any PR or Issues.


## Credits
- [Maximilian Oswald](https://github.com/dev-idkwhoami)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
