<?php

namespace Idkwhoami\FluxTables\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'flux-tables:install';

    protected $description = 'Installs Flux Tables and prepares the project for using it.';

    public function handle(): void
    {
        $this->call('vendor:publish', ['--tag' => 'flux-tables-flux-views']);
        $this->call('flux:icon', ['icons' => ['arrow-up-narrow-wide', 'arrow-down-wide-narrow', 'arrow-up-down', 'filter', 'filter-x', 'chevron-down', 'search']]);
    }
}
