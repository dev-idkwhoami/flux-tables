<?php

namespace Idkwhoami\FluxTables\Tests;

use Idkwhoami\FluxTables\FluxTablesServiceProvider;
use Illuminate\Foundation\Application;

class PackageTestCase extends \Orchestra\Testbench\TestCase
{
    protected function resolveApplication(): Application
    {
        return parent::resolveApplication()
            ->useEnvironmentPath(realpath(__DIR__.'/../'))
            ->loadEnvironmentFrom('.env.testing');
    }

    protected function getPackageProviders($app): array
    {
        return [
            FluxTablesServiceProvider::class,
        ];
    }

}
