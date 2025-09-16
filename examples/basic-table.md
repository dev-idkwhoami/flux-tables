# Basic Table Example with New Configuration System

This example demonstrates the improved configuration system that supports both models and queries, dynamic values, and proper scope management.

## New Configuration Methods

The TableConfiguration class now provides three creation methods:

```php
// 1. Create empty configuration (set source later)
$config = TableConfiguration::make();

// 2. Create from model class
$config = TableConfiguration::forModel(\App\Models\User::class);

// 3. Create from existing query (preserves scopes and conditions)
$query = \App\Models\User::where('tenant_id', auth()->user()->tenant_id);
$config = TableConfiguration::forQuery($query);
```

## SimpleTable with Static Configuration

Basic table with fixed configuration:

```php
<?php

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

// Create a fully configured table
$configuration = TableConfiguration::forModel(\App\Models\User::class)
    ->searchConfig()
        ->enable()
        ->searchableColumns(['name', 'email'])
        ->placeholder('Search users by name or email...')
        ->noResultsText('No users found matching your search')
    ->paginationConfig()
        ->defaultPerPage(10)
        ->perPageOptions([5, 10, 25, 50])
        ->showingText('Showing {from} to {to} of {total} users')
    ->columnConfig()
        ->columns([
            'name' => 'User Name',
            'email' => 'Email Address',
            'created_at' => 'Joined Date'
        ])
    ->uiConfig()
        ->tableTitle('User Directory')
        ->emptyStateTitle('No Users Found')
        ->emptyStateDescription('There are no users in the system yet.')
    ->localizationConfig()
        ->locale('en')
        ->customTranslations([
            'flux-tables::search.placeholder' => 'Find a user...',
            'flux-tables::pagination.showing' => 'Displaying'
        ])
    ->initializeDefaults();
```

```blade
{{-- Usage with configuration object --}}
<livewire:simple-table :configuration="$configuration" />

{{-- Or with model class for simple cases --}}
<livewire:simple-table model-class="\App\Models\User::class" />
```

## Dynamic Configuration with Closures

The new system supports dynamic configuration values using closures:

```php
<?php

$config = TableConfiguration::forModel(\App\Models\Order::class)
    ->title(fn($user) => $user->isAdmin() ? 'All Orders' : 'My Orders')
    ->columns(fn($user) => $user->hasRole('manager') ? [
        'id' => 'Order ID',
        'customer_name' => 'Customer',
        'total' => 'Total',
        'profit_margin' => 'Profit Margin', // Only for managers
        'created_at' => 'Date'
    ] : [
        'id' => 'Order ID',
        'customer_name' => 'Customer',
        'total' => 'Total',
        'created_at' => 'Date'
    ])
    ->paginationConfig()
        ->defaultPerPage(fn($user) => $user->isAdmin() ? 50 : 25)
        ->perPageOptions(fn($user) => $user->isAdmin() ? [25, 50, 100, 200] : [10, 25, 50])
    ->searchableColumns(fn($user, $table) =>
        $user->can('search-sensitive-data')
            ? ['order_number', 'customer_name', 'customer_email']
            : ['order_number', 'customer_name']
    )
    ->initializeDefaults();
```

## Scope Management for Multi-Tenant Apps

Handle problematic scopes (like tenant scopes) properly:

```php
<?php

// Option 1: Use pre-scoped query (recommended)
$tenantUsers = \App\Models\User::where('tenant_id', auth()->user()->tenant_id);
$config = TableConfiguration::forQuery($tenantUsers)
    ->withoutScopes(['tenant']) // Disable tenant scope since we pre-applied it
    ->initializeDefaults();

// Option 2: Disable specific global scopes
$config = TableConfiguration::forModel(\App\Models\User::class)
    ->withoutScopes(['tenant', 'softdeletes']) // Disable problematic scopes
    ->initializeDefaults();

// Option 3: Disable all global scopes and manually apply what you need
$config = TableConfiguration::forModel(\App\Models\User::class)
    ->withoutGlobalScopes(true)
    ->initializeDefaults();
```

## Real-World Usage Patterns

### Multi-Tenant SaaS Example

