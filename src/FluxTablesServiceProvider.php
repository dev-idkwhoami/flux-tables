<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Livewire\ExampleTable;
use Idkwhoami\FluxTables\Livewire\Filters\DateRange;
use Idkwhoami\FluxTables\Livewire\Filters\Deleted;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxTablesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-tables');

        Livewire::component('flux-example-table', ExampleTable::class);
        Livewire::component('flux-filter-deleted', Deleted::class);
        Livewire::component('flux-filter-date-range', DateRange::class);
    }

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
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'flux-tables');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/flux-tables'),
        ], [
            'flux-tables-lang',
            'flux-tables'
        ]);
    }

}
