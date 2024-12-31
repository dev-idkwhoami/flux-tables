<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Livewire\ActionComponent;
use Idkwhoami\FluxTables\Livewire\FilterComponent;
use Idkwhoami\FluxTables\Livewire\TableComponent;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxTablesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FluxTables::class, fn () => new FluxTables);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-tables');

        Livewire::component('flux-table', TableComponent::class);
        Livewire::component('flux-filter', FilterComponent::class);
        Livewire::component('flux-action', ActionComponent::class);
    }
}
