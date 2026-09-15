<?php

use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Facades\Taxes;

/*
|--------------------------------------------------------------------------
| Action conventions (spec 0016, mirrored from the core suite)
|--------------------------------------------------------------------------
*/

arch('actions implement a contract')
    ->expect('Lunar\Bundles\Actions')
    ->classes()
    ->not->toImplementNothing();

arch('actions expose an execute method')
    ->expect('Lunar\Bundles\Actions')
    ->classes()
    ->toHaveMethod('execute');

arch('actions do not depend on Lunar service facades')
    ->expect('Lunar\Bundles\Actions')
    ->not->toUse([
        Taxes::class,
        Pricing::class,
        Payments::class,
        Discounts::class,
        PriceCalculator::class,
        Carriers::class,
    ]);
