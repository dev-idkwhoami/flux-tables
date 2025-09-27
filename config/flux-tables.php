<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table State Persistence
    |--------------------------------------------------------------------------
    |
    | Configure default settings for table state persistence across your
    | application. These can be overridden per table configuration.
    |
    */

    'persistence' => [

        /*
        |--------------------------------------------------------------------------
        | Default Persistence Settings
        |--------------------------------------------------------------------------
        |
        | Configure default persistence behavior for different table features.
        |
        */
        'defaults' => [
            'column_toggles' => 'session',  // Column visibility preferences
            'filters' => 'none',           // Filter values
            'sorting' => 'none',           // Sort preferences
            'search' => 'none',            // Search queries
            'pagination' => 'session',     // Items per page preference
        ],

        /*
        |--------------------------------------------------------------------------
        | Database Settings
        |--------------------------------------------------------------------------
        |
        | Settings for database-based persistence.
        |
        */
        'database' => [
            'table' => env('FLUX_TABLES_PERSISTENCE_TABLE', 'table_state_preferences'),
            'auto_cleanup' => env('FLUX_TABLES_AUTO_CLEANUP', true),
            'cleanup_probability' => env('FLUX_TABLES_CLEANUP_PROBABILITY', 1), // 1%
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Column Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for columns when not explicitly configured.
    |
    */

    'columns' => [
        'default_toggleable' => false,
        'default_visible' => true,
        'default_toggled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for table configurations and data.
    |
    */

    'cache' => [
        'enabled' => env('FLUX_TABLES_CACHE_ENABLED', true),
        'ttl' => env('FLUX_TABLES_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('FLUX_TABLES_CACHE_PREFIX', 'flux_tables'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings to optimize performance for large datasets.
    |
    */

    'performance' => [
        'max_pagination_limit' => env('FLUX_TABLES_MAX_PAGINATION', 1000),
        'default_per_page' => env('FLUX_TABLES_DEFAULT_PER_PAGE', 15),
        'enable_query_optimization' => env('FLUX_TABLES_QUERY_OPTIMIZATION', true),
    ],
];
