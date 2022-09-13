<?php

namespace Lunar\Hub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallHub extends Command
{
    protected $commands = [
        'vendor:publish --tag=getcandy:hub:public --force',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getcandy:hub:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install hub dependancies';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Publishing assets to public folder.');

        // Publish hub public assets
        foreach ($this->commands as $command) {
            Artisan::call($command);
        }

        $this->line('Done.');
    }
}
