<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Console\Commands\InstallCommand;
use Idkwhoami\FluxTables\Data\TableDataRepository;
use Idkwhoami\FluxTables\Livewire\FluxTable;
use Idkwhoami\FluxTables\Services\TableStateService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxTablesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerServices();
        $this->registerConfig();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootViews();
        $this->bootLivewireComponents();
        $this->bootPublishing();
        $this->bootCommands();
    }

    /**
     * Register application services.
     */
    private function registerServices(): void
    {
        $this->app->bind(TableStateService::class, function ($app, $parameters) {
            return new TableStateService(
                $parameters['tableId'],
                $app['events']
            );
        });

        $this->app->bind(TableDataRepository::class, function ($app, $parameters) {
            return new TableDataRepository($parameters['modelClass']);
        });

        // Register facade binding for TableStateService
        $this->app->singleton('flux-tables.state-service', function ($app) {
            return new class($app) {
                private $app;

                public function __construct($app)
                {
                    $this->app = $app;
                }

                public function forTable(string $tableId): TableStateService
                {
                    return $this->app->makeWith(TableStateService::class, [
                        'tableId' => $tableId
                    ]);
                }

                public function make(string $tableId): TableStateService
                {
                    return $this->forTable($tableId);
                }

                public function __call($method, $arguments)
                {
                    if (empty($arguments)) {
                        throw new \InvalidArgumentException('Table ID is required as first argument');
                    }

                    $tableId = array_shift($arguments);
                    $service = $this->forTable($tableId);

                    return $service->$method(...$arguments);
                }
            };
        });
    }

    /**
     * Register configuration.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/flux-tables.php',
            'flux-tables'
        );
    }

    /**
     * Bootstrap views.
     */
    private function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-tables');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'flux-tables');
    }

    /**
     * Bootstrap Livewire components.
     */
    private function bootLivewireComponents(): void
    {
        Livewire::component('flux-table', FluxTable::class);
    }

    /**
     * Bootstrap publishing.
     */
    private function bootPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/flux-tables.php' => config_path('flux-tables.php'),
        ], [
            'flux-tables-config',
            'flux-tables'
        ]);

        // Publish migrations with automatic timestamps
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'flux-tables-migrations');

        // Publish language files
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/flux-tables'),
        ], [
            'flux-tables-lang',
            'flux-tables'
        ]);
    }

    /**
     * Bootstrap Artisan commands.
     */
    private function bootCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class
        ]);
    }
}
