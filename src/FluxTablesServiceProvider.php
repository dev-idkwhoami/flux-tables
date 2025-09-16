<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Console\Commands\InstallCommand;
use Idkwhoami\FluxTables\Data\TableDataRepository;
use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Idkwhoami\FluxTables\Services\TableStateService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxTablesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-tables');


        $this->publishes([
            __DIR__.'/../resources/views/flux' => resource_path('views/flux'),
        ], 'flux-tables-flux-views');

        // New three-tier table system
        Livewire::component('flux-simple-table', SimpleTable::class);
        Livewire::component('flux-advanced-table', \Idkwhoami\FluxTables\Livewire\AdvancedTable::class);
        Livewire::component('flux-enhanced-table', \Idkwhoami\FluxTables\Livewire\EnhancedTable::class);
    }

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
    }

    /**
     * @return void
     */
    private function prepareCommands(): void
    {
        $this->commands([
            InstallCommand::class
        ]);
    }

    /**
     * @return void
     */
    private function prepareConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/flux-tables.php' => config_path('flux-tables.php'),
        ], [
            'flux-tables-config',
            'flux-tables'
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/flux-tables.php',
            'flux-tables'
        );
    }

    /**
     * @return void
     */
    public function prepareLocalization(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'flux-tables');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/flux-tables'),
        ], [
            'flux-tables-lang',
            'flux-tables'
        ]);
    }

}
