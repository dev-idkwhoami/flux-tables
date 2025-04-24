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
        $this->call('flux:icon', [
            'icons' => [
                'arrow-up-narrow-wide', 'arrow-down-wide-narrow', 'arrow-up-down', 'funnel', 'funnel-x', 'chevron-down',
                'search', 'ellipsis', 'circle-x', 'circle-check'
            ]
        ]);

        $this->info('⚠️ Please add the following into your app.css:');
        $this->comment('⚠️ @import "../../vendor/idkwhoami/flux-tables/dist/flux-tables.css";');
        $this->info('⚠️ This is required so vite can compile the packages classes.');
    }
}
