<?php

namespace Lunar\Shipping\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Models\Contracts\Country as CountryContract;
use Lunar\Shipping\Interfaces\PostcodeResolverInterface;
use Lunar\Shipping\Managers\PostcodeManager;

/**
 * @method static \Lunar\Shipping\Managers\PostcodeManager addResolver(string|PostcodeResolverInterface|array $resolver)
 * @method static PostcodeResolverInterface country(CountryContract $country)
 * @method static Collection getResolvers()
 *
 * @see PostcodeManager
 */
class Postcode extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return PostcodeManager::class;
    }
}