```php
<?php

class OrderTableController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pre-apply tenant scope to avoid issues
        $tenantOrders = \App\Models\Order::where('tenant_id', $user->tenant_id);

        $config = TableConfiguration::forQuery($tenantOrders)
            ->title(fn($user) => "Orders for {$user->company->name}")
            ->columns(fn($user) => array_filter([
                'order_number' => 'Order #',
                'customer_name' => 'Customer',
                'total' => 'Total',
                'commission' => $user->hasRole('sales') ? 'Commission' : null,
                'profit_margin' => $user->can('view-margins') ? 'Profit' : null,
                'created_at' => 'Date'
            ]))
            ->searchableColumns(['order_number', 'customer_name'])
            ->paginationConfig()
                ->defaultPerPage(fn($user) => $user->preference('table_rows', 25))
                ->perPageOptions(fn($user) =>
                    $user->hasRole('admin')
                        ? [10, 25, 50, 100, 200]
                        : [10, 25, 50]
                )
            ->withoutScopes(['tenant']) // Already applied to query
            ->initializeDefaults();

        return view('orders.index', compact('config'));
    }
}
```

### E-commerce Admin with Dynamic Permissions

```php
<?php

$config = TableConfiguration::forModel(\App\Models\Product::class)
    ->title(fn($user) => $user->can('manage-all-products') ? 'All Products' : 'My Products')
    ->query(fn($user) =>
        $user->can('manage-all-products')
            ? null // Use base model query
            : \App\Models\Product::where('created_by', $user->id)
    )
    ->columns(fn($user) => [
        'image' => 'Image',
        'name' => 'Product Name',
        'sku' => 'SKU',
        'price' => 'Price',
        'stock' => 'Stock',
        'category.name' => 'Category',
        'profit_margin' => $user->can('view-costs') ? 'Profit %' : null,
        'created_at' => 'Added'
    ])
    ->searchableColumns(fn($user) =>
        $user->can('search-all-fields')
            ? ['name', 'sku', 'description', 'category.name']
            : ['name', 'sku']
    )
    ->initializeDefaults();
```

### Blog with User-Specific Content

```php
<?php

$config = TableConfiguration::forModel(\App\Models\Post::class)
    ->title(fn($user) => $user->isAuthor() ? 'My Articles' : 'All Articles')
    ->searchConfig()
        ->enable()
        ->searchableColumns(['title', 'content'])
        ->placeholder(fn($user) => $user->isAdmin() ? 'Search all posts...' : 'Search your posts...')
    ->sortingConfig()
        ->enable()
        ->defaultSort('created_at', 'desc')
    ->columnConfig()
        ->columns(fn($user) => array_filter([
            'title' => 'Title',
            'author.name' => $user->isAdmin() ? 'Author' : null,
            'status' => 'Status',
            'views' => $user->can('view-analytics') ? 'Views' : null,
            'created_at' => 'Published',
        ]))
    ->paginationConfig()
        ->defaultPerPage(fn($user) => $user->preference('posts_per_page', 20))
    ->initializeDefaults();
```

## EnhancedTable with Full Features

Complete table with filtering, actions, and all features:

```php
<?php

$enhancedConfig = TableConfiguration::make()
    ->modelClass(\App\Models\User::class)
    ->searchConfig()
        ->enable()
        ->searchableColumns(['name', 'email'])
    ->sortingConfig()
        ->enable()
        ->defaultSort('created_at', 'desc')
    ->filterConfig()
        ->enable()
        ->persistFilters()
        ->modalPosition('right')
        ->showActiveCount()
        ->filters([
            \Idkwhoami\FluxTables\Filters\BooleanFilter::make('active')
                ->label('Account Status')
                ->property('is_active'),
            \Idkwhoami\FluxTables\Filters\DateRangeFilter::make('joined')
                ->label('Join Date')
                ->property('created_at'),
            \Idkwhoami\FluxTables\Filters\SelectFilter::make('role')
                ->label('User Role')
                ->property('role')
                ->options([
                    'admin' => 'Administrator',
                    'user' => 'Regular User',
                    'moderator' => 'Moderator'
                ])
        ])
    ->actionConfig()
        ->enable()
        ->confirmDestructive()
        ->bulkActions([
            'activate' => 'Activate Selected',
            'deactivate' => 'Deactivate Selected',
            'export' => 'Export Selected'
        ])
    ->columnConfig()
        ->enableToggling()
        ->columns([
            'name' => 'Full Name',
            'email' => 'Email Address',
            'role' => 'Role',
            'is_active' => 'Active',
            'created_at' => 'Joined',
            'last_login_at' => 'Last Login'
        ])
    ->paginationConfig()
        ->defaultPerPage(25)
        ->perPageOptions([10, 25, 50, 100])
    ->uiConfig()
        ->tableTitle('User Management')
        ->showCreateButton()
        ->createButtonText('Add New User')
        ->showBulkActions()
        ->showColumnToggle()
        ->showFilterButton()
    ->localizationConfig()
        ->locale('en')
        ->fallbackLocale('en')
        ->customTranslations([
            'flux-tables::actions.edit' => 'Modify',
            'flux-tables::actions.delete' => 'Remove',
            'flux-tables::filters.title' => 'Filter Users'
        ])
    ->initializeDefaults();
```

