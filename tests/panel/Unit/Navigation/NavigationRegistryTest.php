<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('groups items and sorts by priority', function () {
    $registry = new NavigationRegistry;
    $registry->group('catalog', 'Catalog', priority: 10);
    $registry->addItem('catalog', new NavigationItem(key: 'products', label: 'Products', priority: 20));
    $registry->addItem('catalog', new NavigationItem(key: 'brands', label: 'Brands', priority: 10));

    $result = $registry->toArray();

    expect($result['groups'])->toHaveCount(1)
        ->and($result['groups'][0]['key'])->toBe('catalog')
        ->and(array_column($result['groups'][0]['items'], 'key'))->toBe(['brands', 'products']);
});

it('auto-creates a group when adding to an unknown group key', function () {
    $registry = new NavigationRegistry;
    $registry->addItem('sales', new NavigationItem(key: 'orders', label: 'Orders'));

    expect($registry->toArray()['groups'][0]['key'])->toBe('sales');
});

it('filters items by permission', function () {
    Gate::define('panel-test.secret', fn ($user) => (bool) $user->admin);

    $admin = Staff::factory()->create(['admin' => true]);
    $mortal = Staff::factory()->create(['admin' => false]);

    $registry = new NavigationRegistry;
    $registry->addItem('sales', new NavigationItem(key: 'orders', label: 'Orders', permission: 'panel-test.secret'));
    $registry->addItem('sales', new NavigationItem(key: 'open', label: 'Open', permission: null));

    $forAdmin = array_column($registry->toArray($admin)['groups'][0]['items'], 'key');
    $forMortal = array_column($registry->toArray($mortal)['groups'][0]['items'], 'key');
    $forGuest = array_column($registry->toArray(null)['groups'][0]['items'], 'key');

    // Both items share the default priority 50; PHP 8's stable sort keeps insertion order (orders, then open).
    expect($forAdmin)->toBe(['orders', 'open'])
        ->and($forMortal)->toBe(['open'])
        ->and($forGuest)->toBe(['open']);
});

it('drops groups whose items are all filtered out', function () {
    Gate::define('panel-test.secret', fn ($user) => false);
    $staff = Staff::factory()->create(['admin' => false]);

    $registry = new NavigationRegistry;
    $registry->addItem('hidden', new NavigationItem(key: 'x', label: 'X', permission: 'panel-test.secret'));

    expect($registry->toArray($staff)['groups'])->toBe([]);
});

it('resolves item urls from route names and nulls unknown routes', function () {
    Route::get('panel-test-route', fn () => 'ok')->name('panel.test');

    $registry = new NavigationRegistry;
    $registry->addTopLevelItem(new NavigationItem(key: 'known', label: 'Known', route: 'panel.test'));
    $registry->addTopLevelItem(new NavigationItem(key: 'unknown', label: 'Unknown', route: 'panel.missing'));

    $items = collect($registry->toArray()['items'])->keyBy('key');

    expect($items['known']['url'])->toContain('panel-test-route')
        ->and($items['unknown']['url'])->toBeNull();
});

it('nests child items and returns the first item by priority', function () {
    $registry = new NavigationRegistry;
    $registry->addItem('catalog', new NavigationItem(key: 'products', label: 'Products', priority: 5));
    $registry->addChildItem('products', new NavigationItem(key: 'variants', label: 'Variants'));

    expect($registry->toArray()['groups'][0]['items'][0]['children'][0]['key'])->toBe('variants')
        ->and($registry->firstItem()->key)->toBe('products');
});

it('assembles configured menus from section-tagged groups', function () {
    config()->set('lunar.panel.menus', [
        ['key' => 'shop', 'label' => 'Shop', 'icon' => 'package', 'sections' => ['catalog']],
        ['key' => 'other', 'label' => 'Other', 'icon' => 'dots', 'sections' => []],
    ]);

    $registry = new NavigationRegistry;
    $registry->beginSection('catalog');
    $registry->addItem('products-group', new NavigationItem(key: 'products', label: 'Products'));
    $registry->endSection();
    $registry->beginSection('orphan');
    $registry->addItem('orphan-group', new NavigationItem(key: 'lost', label: 'Lost'));
    $registry->endSection();

    $result = $registry->toArray();

    expect($result)->toHaveKey('menus')
        ->and($result['menus'][0]['key'])->toBe('shop')
        ->and($result['menus'][0]['groups'][0]['key'])->toBe('products-group')
        // Groups matching no menu fall into the last menu.
        ->and($result['menus'][1]['groups'][0]['key'])->toBe('orphan-group');
});

it('ignores menus config when skipMenus is set', function () {
    config()->set('lunar.panel.menus', [
        ['key' => 'shop', 'label' => 'Shop', 'icon' => 'package', 'sections' => ['catalog']],
    ]);

    $registry = new NavigationRegistry;
    $registry->addItem('g', new NavigationItem(key: 'i', label: 'I'));

    expect($registry->toArray(skipMenus: true))->toHaveKey('groups');
});
