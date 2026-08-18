<?php

namespace Lunar\Panel\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Panel\PanelManager;

use function Laravel\Prompts\info;

class InstallPanelCommand extends Command
{
    protected $signature = 'lunar:panel:install';

    protected $description = 'Publish the Lunar panel config and compiled assets';

    public function handle(PanelManager $manager): int
    {
        info('Installing the Lunar panel...');

        $this->call('vendor:publish', [
            '--tag' => 'panel-config',
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'panel-all-assets',
            '--force' => true,
        ]);

        info('Lunar panel installed. Visit it at: '.url($manager->path()));

        return self::SUCCESS;
    }
}
