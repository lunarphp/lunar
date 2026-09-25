<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Contracts\ModelManifest as ModelManifestContract;
use Lunar\Core\Manifests\ModelManifest;

class ModelsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:models:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the discovered Lunar models for faster boots';

    /**
     * Execute the console command.
     */
    public function handle(ModelManifestContract $manifest): int
    {
        if (! $manifest instanceof ModelManifest) {
            $this->components->warn('The bound model manifest does not support caching.');

            return self::SUCCESS;
        }

        $models = $manifest->cache();

        $this->components->info(sprintf(
            'Cached %d models from %d directories.',
            count($models, COUNT_RECURSIVE) - count($models),
            count($models),
        ));

        return self::SUCCESS;
    }
}
