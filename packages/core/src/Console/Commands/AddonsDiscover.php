<?php

namespace GetCandy\Console\Commands;

use GetCandy\Addons\Manifest;
use Illuminate\Console\Command;

class AddonsDiscover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getcandy:addons:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached addon package manifest';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Manifest $manifest)
    {
        $manifest->build();

        foreach (array_keys($manifest->manifest) as $package) {
            $this->line("Discovered Addon: <info>{$package}</info>");
        }

        $this->info('Addon manifest generated successfully.');
    }
}
