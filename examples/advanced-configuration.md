# Advanced Configuration Examples

This document showcases advanced configuration patterns and real-world usage scenarios for the Flux Tables package.

## Custom Column Configurations

### Advanced Column Types with Configuration

```php
<?php

use Idkwhoami\FluxTables\Configuration\TableConfiguration;

$config = TableConfiguration::make()
    ->modelClass(\App\Models\Order::class)
    ->columnConfig()
        ->columns([
            // Text column with transformation
            'order_number' => [
                'label' => 'Order #',
                'sortable' => true,
                'searchable' => true,
                'transform' => fn($value) => strtoupper($value)
            ],

            // Currency column with formatting
            'total' => [
                'label' => 'Total Amount',
                'sortable' => true,
                'format' => 'currency',
                'currency' => 'USD'
            ],

            // Status column with badge styling
            'status' => [
                'label' => 'Status',
                'sortable' => true,
                'type' => 'badge',
                'variants' => [
                    'pending' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger'
                ]
            ],

            // Relationship column with eager loading
            'customer.name' => [
                'label' => 'Customer',
                'sortable' => true,
                'searchable' => true,
                'with' => ['customer']
            ],

            // Date column with human-readable format
            'created_at' => [
                'label' => 'Order Date',
                'sortable' => true,
                'type' => 'datetime',
                'format' => 'human',
                'timezone' => 'user'
            ]
        ])
        ->defaultVisibleColumns(['order_number', 'customer.name', 'total', 'status', 'created_at'])
        ->enableToggling()
    ->initializeDefaults();
```

## Complex Filter Configurations

### Multi-Level Filters with Dependencies

```php
<?php

$config = TableConfiguration::make()
    ->modelClass(\App\Models\Product::class)
    ->filterConfig()
        ->enable()
        ->persistFilters(true)
        ->modalPosition('right')
        ->modalDismissible(false)
        ->showActiveCount(true)
        ->showClearAll(true)
        ->filters([
            // Category filter affects subcategory options
            'category' => [
                'type' => 'select',
                'label' => 'Category',
                'property' => 'category_id',
                'options' => fn() => \App\Models\Category::pluck('name', 'id'),
                'affects' => ['subcategory']
            ],

            // Subcategory depends on category selection
            'subcategory' => [
                'type' => 'select',
                'label' => 'Subcategory',
                'property' => 'subcategory_id',
                'options' => fn($categoryId) => \App\Models\Subcategory::where('category_id', $categoryId)->pluck('name', 'id'),
                'depends_on' => 'category'
            ],

            // Price range with currency formatting
            'price_range' => [
                'type' => 'number_range',
                'label' => 'Price Range',
                'property' => 'price',
                'min' => 0,
                'max' => 10000,
                'step' => 0.01,
                'currency' => 'USD'
            ],

            // Stock status with custom logic
            'stock_status' => [
                'type' => 'select',
                'label' => 'Stock Status',
                'options' => [
                    'in_stock' => 'In Stock',
                    'low_stock' => 'Low Stock',
                    'out_of_stock' => 'Out of Stock'
                ],
                'apply' => function($query, $value) {
                    switch ($value) {
                        case 'in_stock':
                            return $query->where('stock_quantity', '>', 10);
                        case 'low_stock':
                            return $query->whereBetween('stock_quantity', [1, 10]);
                        case 'out_of_stock':
                            return $query->where('stock_quantity', 0);
                    }
                }
            ],

            // Featured products toggle
            'featured' => [
                'type' => 'boolean',
                'label' => 'Featured Products',
                'property' => 'is_featured',
                'default' => null,
                'options' => [
                    null => 'All Products',
                    true => 'Featured Only',
                    false => 'Regular Only'
                ]
            ]
        ])
        ->defaultValues([
            'stock_status' => 'in_stock',
            'featured' => null
        ])
    ->initializeDefaults();
```

## Advanced Action Configurations

### Bulk Actions with Permissions

```php
<?php

$config = TableConfiguration::make()
    ->modelClass(\App\Models\User::class)
    ->actionConfig()
        ->enable()
        ->showBulkActions(true)
        ->confirmDestructive(true)
        ->confirmationModalVariant('danger')
        ->bulkActions([
            'activate' => [
                'label' => 'Activate Users',
                'icon' => 'user-check',
                'variant' => 'success',
                'permission' => 'users.activate',
                'confirmation' => 'Are you sure you want to activate the selected users?',
                'action' => function($userIds) {
                    \App\Models\User::whereIn('id', $userIds)->update(['is_active' => true]);
                    return 'Users activated successfully';
                }
            ],

            'deactivate' => [
                'label' => 'Deactivate Users',
                'icon' => 'user-x',
                'variant' => 'warning',
                'permission' => 'users.deactivate',
                'confirmation' => 'Deactivated users will not be able to log in. Continue?',
                'action' => function($userIds) {
                    \App\Models\User::whereIn('id', $userIds)->update(['is_active' => false]);
                    return 'Users deactivated successfully';
                }
            ],

            'export' => [
                'label' => 'Export to CSV',
                'icon' => 'download',
                'variant' => 'outline',
                'permission' => 'users.export',
                'action' => function($userIds) {
                    return response()->download(
                        \App\Services\UserExportService::export($userIds),
                        'users-' . now()->format('Y-m-d') . '.csv'
                    );
                }
            ],

            'delete' => [
                'label' => 'Delete Users',
                'icon' => 'trash-2',
                'variant' => 'danger',
                'permission' => 'users.delete',
                'confirmation' => 'This action cannot be undone. Are you sure?',
                'action' => function($userIds) {
                    \App\Models\User::whereIn('id', $userIds)->delete();
                    return 'Users deleted successfully';
                }
            ]
        ])
        ->texts([
            'bulk_actions' => 'Batch Operations',
            'select_all' => 'Select All Users',
            'selected_items' => '{count} users selected',
            'no_items_selected' => 'Please select users first',
            'action_completed' => 'Operation completed successfully',
            'action_failed' => 'Operation failed: {error}'
        ])
    ->initializeDefaults();
```

