# Flux Tables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idkwhoami/flux-tables.svg?style=flat-square)](https://packagist.org/packages/idkwhoami/flux-tables)

This package is a simple wrapper around [Flux UI](http://fluxui.dev) for a quick and customizable way to create tables.

> [!IMPORTANT]
> This package is NOT "in development". If I need to extend it i will do so. Feel free to fork it or use it as is.
But feature requests are probably being ignored. Same for pull requests

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

The very basic usage example is as follows:

```php
$columns = [
    \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('name')
        ->label('Username')
        ->property('name')
        ->searchable()
        ->sortable(),
];

$filters = [
    \Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter::make('deleted')
        ->label('Deleted')
        ->default(\Idkwhoami\FluxTables\Enums\DeletionState::WithoutDeleted->value),
];
```

```bladehtml
    @php
        <!-- PHP Code Above -->
    @endphp

    <livewire:flux-simple-table title="Users" :$columns :$filters :model="\App\Models\User::class"/>
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

- Interactive pagination amount on simple table

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see fork the project and adjust as much as u want to. But please dont expect me to answer to any PR or Issues.


## Credits
- [Maximilian Oswald](https://github.com/dev-idkwhoami)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
