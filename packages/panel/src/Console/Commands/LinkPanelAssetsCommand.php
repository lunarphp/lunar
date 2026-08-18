<?php

namespace Lunar\Panel\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Panel\PanelManager;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class LinkPanelAssetsCommand extends Command
{
    protected $signature = 'lunar:panel:link';

    protected $description = 'Create symbolic links for registered Lunar panel module assets';

    public function handle(PanelManager $manager): int
    {
        $buildPaths = $manager->viteBuildPaths();

        if (empty($buildPaths)) {
            warning('No panel module build paths are registered. Pass __buildSourcePath to PanelManager::vite() to register one.');

            return self::SUCCESS;
        }

        foreach ($buildPaths as $key => $buildPath) {
            $target = public_path("vendor/lunar-panel/{$key}");

            if (is_link($target)) {
                info("The [{$target}] link already exists.");

                continue;
            }

            if (is_dir($target)) {
                warning("The [{$target}] directory already exists. Remove it first to create a symlink.");

                continue;
            }

            if (! is_dir($buildPath)) {
                warning("The build path [{$buildPath}] does not exist. Run `npm run build` in your module first.");

                continue;
            }

            $directory = dirname($target);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            symlink($buildPath, $target);

            info("The [{$target}] link has been connected to [{$buildPath}].");
        }

        return self::SUCCESS;
    }
}