## Pagination with Custom Logic

### Advanced Pagination Configuration

```php
<?php

$config = TableConfiguration::make()
    ->modelClass(\App\Models\Article::class)
    ->paginationConfig()
        ->defaultPerPage(20)
        ->perPageOptions([10, 20, 50, 100, 200])
        ->showingText('Displaying {from}-{to} of {total} articles')
        ->previousPageText('‹ Previous')
        ->nextPageText('Next ›')
        ->firstPageText('« First')
        ->lastPageText('Last »')
        ->enableFirstLast(true)
        ->enablePreviousNext(true)
        ->enableJumpToPage(true)
        ->maxVisiblePages(7)
        ->customPaginationView('custom.pagination')
        ->customPerPageView('custom.per-page-selector')
    ->initializeDefaults();
```

## Localization with Context

### Dynamic Localization Based on User Settings

```php
<?php

class LocalizedTableConfiguration
{
    public static function create(string $modelClass, ?User $user = null): TableConfiguration
    {
        $user = $user ?? auth()->user();
        $locale = $user?->preferred_locale ?? app()->getLocale();
        $timezone = $user?->timezone ?? config('app.timezone');

        return TableConfiguration::make()
            ->modelClass($modelClass)
            ->localizationConfig()
                ->locale($locale)
                ->fallbackLocale('en')
                ->customTranslations([
                    // User-specific customizations
                    'flux-tables::pagination.showing' => $locale === 'es'
                        ? 'Mostrando {from} a {to} de {total} elementos'
                        : 'Showing {from} to {to} of {total} items',

                    'flux-tables::search.placeholder' => match($locale) {
                        'es' => 'Buscar...',
                        'fr' => 'Rechercher...',
                        'de' => 'Suchen...',
                        default => 'Search...'
                    },

                    'flux-tables::filters.title' => match($locale) {
                        'es' => 'Filtros',
                        'fr' => 'Filtres',
                        'de' => 'Filter',
                        default => 'Filters'
                    }
                ])
            ->uiConfig()
                ->timezone($timezone)
                ->dateFormat($user?->date_format ?? 'Y-m-d')
                ->timeFormat($user?->time_format ?? 'H:i:s')
            ->initializeDefaults();
    }
}

// Usage
$config = LocalizedTableConfiguration::create(\App\Models\Order::class)
    ->searchConfig()
        ->enable()
        ->searchableColumns(['order_number', 'customer.name'])
    ->sortingConfig()
        ->enable()
        ->defaultSort('created_at', 'desc');
```

## Performance Optimization Configuration

### Large Dataset Optimization

```php
<?php

$config = TableConfiguration::make()
    ->modelClass(\App\Models\Transaction::class)
    ->performanceConfig()
        ->enableQueryOptimization(true)
        ->enableSelectOptimization(true)
        ->enableEagerLoading(['user', 'account', 'category'])
        ->enableIndexHints(['created_at', 'user_id', 'amount'])
        ->enableQueryCaching(true, 300) // 5 minutes
        ->enableResultCaching(true, 60) // 1 minute
        ->maxPerPage(100)
        ->chunkSize(1000)
    ->searchConfig()
        ->enable()
        ->searchableColumns(['reference_number', 'description'])
        ->enableFullTextSearch(true)
        ->minimumSearchLength(3)
        ->searchDelay(500) // milliseconds
    ->columnConfig()
        ->columns([
            'reference_number' => 'Reference',
            'user.name' => 'User',
            'account.name' => 'Account',
            'amount' => 'Amount',
            'category.name' => 'Category',
            'created_at' => 'Date'
        ])
        ->virtualColumns([
            'formatted_amount' => [
                'select' => "CONCAT('$', FORMAT(amount, 2))",
                'label' => 'Formatted Amount'
            ]
        ])
    ->initializeDefaults();
```

## UI Customization Configuration

### Custom Styling and Layout

