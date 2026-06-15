<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Manifests\FulfilmentMethodManifest;

/**
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest register(\Lunar\Core\Contracts\FulfilmentMethod|string $method)
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest set(iterable $methods)
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest forget(string ...$keys)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Lunar\Core\Contracts\FulfilmentMethod|null get(?string $key)
 * @method static array states()
 * @method static array transitions()
 * @method static array stateNamesIn(\Lunar\Core\Enums\FulfilmentStateCategory $category)
 *
 * @see FulfilmentMethodManifest
 */
class FulfilmentMethods extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\FulfilmentMethodManifest::class;
    }
}
