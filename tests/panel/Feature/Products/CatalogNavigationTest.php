<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('leads the catalog group with Products carrying the Product types child', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.products.index'))
        ->assertInertia(function (Assert $page) {
            $groups = collect($page->toArray()['props']['navigation']['groups']);
            $catalog = $groups->firstWhere('key', 'catalog');

            expect($catalog)->not->toBeNull();

            $items = collect($catalog['items']);

            expect($items->pluck('key')->all())->toBe(['products', 'brands', 'collections']);

            $products = $items->firstWhere('key', 'products');

            expect(collect($products['children'])->pluck('key')->all())->toBe(['all-products', 'product-types'])
                ->and(collect($products['children'])->firstWhere('key', 'all-products')['exact'])->toBeTrue();
        });
});

it('hides the product types child without the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    // A staff member without catalog:manage-products sees neither Products
    // nor its children; the group itself may be empty or absent.
    $this->get(route('panel.dashboard'))
        ->assertInertia(function (Assert $page) {
            $groups = collect($page->toArray()['props']['navigation']['groups']);
            $catalog = $groups->firstWhere('key', 'catalog');

            $keys = collect($catalog['items'] ?? [])->pluck('key');

            expect($keys->contains('products'))->toBeFalse();
        });
});
