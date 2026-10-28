<?php

namespace Lunar\Bundles\Actions;

use Lunar\Bundles\Contracts\Actions\DeletesBundle;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;

class DeleteBundle implements DeletesBundle
{
    public function execute(Bundle $bundle): void
    {
        $bundle->getConnection()->transaction(function () use ($bundle) {
            // Deleted through the models rather than the FK cascade so cache
            // invalidation and activity logging see every row go.
            $bundle->components()->get()->each(fn (BundleComponent $component) => $component->delete());
            $bundle->groups()->get()->each(fn (BundleGroup $group) => $group->delete());
            $bundle->delete();
        });
    }
}
