<?php

use Filament\Widgets\Widget;
use Lunar\Shipping\Managers\PostcodeManager;
use Lunar\Shipping\Resolvers\PostcodeResolver;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class)
    ->group('shipping', 'shipping-postcode');

test('the service provider binds PostcodeManager as a singleton with the default resolver pre-registered', function () {
    $manager = app(PostcodeManager::class);

    expect($manager)->toBeInstanceOf(PostcodeManager::class);
    expect(app(PostcodeManager::class))->toBe($manager); // singleton — same instance

    $resolvers = $manager->getResolvers();
    expect($resolvers)->toHaveCount(1);
    expect($resolvers->first())->toBe(PostcodeResolver::class);
});

test('the service provider registers the Filament widget view when Filament is installed', function () {
    expect(class_exists(Widget::class))->toBeTrue();

    expect(view()->exists('shipping::widgets.availability-schedule'))->toBeTrue();
});
