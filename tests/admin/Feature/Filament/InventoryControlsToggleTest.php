<?php

use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class);

afterEach(function () {
    LunarPanel::withoutInventoryControls(false);
});

test('inventory controls are enabled by default', function () {
    expect(LunarPanel::usesInventoryControls())->toBeTrue()
        ->and(ManageVariantInventory::shouldRegisterNavigation())->toBeTrue();
});

test('withoutInventoryControls hides the built-in inventory page', function () {
    LunarPanel::withoutInventoryControls();

    expect(LunarPanel::usesInventoryControls())->toBeFalse()
        ->and(ManageVariantInventory::shouldRegisterNavigation())->toBeFalse();
});
