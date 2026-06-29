<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Manifests\FulfilmentMethodManifest;

/**
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest register(FulfilmentMethod|string $method)
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest set(iterable $methods)
 * @method static \Lunar\Core\Contracts\FulfilmentMethodManifest forget(string ...$keys)
 * @method static Collection all()
 * @method static FulfilmentMethod|null get(?string $key)
 * @method static array states()
 * @method static array transitions()
 * @method static array stateNamesIn(FulfilmentStateCategory $category)
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
