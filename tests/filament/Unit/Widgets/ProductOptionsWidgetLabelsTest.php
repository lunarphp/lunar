<?php

use Lunar\Filament\Widgets\Products\ProductOptionsWidget;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

/**
 * Without an explicit label Filament humanises the action name, which bypasses
 * translation entirely — "Save Variants" was hardcoded English for every locale.
 */
it('labels the widget actions from translations', function (string $method, string $key) {
    $widget = new ProductOptionsWidget;

    expect($widget->{$method}()->getLabel())
        ->toBe(__("lunar-filament::productoption.widgets.product-options.actions.{$key}.label"));
})->with([
    'save variants' => ['saveVariantsAction', 'save-variants'],
    'add shared option' => ['addSharedOptionAction', 'add-shared-option'],
]);