```php
<?php

$config = TableConfiguration::make()
    ->modelClass(\App\Models\Project::class)
    ->uiConfig()
        ->tableTitle('Project Portfolio')
        ->tableDescription('Manage your projects and track progress')
        ->tableIcon('folder')
        ->theme('dark')
        ->customCssClasses([
            'table' => 'custom-project-table',
            'header' => 'bg-gradient-to-r from-blue-500 to-purple-600',
            'row' => 'hover:bg-blue-50 transition-colors',
            'cell' => 'px-6 py-4'
        ])
        ->customStyles([
            '.custom-project-table' => [
                'border-radius' => '12px',
                'box-shadow' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                'overflow' => 'hidden'
            ]
        ])
        ->showHeader(true)
        ->showFooter(true)
        ->showBorder(true)
        ->showStriped(true)
        ->showHover(true)
        ->showCreateButton(true)
        ->createButtonText('New Project')
        ->createButtonIcon('plus')
        ->createButtonVariant('primary')
        ->showSearchBox(true)
        ->searchBoxPosition('top-right')
        ->showFilterButton(true)
        ->showColumnToggle(true)
        ->showPaginationInfo(true)
        ->showPerPageSelector(true)
        ->showRefreshButton(true)
        ->showExportButton(true)
        ->emptyStateTitle('No Projects Found')
        ->emptyStateDescription('Start by creating your first project')
        ->emptyStateIcon('folder-plus')
        ->emptyStateAction([
            'text' => 'Create Project',
            'url' => route('projects.create'),
            'variant' => 'primary'
        ])
    ->initializeDefaults();
```

## Real-World Usage Patterns

### E-commerce Admin Panel

```php
<?php

class EcommerceTableFactory
{
    public static function products(): TableConfiguration
    {
        return TableConfiguration::make()
            ->modelClass(\App\Models\Product::class)
            ->searchConfig()
                ->enable()
                ->searchableColumns(['name', 'sku', 'description'])
                ->placeholder('Search products by name, SKU, or description...')
            ->filterConfig()
                ->enable()
                ->persistFilters()
                ->filters([
                    'category' => [
                        'type' => 'select',
                        'label' => 'Category',
                        'property' => 'category_id',
                        'options' => fn() => \App\Models\Category::pluck('name', 'id')
                    ],
                    'status' => [
                        'type' => 'select',
                        'label' => 'Status',
                        'property' => 'status',
                        'options' => [
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            'draft' => 'Draft'
                        ]
                    ],
                    'price_range' => [
                        'type' => 'number_range',
                        'label' => 'Price Range',
                        'property' => 'price',
                        'currency' => 'USD'
                    ]
                ])
            ->actionConfig()
                ->enable()
                ->bulkActions([
                    'activate' => 'Activate Products',
                    'deactivate' => 'Deactivate Products',
                    'export' => 'Export to CSV'
                ])
            ->columnConfig()
                ->columns([
                    'image' => 'Image',
                    'name' => 'Product Name',
                    'sku' => 'SKU',
                    'category.name' => 'Category',
                    'price' => 'Price',
                    'stock_quantity' => 'Stock',
                    'status' => 'Status',
                    'created_at' => 'Added'
                ])
            ->initializeDefaults();
    }

    public static function orders(): TableConfiguration
    {
        return TableConfiguration::make()
            ->modelClass(\App\Models\Order::class)
            ->searchConfig()
                ->enable()
                ->searchableColumns(['order_number', 'customer.name', 'customer.email'])
            ->filterConfig()
                ->enable()
                ->filters([
                    'status' => [
                        'type' => 'select',
                        'label' => 'Order Status',
                        'property' => 'status',
                        'options' => [
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled'
                        ]
                    ],
                    'date_range' => [
                        'type' => 'date_range',
                        'label' => 'Order Date',
                        'property' => 'created_at'
                    ]
                ])
            ->sortingConfig()
                ->enable()
                ->defaultSort('created_at', 'desc')
            ->initializeDefaults();
    }
}
```

### Multi-tenant SaaS Configuration

```php
<?php

class TenantAwareTableConfiguration
{
    public static function forTenant(string $modelClass, Tenant $tenant): TableConfiguration
    {
        return TableConfiguration::make()
            ->modelClass($modelClass)
            ->localizationConfig()
                ->locale($tenant->default_locale)
                ->fallbackLocale('en')
                ->customTranslations($tenant->custom_translations ?? [])
            ->uiConfig()
                ->theme($tenant->ui_theme)
                ->customCssClasses($tenant->custom_css_classes ?? [])
                ->tableTitle($tenant->getTableTitle($modelClass))
            ->performanceConfig()
                ->maxPerPage($tenant->max_table_rows)
                ->enableQueryCaching($tenant->enable_caching)
            ->scopeConfig()
                ->tenantScope(['tenant_id' => $tenant->id])
                ->softDeleteScope($tenant->enable_soft_deletes)
            ->initializeDefaults();
    }
}
```

This comprehensive configuration system provides:

1. **Complete Customization**: Every aspect is configurable
2. **Performance Optimization**: Built-in performance enhancements
3. **Localization Support**: Full translation capabilities
4. **Multi-tenant Ready**: Tenant-aware configurations
5. **Real-world Patterns**: E-commerce and SaaS examples
6. **Type Safety**: Proper validation and type hints
7. **Extensibility**: Easy to extend for custom needs