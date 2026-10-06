<?php

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Tests\Admin\Stubs\Filament\Extensions\ExtensionA;
use Lunar\Tests\Admin\Stubs\Filament\Extensions\ExtensionB;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('lunar.admin');

it('can register multiple extensions at once', function () {

    $panel = LunarPanel::extensions([
        ViewCustomer::class => [ExtensionA::class, ExtensionB::class],
        EditProduct::class => ExtensionA::class,
        ListProducts::class => 'SomeClassThatDoesntExist',
    ]);

    expect($panel->getExtensions())->toHaveCount(3)
        ->and($panel->getExtensions())->toHaveKey(ViewCustomer::class)
        ->and($panel->getExtensions()[ViewCustomer::class])->toHaveCount(2)
        ->and($panel->getExtensions()[ViewCustomer::class][0])->toBeInstanceOf(ExtensionA::class)
        ->and($panel->getExtensions()[ViewCustomer::class][1])->toBeInstanceOf(ExtensionB::class)
        ->and($panel->getExtensions()[EditProduct::class][0])->toBeInstanceOf(ExtensionA::class)
        ->and($panel->getExtensions()[ListProducts::class])->toHaveCount(0);
});

it('honours the configured staff guard', function () {
    expect(Filament::getPanel('lunar')->getAuthGuard())->toBe('staff');

    config(['lunar.staff.guard' => 'backoffice']);

    $captured = null;
    LunarPanel::panel(function (Panel $panel) use (&$captured) {
        $captured = $panel;

        return $panel;
    })->register();

    expect($captured->getAuthGuard())->toBe('backoffice');
});

it('registers navigation groups matching the translated resource group names', function () {
    $this->app->setLocale('de');

    $registered = collect(Filament::getPanel('lunar')->getNavigationGroups())
        ->map(fn (NavigationGroup|string $group) => $group instanceof NavigationGroup
            ? $group->getLabel()
            : $group);

    expect($registered->all())
        ->toContain(ProductResource::getNavigationGroup())
        ->toContain(DiscountResource::getNavigationGroup())
        ->toContain(ChannelResource::getNavigationGroup())
        // Literals only coincide with the resource names in English.
        ->not->toContain('Catalog')
        ->not->toContain('Sales')
        ->not->toContain('Settings');
});
