<?php

namespace Lunar\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Addons\Manifest;

class AddonsDiscover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:addons:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached addon package manifest';

    /**
     * Execute the console command.
     */
    public function handle(Manifest $manifest)
    {
        $manifest->build();

        foreach (array_keys($manifest->manifest) as $package) {
            $this->components->line("Discovered Addon: <info>{$package}</info>");
        }

        $this->components->info('Addon manifest generated successfully.');

        return Command::SUCCESS;
    }
}
