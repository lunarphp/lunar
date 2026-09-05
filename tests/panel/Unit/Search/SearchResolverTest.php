<?php

use Lunar\Core\Models\Staff;
use Lunar\Panel\Facades\Panel;
use Lunar\Panel\Search\SearchCommandResolver;
use Lunar\Panel\Search\SearchSourceResolver;
use Lunar\Tests\Panel\Fixtures\Search\ProductTypeSearchSource;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('orders sources by position', function () {
    $resolver = new SearchSourceResolver(Panel::getSearchSources(), Staff::factory()->create(['admin' => true]));

    $keys = collect($resolver->visible())->map->key()->all();

    expect($keys)->toEqual(['orders', 'products', 'collections', 'brands', 'customers']);
});

it('exposes the visible sources as kind chips', function () {
    $chips = (new SearchSourceResolver(Panel::getSearchSources(), Staff::factory()->create(['admin' => true])))->kinds();

    expect($chips[0])->toHaveKeys(['key', 'label', 'icon'])
        ->and($chips[0]['key'])->toBe('orders');
});

it('hides sources the user has no permission for', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-orders');

    $keys = collect((new SearchSourceResolver(Panel::getSearchSources(), $staff))->visible())->map->key()->all();

    expect($keys)->toEqual(['orders']);
});

it('treats a source with no permission as visible to everyone', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $keys = collect((new SearchSourceResolver([ProductTypeSearchSource::class], $staff))->visible())->map->key()->all();

    expect($keys)->toEqual(['product-types']);
});

it('resolves commands into the payload the palette filters', function () {
    $commands = (new SearchCommandResolver(Panel::getSearchCommands(), Staff::factory()->create(['admin' => true])))->resolve();

    expect($commands[0])->toHaveKeys(['key', 'label', 'url', 'icon'])
        ->and(collect($commands)->pluck('key')->all())->toEqual([
            'products.create',
            'collections.create',
            'brands.create',
            'customers.create',
            'discounts.create',
            'product-types.create',
        ]);
});

it('hides commands the user has no permission for', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-discounts');

    $commands = (new SearchCommandResolver(Panel::getSearchCommands(), $staff))->resolve();

    expect(collect($commands)->pluck('key')->all())->toEqual(['discounts.create']);
});

it('registers the built-in sources and commands from the sections', function () {
    expect(Panel::getSearchSources())->toHaveCount(5)
        ->and(Panel::getSearchCommands())->toHaveCount(6);
});
