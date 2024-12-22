<?php

namespace Idkwhoami\FluxTables;

use Idkwhoami\FluxTables\Commands\FluxTablesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FluxTablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('flux-tables')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(FluxTablesCommand::class);
    }
}
