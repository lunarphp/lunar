<?php

use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Filament\Contracts\DiscountFormType;
use Lunar\Filament\Support\ComponentExtensions\Registry;
use Lunar\Filament\Support\Facades\LunarFilament;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

class DiscountFormRegistryTestForm implements DiscountFormType
{
    public function lunarPanelSchema(): array
    {
        return [];
    }

    public function lunarPanelOnFill(array $data): array
    {
        return $data;
    }

    public function lunarPanelOnSave(array $data): array
    {
        return $data;
    }

    public function lunarPanelRelationManagers(): array
    {
        return [];
    }
}

class DiscountFormRegistryTestSelfFormingType extends PercentageOff implements DiscountFormType
{
    public function lunarPanelSchema(): array
    {
        return [];
    }

    public function lunarPanelOnFill(array $data): array
    {
        return $data;
    }

    public function lunarPanelOnSave(array $data): array
    {
        return $data;
    }

    public function lunarPanelRelationManagers(): array
    {
        return [];
    }
}

test('a type that implements the contract is its own form', function () {
    $type = new DiscountFormRegistryTestSelfFormingType;

    expect(app(Registry::class)->discountFormFor($type))->toBe($type);
});

test('a type with no form and no mapping contributes nothing', function () {
    expect(app(Registry::class)->discountFormFor(new PercentageOff))->toBeNull();
});

test('a mapped type resolves its separate form class through the facade', function () {
    LunarFilament::discountForm(PercentageOff::class, DiscountFormRegistryTestForm::class);

    expect(LunarFilament::discountForms())->toBe([PercentageOff::class => DiscountFormRegistryTestForm::class]);
    expect(LunarFilament::discountFormFor(new PercentageOff))->toBeInstanceOf(DiscountFormRegistryTestForm::class);
});
