<?php

namespace Lunar\Bundles\Panel\Http\Requests\Concerns;

use Illuminate\Validation\ValidationException;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\ProductVariant;

trait RequiresBundle
{
    /**
     * Components and groups only exist on a defined bundle; syncing against
     * an ordinary variant is a client error, not a way to define one.
     */
    public function bundle(ProductVariant $variant): Bundle
    {
        $variant->loadMissing('bundle');

        /** @var ?Bundle $bundle */
        $bundle = $variant->bundle;

        if (! $bundle) {
            throw ValidationException::withMessages([
                'bundle' => [__('bundles::bundles.panel.not_defined')],
            ]);
        }

        return $bundle;
    }
}
