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

        Livewire::component('flux-table', FluxTable::class);
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
            __DIR__.'/../lang' => $this->app->langPath('vendor/flux-tables'),
        ], [
            'flux-tables-lang',
            'flux-tables'
        ]);
    }

}
