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

## Usage

In a service provider's `boot()` method:
```php
FluxTables::create(User::class)
            ->table(
                fn (Table $table) => $table
                    ->paginationOptions([5, 10, 20], 10)
                    ->actions([
                        ComponentAction::make('create')
                            ->icon('plus')
                            ->position(ActionPosition::TITLE_INLINE)
                            ->component('create-user')
                            ->label('Create User'),
                    ])
                    ->columns([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->label('Name'),
                        TextColumn::make('contact.first_name')
                            ->label('User')
                            ->sortable()
                            ->searchable(),
                        TextColumn::make('teams.name')
                            ->label('Teams')
                            ->list()
                            ->searchable(),
                        TextColumn::make('updated_at')
                            ->label('Last Update')
                            ->transform(fn ($value) => $value->diffForHumans()),
                        ViewColumn::make('actions')
                            ->view('table.user-actions'),
                    ])
                    ->filters([
                        EqualsFilter::make('name')
                            ->label('Name equals')
                            ->callback(fn (Builder $query, mixed $value) => $query->where('users.name', '=', $value)),
                        SelectFilter::make('user')
                            ->label('Select User')
                            ->multiple()
                            ->options(fn () => User::all())
                            ->callback(fn (Builder $query, mixed $value) => $query->whereIn('users.id', $value)),
                        DateRangeFilter::make('createdBetween')
                            ->label('Created Between')
                            ->callback(fn (
                                Builder $query,
                                mixed $value
                            ) => count($value) == 2 ? $query->whereBetween('users.created_at', $value) : $query),
                    ])
            );
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see fork the project and adjust as much as u want to. But please dont expect me to answer to any PR or Issues.


## Credits
- [Maximilian Oswald](https://github.com/dev-idkwhoami)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
