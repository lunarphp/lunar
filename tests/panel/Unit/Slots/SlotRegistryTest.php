<?php

use Illuminate\Support\Facades\Gate;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Slots\Slot;
use Lunar\Panel\Slots\SlotRegistry;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('returns only slots whose zone belongs to the requested page', function () {
    $registry = new SlotRegistry;
    $registry->add(new Slot(zone: 'customers.show:main:after', component: 'seo::SeoSection'));
    $registry->add(new Slot(zone: 'customers.index:actions', component: 'x::Export'));

    $slots = $registry->forPage('customers.show');

    expect($slots)->toHaveKeys(['customers.show:main:after'])
        ->and($slots)->not->toHaveKey('customers.index:actions')
        ->and($slots['customers.show:main:after'][0]['component'])->toBe('seo::SeoSection');
});

it('does not match pages whose name merely shares a prefix', function () {
    $registry = new SlotRegistry;
    $registry->add(new Slot(zone: 'customers.index:actions', component: 'x::Export'));

    expect($registry->forPage('customers'))->toBe([]);
});

it('orders slots in a zone by priority', function () {
    $registry = new SlotRegistry;
    $registry->add(new Slot(zone: 'p:main', component: 'b::Second', priority: 50));
    $registry->add(new Slot(zone: 'p:main', component: 'a::First', priority: 10));

    expect(array_column($registry->forPage('p')['p:main'], 'component'))
        ->toBe(['a::First', 'b::Second']);
});

it('filters slots by permission', function () {
    Gate::define('panel-test.slot', fn ($user) => (bool) $user->admin);

    $admin = Staff::factory()->create(['admin' => true]);
    $mortal = Staff::factory()->create(['admin' => false]);

    $registry = new SlotRegistry;
    $registry->add(new Slot(zone: 'p:main', component: 'x::Secret', permission: 'panel-test.slot'));

    expect($registry->forPage('p', $admin))->toHaveKey('p:main')
        ->and($registry->forPage('p', $mortal))->toBe([])
        ->and($registry->forPage('p', null))->toBe([]);
});

it('passes props through to the payload', function () {
    $registry = new SlotRegistry;
    $registry->add(new Slot(zone: 'p:side', component: 'x::Card', props: ['title' => 'Hello']));

    expect($registry->forPage('p')['p:side'][0]['props'])->toBe(['title' => 'Hello']);
});
