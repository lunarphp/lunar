<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('brands list active brands only and embed their visible products', function (): void {
    $active = Brand::factory()->create(['handle' => 'acme', 'name' => 'Acme']);
    Brand::factory()->draft()->create(['handle' => 'draft']);

    $product = $this->visibleProduct($this->store, ['brand_id' => $active->id]);

    $response = $this->getJson('/api/storefront/v1/brands?include=products')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0'))->toMatchArray(['id' => $active->public_id, 'type' => 'brands', 'name' => 'Acme', 'handle' => 'acme']);
    expect($response->json('data.0.products.0.id'))->toBe($product->public_id);

    $this->getJson("/api/storefront/v1/brands/{$active->public_id}")->assertOk()->assertJsonPath('data.handle', 'acme');
    $this->getJson('/api/storefront/v1/brands?filter[handle]=nope')->assertOk()->assertJsonCount(0, 'data');
});

test('collections list published collections in the channel with group, parent and children', function (): void {
    $group = CollectionGroup::factory()->create(['handle' => 'menu']);

    $parent = Collection::factory()->published()->create(['collection_group_id' => $group->id, 'handle' => 'clothing']);
    $parent->scheduleChannel($this->store['channel']);
    $parent->scheduleCustomerGroup($this->store['group'], pivotData: ['enabled' => true, 'visible' => true]);

    $child = Collection::factory()->published()->create(['collection_group_id' => $group->id, 'handle' => 'shirts']);
    $child->scheduleChannel($this->store['channel']);
    $child->scheduleCustomerGroup($this->store['group'], pivotData: ['enabled' => true, 'visible' => true]);
    $child->appendToNode($parent)->save();

    Collection::factory()->draft()->create(['collection_group_id' => $group->id]);

    $response = $this->getJson('/api/storefront/v1/collections?include=group,parent,children&sort=created_at')->assertOk();

    expect($response->json('data.*.handle'))->toBe(['clothing', 'shirts']);
    expect($response->json('data.0.group.handle'))->toBe('menu');
    expect($response->json('data.0.children.0.id'))->toBe($child->public_id);
    expect($response->json('data.1.parent.id'))->toBe($parent->public_id);
    expect($response->json('data.1.parent_id'))->toBe($parent->public_id);

    expect($this->getJson('/api/storefront/v1/collections?filter[root]=1')->json('data.*.handle'))->toBe(['clothing']);
    expect($this->getJson("/api/storefront/v1/collections?filter[parent]={$parent->public_id}")->json('data.*.handle'))->toBe(['shirts']);
    expect($this->getJson('/api/storefront/v1/collections?filter[group]=menu')->json('data'))->toHaveCount(2);
});

test('collection groups embed their visible collections', function (): void {
    $group = CollectionGroup::factory()->create(['handle' => 'menu', 'name' => 'Menu']);

    $visible = Collection::factory()->published()->create(['collection_group_id' => $group->id]);
    $visible->scheduleChannel($this->store['channel']);
    $visible->scheduleCustomerGroup($this->store['group'], pivotData: ['enabled' => true, 'visible' => true]);

    Collection::factory()->draft()->create(['collection_group_id' => $group->id]);

    $response = $this->getJson("/api/storefront/v1/collection-groups/{$group->public_id}?include=collections")->assertOk();

    expect($response->json('data.name'))->toBe('Menu');
    expect($response->json('data.collections.*.id'))->toBe([$visible->public_id]);
});
