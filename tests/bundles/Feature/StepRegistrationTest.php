<?php

use Lunar\Bundles\Pipelines\CartLine\PriceBundleSelection;
use Lunar\Bundles\Pipelines\Order\Creation\CreateBundleComponentLines;
use Lunar\Bundles\Validation\CartLine\BundleSelection;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Facades\Steps;
use Lunar\Core\Pipelines\CartLine\GetUnitPrice;
use Lunar\Core\Pipelines\Order\Creation\MapDiscountBreakdown;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);

test('the package registers its steps with core step lists', function () {
    expect(Steps::for(StepList::CartLinePricing)->all())->toBe([GetUnitPrice::class, PriceBundleSelection::class])
        ->and(Steps::for(StepList::AddToCartValidation)->all())->toContain(BundleSelection::class)
        ->and(Steps::for(StepList::UpdateCartLineValidation)->all())->toContain(BundleSelection::class)
        ->and(array_slice(Steps::for(StepList::OrderCreation)->all(), -2))->toBe([MapDiscountBreakdown::class, CreateBundleComponentLines::class]);
});
