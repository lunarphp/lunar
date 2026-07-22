<?php

use Illuminate\Support\Facades\Log;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Dashboard\LayoutResolver;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Dashboard\WidgetRegistry;
use Lunar\Tests\Panel\Fixtures\Dashboard\AlphaWidget;
use Lunar\Tests\Panel\Fixtures\Dashboard\AnchoredWidget;
use Lunar\Tests\Panel\Fixtures\Dashboard\BetaWidget;
use Lunar\Tests\Panel\Fixtures\Dashboard\DuplicateAlphaWidget;
use Lunar\Tests\Panel\Fixtures\Dashboard\GatedWidget;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('orders widgets by priority with anchors resolved', function () {
    $registry = new WidgetRegistry;
    $registry->add(AlphaWidget::class);
    $registry->add(AnchoredWidget::class);
    $registry->add(BetaWidget::class);

    $keys = array_map(fn (Widget $widget) => $widget->key(), $registry->for(null));

    expect($keys)->toBe(['beta', 'anchored', 'alpha']);
});

it('filters widgets by permission', function () {
    $registry = new WidgetRegistry;
    $registry->add(AlphaWidget::class);
    $registry->add(GatedWidget::class);

    $denied = Staff::factory()->create(['admin' => false]);
    $granted = Staff::factory()->create(['admin' => false]);
    $granted->givePermissionTo('sales:manage-orders');

    $keysFor = fn ($user) => array_map(fn (Widget $widget) => $widget->key(), $registry->for($user));

    expect($keysFor($denied))->toBe(['alpha']);
    expect($keysFor($granted))->toBe(['alpha', 'gated']);
    expect($keysFor(null))->toBe(['alpha']);
});

it('keeps the first registration for a duplicate key and warns', function () {
    Log::shouldReceive('warning')->once()->withArgs(fn (string $message) => str_contains($message, 'alpha'));

    $registry = new WidgetRegistry;
    $registry->add(AlphaWidget::class);
    $registry->add(DuplicateAlphaWidget::class);

    $widgets = $registry->for(null);

    expect(array_map(fn (Widget $widget) => $widget->key(), $widgets))->toBe(['alpha']);
    expect($widgets[0]->component())->toBe('FixtureWidget');
});

it('resolves a stored layout over the registered widgets', function () {
    $widgets = [app(BetaWidget::class), app(AlphaWidget::class), app(GatedWidget::class)];

    $layout = (new LayoutResolver)->resolve($widgets, [
        ['key' => 'alpha', 'visible' => false],
        ['key' => 'gone', 'visible' => true],
        ['key' => 'gated', 'visible' => true],
    ]);

    expect(array_map(fn (array $entry) => $entry['widget']->key(), $layout))->toBe(['alpha', 'gated', 'beta']);
    expect(array_map(fn (array $entry) => $entry['visible'], $layout))->toBe([false, true, true]);
});

it('falls back to default order and visibility without a stored layout', function () {
    $widgets = [app(BetaWidget::class), app(GatedWidget::class)];

    $layout = (new LayoutResolver)->resolve($widgets, null);

    expect(array_map(fn (array $entry) => $entry['widget']->key(), $layout))->toBe(['beta', 'gated']);
    // GatedWidget defaults to hidden.
    expect(array_map(fn (array $entry) => $entry['visible'], $layout))->toBe([true, false]);
});
