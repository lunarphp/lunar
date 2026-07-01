<?php

declare(strict_types=1);
use Illuminate\Database\Eloquent\Model;
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
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\DiscountCollection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductAssociation;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZoneCountry;
use Lunar\Core\Models\TaxZoneCustomerGroup;
use Lunar\Core\Models\TaxZoneState;
use Lunar\Core\Models\UserPermission;

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

/*
|--------------------------------------------------------------------------
| public_id membership (spec 0046)
|--------------------------------------------------------------------------
|
| Every standalone model carries a public_id external address, except two
| kinds: link/pivot models (addressed through the rows they join) and
| immutable-standard-code models (the ISO code is already a stable public
| id). A new model that neither uses HasPublicId nor is listed below fails
| this test, forcing a conscious include/exclude decision.
|
*/
test('every model carries a public_id except the documented exclusions', function () {
    // Concrete models deliberately WITHOUT a public_id.
    $withoutPublicId = [
        // Link / pivot models — no independent identity; addressed through their parents.
        AttributeModel::class,
        Discountable::class,
        DiscountCollection::class,
        ProductAssociation::class,
        UserPermission::class,
        TaxZoneCountry::class,
        TaxZoneState::class,
        TaxZoneCustomerGroup::class,
        // Immutable-standard-code models — the ISO code is already the stable public id.
        Country::class,
        Currency::class,
        Language::class,
        State::class,
    ];

    $dir = dirname((new ReflectionClass(Base::class))->getFileName());

    foreach (glob($dir.'/*.php') as $file) {
        $class = 'Lunar\\Core\\Models\\'.basename($file, '.php');

        if (! is_subclass_of($class, Model::class)) {
            continue;
        }

        if ((new ReflectionClass($class))->isAbstract()) {
            continue;
        }

        $usesPublicId = in_array(
            HasPublicId::class,
            class_uses_recursive($class),
            true,
        );

        if (in_array($class, $withoutPublicId, true)) {
            expect($usesPublicId)->toBeFalse("{$class} is on the public_id exclusion list but uses HasPublicId — remove it from the list or drop the trait.");
        } else {
            expect($usesPublicId)->toBeTrue("{$class} must use HasPublicId (add the trait, or add the class to the exclusion list in this test).");
        }
    }
});
