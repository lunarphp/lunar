<?php

namespace Lunar\Bundles\Console;

use Illuminate\Console\Command;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;

class RepriceBundlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lunar:bundles:reprice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute the materialised prices of every components-priced bundle';

    public function handle(RepricesBundle $reprice): int
    {
        $count = 0;

        Bundle::query()
            ->where('pricing', BundlePricing::Components)
            ->with('variant')
            ->chunkById(100, function ($bundles) use ($reprice, &$count) {
                foreach ($bundles as $bundle) {
                    $reprice->execute($bundle);
                    $count++;
                }
            });

        $this->components->info(__('bundles::bundles.console.repriced', ['count' => $count]));

        return self::SUCCESS;
    }
}
