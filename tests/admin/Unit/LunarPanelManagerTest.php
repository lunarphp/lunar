<?php

use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Tests\Admin\Stubs\Filament\Extensions\ExtensionA;
use Lunar\Tests\Admin\Stubs\Filament\Extensions\ExtensionB;

uses(\Lunar\Tests\Admin\Unit\Filament\TestCase::class)
    ->group('lunar.panel');

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
