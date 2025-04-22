<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Console\Commands\InstallCommand;
use Idkwhoami\FluxTables\Livewire\Filters\ValuePresent;
use Idkwhoami\FluxTables\Livewire\SimpleTable;
use Idkwhoami\FluxTables\Livewire\Filters\DateRange;
use Idkwhoami\FluxTables\Livewire\Filters\Deleted;
use Idkwhoami\FluxTables\Livewire\Filters\Select;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxTablesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-tables');

        $this->publishes([
            __DIR__.'/../resources/views/flux' => resource_path('views/flux'),
        ], 'flux-tables-flux-views');

        Livewire::component('flux-simple-table', SimpleTable::class);
        Livewire::component('flux-filter-deleted', Deleted::class);
        Livewire::component('flux-filter-date-range', DateRange::class);
        Livewire::component('flux-filter-select', Select::class);
        Livewire::component('flux-filter-value-present', ValuePresent::class);
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
