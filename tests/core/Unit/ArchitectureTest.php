<?php

declare(strict_types=1);
use Lunar\Core\Actions\Collections\SortProductsByPrice;
use Lunar\Core\Actions\Collections\SortProductsBySku;
use Lunar\Core\Actions\Taxes\GetTaxZoneCountry;
use Lunar\Core\Actions\Taxes\GetTaxZonePostcode;
use Lunar\Core\Actions\Taxes\GetTaxZoneState;
use Lunar\Core\Facades\Carriers;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Facades\Pricing;
use Lunar\Core\Facades\Taxes;

/*
|--------------------------------------------------------------------------
| Action conventions (spec 0016)
|--------------------------------------------------------------------------
|
| There is no longer an AbstractAction base class to anchor the action
| layer, so these rules keep new actions consistent: each one implements a
| contract, exposes a single `execute()` entry point, and injects its
| collaborators rather than reaching for a Lunar service facade.
|
*/

// The internal strategy / sub-lookup helpers are deliberately concrete —
// they are injected into their public dispatcher (SortProducts, GetTaxZone)
// and are not a swappable seam of their own.
$internalActionHelpers = [
    SortProductsByPrice::class,
    SortProductsBySku::class,
    GetTaxZoneCountry::class,
    GetTaxZonePostcode::class,
    GetTaxZoneState::class,
];

arch('actions implement a contract')
    ->expect('Lunar\Core\Actions')
    ->classes()
    ->not->toImplementNothing()
    ->ignoring($internalActionHelpers);

arch('actions expose an execute method')
    ->expect('Lunar\Core\Actions')
    ->classes()
    ->toHaveMethod('execute');

arch('actions do not depend on Lunar service facades')
    ->expect('Lunar\Core\Actions')
    ->not->toUse([
        Taxes::class,
        Pricing::class,
        Payments::class,
        Discounts::class,
        PriceCalculator::class,
        Carriers::class,
    ]);
