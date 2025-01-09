<?php

namespace Idkwhoami\FluxTables\Commands;

use Illuminate\Console\Command;

class FluxTablesCommand extends Command
{
    public $signature = 'flux-tables';

    public $description = 'Prepare your app to use FluxTables';

    public function handle(): int
    {
        $this->call('flux:icon', ['columns-3', 'filter-x', 'filter', 'search', 'circle-x', 'circle-check', 'view']);

        $this->info('All done, simply add this to your tailwind content section and you are ready to go');
        $this->comment('./vendor/livewire/flux-tables/resources/views/**/*.blade.php');

        return self::SUCCESS;
    }
}
