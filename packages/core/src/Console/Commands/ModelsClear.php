<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Contracts\ModelManifest as ModelManifestContract;
use Lunar\Core\Manifests\ModelManifest;

class ModelsClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:models:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the cached Lunar models file';

    /**
     * Execute the console command.
     */
    public function handle(ModelManifestContract $manifest): int
    {
        if ($manifest instanceof ModelManifest) {
            $manifest->clearCache();
        }

        $this->components->info('Cached models cleared successfully.');

        return self::SUCCESS;
    }
}
