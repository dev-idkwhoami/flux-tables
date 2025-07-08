# Flux Tables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/idkwhoami/flux-tables.svg?style=flat-square)](https://packagist.org/packages/idkwhoami/flux-tables)

A comprehensive Laravel Livewire table component package built on top of [Flux UI](http://fluxui.dev) that provides a modular, customizable way to create data tables with advanced features like filtering, sorting, searching, and PostgreSQL-specific functionality.

> [!IMPORTANT]
> This package is NOT "in development". If I need to extend it i will do so. Feel free to fork it or use it as is.
But feature requests are probably being ignored. Same for pull requests

> [!WARNING]
> This package is meant to be used in combination with a PostgreSQL database as it provides utils that most databases dont have. For example json path walking & vectors.

## What This Package Does

Flux Tables provides a complete table solution for Laravel applications with:

- **Modular Architecture**: Use traits to build custom table components with only the features you need
- **Rich Column Types**: Text, DateTime, Boolean, JSON, Component, Action, and List columns
- **Advanced Filtering**: Boolean, Date Range, Select, Value Present, and Deleted filters with session persistence
- **PostgreSQL Features**: JSON path querying, advanced sorting, and database-specific optimizations
- **Livewire Integration**: Reactive components with real-time updates and state management
- **Flux UI Integration**: Beautiful, accessible UI components out of the box

## How It's Meant to Be Used

The package offers two main approaches:

1. **Quick Setup**: Use the pre-built `SimpleTable` component for rapid development
2. **Custom Components**: Build your own table components using the modular trait system

The modular design allows you to pick and choose features:
- `HasEloquentTable` - Core Eloquent query functionality
- `HasFilters` - Filter management and application
- `HasSorting` - Column sorting with PostgreSQL JSON support
- `HasSearch` - Global search functionality
- `HasActions` - Row-level actions (edit, delete, etc.)
- `HasToggleableColumns` - Show/hide columns
- `HasDynamicPagination` - Configurable pagination

## Installation

You can install the package via composer:

```bash
composer require idkwhoami/flux-tables
```

Run the installation command:
```bash
php artisan flux-tables:install
```

This will use the `flux:icon` artisan command to fetch the [Lucide](https://lucide.dev/) icons used in the package.

## Quick Start

Here's a basic example using the pre-built SimpleTable component:

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

```blade
<livewire:flux-simple-table 
    create="user-create-form" 
    title="Users" 
    :model="\App\Models\User::class" 
    :default-toggled-columns="['created']" 
    :$filters 
    :$columns 
/>
```

## API Documentation

### Abstract Classes

#### Action (`Idkwhoami\FluxTables\Abstracts\Action\Action`)

Base class for all table actions.

**Methods:**
- `label(string $label): static` - Set the action label
- `icon(string $icon): static` - Set the action icon (Lucide icon name)
- `variant(?string $variant): static` - Set the action variant (default, danger, outline, filled, primary, ghost, subtle)
- `visible(Closure|bool $visible): static` - Set visibility condition
- `access(Closure|bool $access): static` - Set access control condition
- `link(bool $link = true): static` - Render as link instead of button
- `render(mixed $id): string|HtmlString|View|null` - Abstract method to render the action

#### Column (`Idkwhoami\FluxTables\Abstracts\Column\Column`)

Base class for all table columns.

**Methods:**
- `label(?string $label): static` - Set the column label
- `sortable(bool $sortable = true): static` - Make column sortable
- `searchable(bool $searchable = true): static` - Make column searchable
- `toggleable(bool $toggleable): static` - Make column toggleable
- `visible(bool|Closure $visible = true): static` - Set visibility condition
- `render(object $value): string|HtmlString|View|null` - Abstract method to render the column

#### PropertyColumn (`Idkwhoami\FluxTables\Abstracts\Column\PropertyColumn`)

Extends Column for property-based columns.

**Methods:**
- `property(string $property): static` - Set the model property
- `relation(string $relation): static` - Set the relation name
- `count(bool $count = true): static` - Count relation items
- `default(mixed $default): static` - Set default value
- `transform(Closure $transform): static` - Transform the value before rendering

#### Filter (`Idkwhoami\FluxTables\Abstracts\Filter\Filter`)

Base class for all table filters.

**Methods:**
- `label(string $label): static` - Set the filter label
- `default(mixed $default): static` - Set default value
- `visible(Closure|bool $visible): static` - Set visibility condition
- `apply(Builder $query): void` - Abstract method to apply filter to query
- `component(): string` - Abstract method to return Livewire component name
- `renderPill(): string|HtmlString|View` - Abstract method to render filter pill

#### Table (`Idkwhoami\FluxTables\Abstracts\Table\Table`)

Base class for table configuration.

**Methods:**
- `columns(array $columns): static` - Set table columns
- `filters(array $filters): static` - Set table filters
- `getColumns(): array` - Get visible columns
- `getFilters(): array` - Get visible filters
- `getColumn(string $key): Column` - Get specific column by key

### Concrete Column Implementations

#### TextColumn (`Idkwhoami\FluxTables\Concretes\Column\TextColumn`)

Simple text column for displaying string values.

#### DatetimeColumn (`Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn`)

Column for displaying dates and times.

**Methods:**
- `format(string $format): static` - Set date format (default: 'm/d/Y H:i:s')
- `humanReadable(bool $readable = true): static` - Display as human-readable format (e.g., "2 hours ago")

#### BooleanColumn (`Idkwhoami\FluxTables\Concretes\Column\BooleanColumn`)

Column for displaying boolean values as badges or icons.

#### JsonColumn (`Idkwhoami\FluxTables\Concretes\Column\JsonColumn`)

PostgreSQL-specific column for querying JSON data.

**Methods:**
- `path(array|string $path): static` - Set JSON path (e.g., 'user.name' or ['user', 'name'])
- `type(JsonPropertyType $type): static` - Set PostgreSQL cast type (text, integer, boolean, etc.)

#### ComponentColumn (`Idkwhoami\FluxTables\Concretes\Column\ComponentColumn`)

Column that renders a custom Blade component.

**Methods:**
- `component(string $component): static` - Set the component name

#### ActionColumn (`Idkwhoami\FluxTables\Concretes\Column\ActionColumn`)

Column for displaying row actions.

**Methods:**
- `actions(array $actions): static` - Set the actions array

#### ListColumn (`Idkwhoami\FluxTables\Concretes\Column\ListColumn`)

Column for displaying arrays or collections as lists.

### Concrete Filter Implementations

#### BooleanFilter (`Idkwhoami\FluxTables\Concretes\Filter\BooleanFilter`)

Filter for boolean values with true/false/all options.

#### DateRangeFilter (`Idkwhoami\FluxTables\Concretes\Filter\DateRangeFilter`)

Filter for date ranges with start and end date inputs.

**Methods:**
- `property(string $property): static` - Set the date property to filter

#### DeletedFilter (`Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter`)

Filter for soft-deleted models with options for all/only deleted/without deleted.

#### SelectFilter (`Idkwhoami\FluxTables\Concretes\Filter\SelectFilter`)

Filter with predefined options in a select dropdown.

**Methods:**
- `options(array $options): static` - Set the available options

#### ValuePresentFilter (`Idkwhoami\FluxTables\Concretes\Filter\ValuePresentFilter`)

Filter to show/hide records based on whether a field has a value.

**Methods:**
- `property(string $property): static` - Set the property to check
- `description(string $description): static` - Set filter description
- `pillContent(string $content): static` - Set the pill display text

### Action Implementations

#### DirectAction (`Idkwhoami\FluxTables\Abstracts\Action\DirectAction`)

Action that executes immediately when clicked.

**Methods:**
- `operation(Operation $operation): static` - Set the operation to execute

#### ModalAction (`Idkwhoami\FluxTables\Abstracts\Action\ModalAction`)

Action that opens a modal component.

**Methods:**
- `component(string $component): static` - Set the modal component name

### Operations

#### DeleteOperation (`Idkwhoami\FluxTables\Concretes\Operation\DeleteOperation`)

Operation for soft-deleting models.

#### RestoreOperation (`Idkwhoami\FluxTables\Concretes\Operation\RestoreOperation`)

Operation for restoring soft-deleted models.

#### RouteOperation (`Idkwhoami\FluxTables\Concretes\Operation\RouteOperation`)

Operation for redirecting to a route.

**Methods:**
- `route(string $route): static` - Set the route name
- `parameters(array $parameters): static` - Set route parameters

### Traits for Custom Components

#### HasEloquentTable

Core trait for Eloquent-based tables.

**Methods:**
- `table(string $model, array $columns, array $filters): Table` - Configure the table
- `getQuery(): Builder` - Get the base query
- `optimizeSelects(bool $optimize): static` - Enable/disable select optimization
- `applyRelations(Builder $query): void` - Apply relation joins
- `applyColumns(Builder $query): void` - Apply column selections

#### HasFilters

Adds filtering functionality.

**Methods:**
- `applyFilters(Builder $query): void` - Apply active filters to query
- `getFilters(): array` - Get all filters
- `getActiveFilters(): array` - Get currently active filters
- `resetFilter(string $filter): void` - Reset specific filter
- `resetFilters(): void` - Reset all filters
- `hasActiveFilters(): bool` - Check if any filters are active

#### HasSorting

Adds sorting functionality with PostgreSQL JSON support.

**Properties:**
- `?string $sortingColumn` - Current sort column
- `?string $sortingDirection` - Current sort direction

**Methods:**
- `applySorting(Builder $query): void` - Apply sorting to query
- `sort(string $column): void` - Sort by column (cycles through asc/desc/none)
- `resetSorting(): void` - Reset sorting
- `getSortingColumn(): string` - Get current sort column
- `getSortingDirection(): string` - Get current sort direction
- `defaultSortingColumn(): string` - Default sort column
- `defaultSortingDirection(): string` - Default sort direction

#### HasSearch

Adds global search functionality.

**Properties:**
- `string $search` - Current search term

**Methods:**
- `applySearch(Builder $query): void` - Apply search to query
- `resetSearch(): void` - Clear search

#### HasActions

Adds row-level actions functionality.

**Methods:**
- `getActions(): array` - Get all actions
- `executeAction(string $action, mixed $id): void` - Execute an action

#### HasToggleableColumns

Adds column visibility toggling.

**Methods:**
- `toggleColumn(string $column): void` - Toggle column visibility
- `getToggledColumns(): array` - Get currently visible columns
- `defaultToggledColumns(): array` - Default visible columns

#### HasDynamicPagination

Adds configurable pagination.

**Methods:**
- `getPaginationOptions(): array` - Get pagination size options
- `defaultPaginationValue(): int` - Get default pagination size

### Enums

#### DeletionState (`Idkwhoami\FluxTables\Enums\DeletionState`)

- `All` - Show all records
- `OnlyDeleted` - Show only soft-deleted records  
- `WithoutDeleted` - Show only non-deleted records

#### JsonPropertyType (`Idkwhoami\FluxTables\Enums\JsonPropertyType`)

PostgreSQL cast types for JSON properties:
- `Text` - Cast as text
- `Integer` - Cast as integer
- `Boolean` - Cast as boolean
- `Numeric` - Cast as numeric

### Livewire Components

#### SimpleTable (`Idkwhoami\FluxTables\Livewire\SimpleTable`)

Pre-built table component with all features enabled.

**Properties:**
- `string $title` - Table title
- `string $model` - Eloquent model class
- `array $columns` - Column definitions
- `array $filters` - Filter definitions
- `?string $create` - Create component name
- `array $defaultToggledColumns` - Initially visible columns

## Custom Table Example

Here's how to create a custom table component using the modular trait system. This example demonstrates building a comprehensive table with all available features:

### 1. Create the Livewire Component

```php
<?php

namespace App\Livewire;

use App\Models\User;
use Idkwhoami\FluxTables\Abstracts\Table\Table;
use Idkwhoami\FluxTables\Concretes\Table\EloquentTable;
use Idkwhoami\FluxTables\Traits\HasEloquentTable;
use Idkwhoami\FluxTables\Traits\HasFilters;
use Idkwhoami\FluxTables\Traits\HasSearch;
use Idkwhoami\FluxTables\Traits\HasSorting;
use Idkwhoami\FluxTables\Traits\HasActions;
use Idkwhoami\FluxTables\Traits\HasToggleableColumns;
use Idkwhoami\FluxTables\Traits\HasDynamicPagination;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class CustomUserTable extends Component
{
    use HasEloquentTable;
    use HasFilters;
    use HasSorting;
    use HasSearch;
    use HasActions;
    use HasToggleableColumns;
    use HasDynamicPagination;
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        // Livewire automatically calls trait mount methods
        // No manual initialization needed
    }

    public function render(): View
    {
        return view('livewire.custom-user-table');
    }

    #[Computed]
    public function models(): LengthAwarePaginator
    {
        return $this->getQuery()->paginate($this->getPaginationValue());
    }

    public function getQuery(): Builder
    {
        $query = User::query();

        // Apply the modular functionality
        $this->applySearch($query);
        $this->applyFilters($query);
        $this->applySorting($query);
        $this->applyRelations($query);
        $this->applyColumns($query);

        return $query;
    }

    protected function getColumns(): array
    {
        return [
            \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('name')
                ->label('Name')
                ->property('name')
                ->searchable()
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('email')
                ->label('Email')
                ->property('email')
                ->searchable()
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\ComponentColumn::make('avatar')
                ->label('Avatar')
                ->component('columns.user-avatar')
                ->property('avatar_url')
                ->toggleable(false), // Always visible

            \Idkwhoami\FluxTables\Concretes\Column\TextColumn::make('posts')
                ->count()
                ->label('Posts')
                ->relation('posts')
                ->property('posts_count')
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('created_at')
                ->label('Created')
                ->property('created_at')
                ->humanReadable()
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('email_verified_at')
                ->label('Email Verified')
                ->property('email_verified_at')
                ->default('Not verified')
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\BooleanColumn::make('banned')
                ->label('Banned')
                ->property('banned')
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\JsonColumn::make('preferences')
                ->label('Theme')
                ->property('preferences')
                ->path(['ui', 'theme'])
                ->type(\Idkwhoami\FluxTables\Enums\JsonPropertyType::Text)
                ->default('default'),

            \Idkwhoami\FluxTables\Concretes\Column\DatetimeColumn::make('deleted_at')
                ->label('Deleted')
                ->property('deleted_at')
                ->default('n/a')
                ->sortable(),

            \Idkwhoami\FluxTables\Concretes\Column\ActionColumn::make('actions')
                ->label('Actions')
                ->actions([
                    \Idkwhoami\FluxTables\Abstracts\Action\ModalAction::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->link()
                        ->component('user-edit-modal'),

                    \Idkwhoami\FluxTables\Abstracts\Action\ModalAction::make('view')
                        ->label('View Details')
                        ->icon('eye')
                        ->variant('outline')
                        ->component('user-details-modal'),

                    \Idkwhoami\FluxTables\Abstracts\Action\DirectAction::make('ban')
                        ->visible(fn(\Illuminate\Database\Eloquent\Model $model) => 
                            auth()->user()->isNot($model) && !$model->banned && !$model->deleted_at)
                        ->label('Ban')
                        ->icon('user-x')
                        ->variant('danger')
                        ->operation(\Idkwhoami\FluxTables\Concretes\Operation\RouteOperation::make('ban')
                            ->route('admin.users.ban')
                            ->parameters(['user' => fn($model) => $model->id])),

                    \Idkwhoami\FluxTables\Abstracts\Action\DirectAction::make('delete')
                        ->visible(fn(\Illuminate\Database\Eloquent\Model $model) => 
                            auth()->user()->isNot($model) && !$model->deleted_at)
                        ->label('Delete')
                        ->icon('trash-2')
                        ->variant('danger')
                        ->operation(\Idkwhoami\FluxTables\Concretes\Operation\DeleteOperation::make('delete')),

                    \Idkwhoami\FluxTables\Abstracts\Action\DirectAction::make('restore')
                        ->visible(fn(\Illuminate\Database\Eloquent\Model $model) => 
                            auth()->user()->isNot($model) && $model->deleted_at)
                        ->label('Restore')
                        ->icon('rotate-ccw')
                        ->operation(\Idkwhoami\FluxTables\Concretes\Operation\RestoreOperation::make('restore')),
                ]),
        ];
    }

    protected function getFilters(): array
    {
        return [
            \Idkwhoami\FluxTables\Concretes\Filter\DeletedFilter::make('deleted')
                ->label('Deletion State')
                ->default(\Idkwhoami\FluxTables\Enums\DeletionState::WithoutDeleted->value),

            \Idkwhoami\FluxTables\Concretes\Filter\ValuePresentFilter::make('verified')
                ->label('Email Verified')
                ->property('email_verified_at')
                ->description('Show only verified users')
                ->pillContent('Verified only'),

            \Idkwhoami\FluxTables\Concretes\Filter\BooleanFilter::make('banned')
                ->label('Banned Status')
                ->property('banned'),

            \Idkwhoami\FluxTables\Concretes\Filter\DateRangeFilter::make('created')
                ->label('Registration Date')
                ->property('created_at'),

            \Idkwhoami\FluxTables\Concretes\Filter\SelectFilter::make('role')
                ->label('User Role')
                ->property('role')
                ->options([
                    'admin' => 'Administrator',
                    'moderator' => 'Moderator',
                    'user' => 'Regular User',
                ]),
        ];
    }

    // Required by HasSorting trait
    public function defaultSortingColumn(): string
    {
        return 'created_at';
    }

    public function defaultSortingDirection(): string
    {
        return 'desc';
    }

    // Required by HasToggleableColumns trait
    public function defaultToggledColumns(): array
    {
        return ['name', 'email', 'created_at', 'actions']; // Default visible columns
    }

    // Required by HasDynamicPagination trait
    public function defaultPaginationValue(): int
    {
        return 15;
    }

    public function getPaginationOptions(): array
    {
        return [10, 15, 25, 50, 100];
    }
}
```

### 2. Create the Blade View

Create `resources/views/livewire/custom-user-table.blade.php`:

```blade
<div class="space-y-4">
    {{-- Header with title, search, filters, and controls --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Users Management</h2>

        <div class="flex items-center space-x-3">
            {{-- Search Input --}}
            <div class="w-64">
                <flux:input 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    icon="search"
                    clearable
                />
            </div>

            {{-- Pagination Size Selector --}}
            <flux:select 
                wire:model.live="paginationValue"
                placeholder="Per page"
                class="w-24"
            >
                @foreach($this->getPaginationOptions() as $option)
                    <flux:option value="{{ $option }}">{{ $option }}</flux:option>
                @endforeach
            </flux:select>

            {{-- Column Toggle Button --}}
            <flux:modal.trigger name="columns-modal">
                <flux:button variant="outline" icon="columns">
                    Columns
                </flux:button>
            </flux:modal.trigger>

            {{-- Filter Button --}}
            @if($this->table->hasFilters())
                <flux:modal.trigger name="filters-modal">
                    <flux:button variant="outline" icon="filter">
                        Filters
                        @if($this->hasActiveFilters())
                            <flux:badge size="sm" color="blue">{{ count($this->getActiveFilters()) }}</flux:badge>
                        @endif
                    </flux:button>
                </flux:modal.trigger>
            @endif

            {{-- Create User Button --}}
            <flux:modal.trigger name="create-user-modal">
                <flux:button icon="plus">
                    Add User
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Active Filter Pills --}}
    @if($this->hasActiveFilters())
        <div class="flex flex-wrap gap-2">
            @foreach($this->getActiveFilters() as $filter)
                <flux:badge 
                    color="blue" 
                    size="sm"
                    wire:click="resetFilter('{{ $filter->getName() }}')"
                    class="cursor-pointer hover:bg-blue-600"
                >
                    {{ $filter->renderPill() }}
                    <flux:icon.x-mark class="w-3 h-3 ml-1" />
                </flux:badge>
            @endforeach

            <flux:button 
                variant="ghost" 
                size="sm" 
                wire:click="resetFilters"
                class="text-gray-500 hover:text-gray-700"
            >
                Clear all filters
            </flux:button>
        </div>
    @endif

    {{-- Table --}}
    <flux:table :paginate="$this->models">
        <flux:columns>
            @foreach($this->getToggledColumns() as $column)
                <flux:column 
                    :sortable="$column->isSortable()"
                    :sorted="$this->getSortingColumn() === $column->getName() ? $this->getSortingDirection() : null"
                    wire:click="sort('{{ $column->getName() }}')"
                    class="cursor-pointer hover:bg-gray-50"
                >
                    <div class="flex items-center space-x-1">
                        <span>{{ $column->getLabel() }}</span>
                        @if($column->isSortable())
                            @if($this->getSortingColumn() === $column->getName())
                                @if($this->getSortingDirection() === 'asc')
                                    <flux:icon.chevron-up class="w-4 h-4" />
                                @else
                                    <flux:icon.chevron-down class="w-4 h-4" />
                                @endif
                            @else
                                <flux:icon.chevron-up-down class="w-4 h-4 text-gray-400" />
                            @endif
                        @endif
                    </div>
                </flux:column>
            @endforeach
        </flux:columns>

        <flux:rows>
            @foreach($this->models as $user)
                <flux:row :key="$user->id" class="hover:bg-gray-50">
                    @foreach($this->getToggledColumns() as $column)
                        <flux:cell>
                            {!! $column->render($user) !!}
                        </flux:cell>
                    @endforeach
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    {{-- Column Toggle Modal --}}
    <flux:modal name="columns-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Toggle Columns</flux:heading>
                <flux:subheading>Show or hide table columns</flux:subheading>
            </div>

            <div class="space-y-3">
                @foreach($this->table->getColumns() as $column)
                    @if($column->isToggleable())
                        <flux:checkbox 
                            wire:model.live="toggledColumns.{{ $column->getName() }}"
                            label="{{ $column->getLabel() }}"
                        />
                    @endif
                @endforeach
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Close</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Filter Modal --}}
    @if($this->table->hasFilters())
        <flux:modal name="filters-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Filters</flux:heading>
                    <flux:subheading>Refine your search results</flux:subheading>
                </div>

                @foreach($this->getFilters() as $filter)
                    <div>
                        <livewire:dynamic-component 
                            :component="$filter->component()" 
                            :filter="$filter"
                            :key="$filter->getName()"
                        />
                    </div>
                @endforeach

                <div class="flex justify-end space-x-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="resetFilters" variant="danger">
                        Clear All Filters
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    {{-- Create User Modal (placeholder) --}}
    <flux:modal name="create-user-modal" class="md:w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Create New User</flux:heading>
                <flux:subheading>Add a new user to the system</flux:subheading>
            </div>

            {{-- This would contain your user creation form --}}
            <div class="text-center py-8 text-gray-500">
                <flux:icon.user-plus class="w-12 h-12 mx-auto mb-4" />
                <p>User creation form would go here</p>
                <p class="text-sm">Implement your user creation component</p>
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button>Create User</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
```

### 3. Register the Component

Add to your `AppServiceProvider` or create a dedicated service provider:

```php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::component('custom-user-table', CustomUserTable::class);
}
```

### 4. Use in Your Blade Templates

```blade
<livewire:custom-user-table />
```

### Key Features Demonstrated

This comprehensive example showcases all available features:

1. **Complete Trait Integration**: All major traits working together (HasEloquentTable, HasFilters, HasSorting, HasSearch, HasActions, HasToggleableColumns, HasDynamicPagination)
2. **Rich Column Types**: Text, Component, DateTime, Boolean, JSON, and Action columns with various configurations
3. **Advanced Filtering**: Multiple filter types including DeletedFilter, ValuePresentFilter, BooleanFilter, DateRangeFilter, and SelectFilter
4. **Interactive Actions**: Both modal actions (edit, view details) and direct actions (ban, delete, restore) with conditional visibility
5. **Dynamic UI Controls**: Column toggles, pagination size selection, search functionality, and filter management
6. **PostgreSQL Features**: JSON column with path querying and type casting
7. **User Experience**: Hover effects, sorting indicators, filter pills, and responsive modals

### Benefits of This Approach

1. **Modularity**: Pick and choose only the traits you need for simpler implementations
2. **Full Feature Set**: This example shows how all features work together seamlessly
3. **Customization**: Complete control over UI, behavior, and data presentation
4. **Performance**: Optimized queries with relation handling, select optimization, and efficient pagination
5. **Flexibility**: Easy to extend, modify, or simplify based on your specific requirements
6. **Reusability**: Use this pattern across different models and use cases
7. **Maintainability**: Clear separation of concerns with trait-based architecture

### Customization Options

You can adapt this example by:

- **Simplifying**: Remove traits you don't need (e.g., remove HasActions for read-only tables)
- **Extending**: Add custom column types by extending the abstract Column class
- **Custom Filters**: Build specialized filters by extending the abstract Filter class
- **Custom Operations**: Create new operations for DirectAction beyond the provided ones
- **UI Modifications**: Customize the Blade template for your design system
- **Model-Specific Features**: Add model-specific logic in visibility conditions and transformations
- **Integration**: Connect with your existing authentication, authorization, and business logic

This modular approach allows you to start with this comprehensive example and adapt it to your exact needs, whether you want a full-featured admin interface or a simple data display table.

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