```blade
<livewire:enhanced-table
    model-class="\App\Models\User::class"
    :configuration="$enhancedConfig"
/>
```

## Localization Examples

### Multiple Language Support

```php
<?php

// Spanish configuration
$spanishConfig = TableConfiguration::make()
    ->modelClass(\App\Models\Product::class)
    ->localizationConfig()
        ->locale('es')
        ->fallbackLocale('en')
        ->customTranslations([
            'flux-tables::search.placeholder' => 'Buscar productos...',
            'flux-tables::pagination.showing' => 'Mostrando',
            'flux-tables::actions.edit' => 'Editar',
            'flux-tables::actions.delete' => 'Eliminar'
        ])
    ->uiConfig()
        ->tableTitle('Gestión de Productos')
    ->initializeDefaults();

// French configuration
$frenchConfig = TableConfiguration::make()
    ->modelClass(\App\Models\Product::class)
    ->localizationConfig()
        ->locale('fr')
        ->fallbackLocale('en')
        ->customTranslations([
            'flux-tables::search.placeholder' => 'Rechercher des produits...',
            'flux-tables::pagination.showing' => 'Affichage',
            'flux-tables::actions.edit' => 'Modifier',
            'flux-tables::actions.delete' => 'Supprimer'
        ])
    ->uiConfig()
        ->tableTitle('Gestion des Produits')
    ->initializeDefaults();
```

### Dynamic Language Switching

```php
<?php

class ProductTableController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->get('lang', app()->getLocale());

        $config = TableConfiguration::make()
            ->modelClass(\App\Models\Product::class)
            ->localizationConfig()
                ->locale($locale)
                ->fallbackLocale('en')
            ->searchConfig()
                ->enable()
                ->searchableColumns(['name', 'description'])
            ->initializeDefaults();

        return view('products.index', compact('config'));
    }
}
```

## Advanced Configuration Patterns

### Configuration Factories

```php
<?php

class TableConfigurationFactory
{
    public static function userManagement(): TableConfiguration
    {
        return TableConfiguration::make()
            ->modelClass(\App\Models\User::class)
            ->searchConfig()
                ->enable()
                ->searchableColumns(['name', 'email'])
            ->sortingConfig()
                ->enable()
                ->defaultSort('created_at', 'desc')
            ->filterConfig()
                ->enable()
                ->persistFilters()
            ->actionConfig()
                ->enable()
                ->confirmDestructive()
            ->columnConfig()
                ->enableToggling()
            ->initializeDefaults();
    }

    public static function blogPosts(): TableConfiguration
    {
        return TableConfiguration::make()
            ->modelClass(\App\Models\Post::class)
            ->searchConfig()
                ->enable()
                ->searchableColumns(['title', 'content'])
            ->sortingConfig()
                ->enable()
                ->defaultSort('published_at', 'desc')
            ->columnConfig()
                ->columns([
                    'title' => 'Post Title',
                    'author.name' => 'Author',
                    'published_at' => 'Published',
                    'status' => 'Status'
                ])
            ->initializeDefaults();
    }
}
```

### Usage in Controllers

```php
<?php

class UserController extends Controller
{
    public function index()
    {
        $config = TableConfigurationFactory::userManagement()
            ->localizationConfig()
                ->locale(auth()->user()->preferred_locale ?? 'en')
            ->uiConfig()
                ->tableTitle(__('users.management_title'));

        return view('users.index', compact('config'));
    }
}
```

## Benefits of the Configuration System

1. **Full Customization**: Every aspect of the table is configurable
2. **Localization Ready**: Complete translation support for all text
3. **Fluent Interface**: Chainable methods for clean configuration
4. **Type Safety**: Proper type hints and validation
5. **Modular**: Only configure the features you need
6. **Reusable**: Create factory methods for common configurations
7. **Flexible**: Override defaults on a per-instance basis
8. **Maintainable**: Centralized configuration management