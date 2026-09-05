<?php

use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Catalog records generate URLs on create, which needs a default language;
    // order rows format a total against the default currency.
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
});

function searchAs(Staff $staff, array $params = []): TestResponse
{
    return test()->actingAs($staff, 'staff')->getJson('/panel/search?'.http_build_query($params));
}

it('finds records across every source', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Product::factory()->create(['name' => collect(['en' => 'Radiant Lamp'])]);
    Brand::factory()->create(['name' => 'Radiant Co']);
    Customer::factory()->create(['first_name' => 'Radiant', 'last_name' => 'Person']);

    $rows = collect(searchAs($staff, ['q' => 'radiant'])->assertOk()->json('data'));

    expect($rows->pluck('kind')->unique()->sort()->values()->all())
        ->toEqual(['brands', 'customers', 'products']);
});

it('returns rows in a uniform shape', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $product = Product::factory()->create(['name' => collect(['en' => 'Uniform Shape Lamp'])]);

    $row = searchAs($staff, ['q' => 'Uniform Shape'])->assertOk()->json('data.0');

    expect($row)->toHaveKeys(['kind', 'kind_label', 'icon', 'id', 'label', 'hint', 'url'])
        ->and($row['kind'])->toBe('products')
        ->and($row['id'])->toBe($product->id)
        ->and($row['url'])->toContain("/panel/products/{$product->id}/edit");
});

it('narrows the fan-out to the requested kinds', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Product::factory()->create(['name' => collect(['en' => 'Narrow Lamp'])]);
    Brand::factory()->create(['name' => 'Narrow Co']);

    $rows = collect(searchAs($staff, ['q' => 'narrow', 'kinds' => ['brands']])->assertOk()->json('data'));

    expect($rows->pluck('kind')->unique()->all())->toEqual(['brands']);
});

it('caps the rows each source contributes so no source crowds out the rest', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Product::factory()->count(9)->sequence(fn ($sequence) => [
        'name' => collect(['en' => 'Crowded Lamp '.$sequence->index]),
    ])->create();

    Brand::factory()->create(['name' => 'Crowded Co']);

    $rows = collect(searchAs($staff, ['q' => 'crowded'])->assertOk()->json('data'));

    expect($rows->where('kind', 'products'))->toHaveCount(5)
        ->and($rows->where('kind', 'brands'))->toHaveCount(1);
});

it('matches every token regardless of word order', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Collection::factory()->create(['name' => collect(['en' => 'Black Friday Deals'])]);
    Collection::factory()->create(['name' => collect(['en' => 'Black Tie Formalwear'])]);

    $rows = collect(searchAs($staff, ['q' => 'friday black'])->assertOk()->json('data'));

    expect($rows->where('kind', 'collections'))->toHaveCount(1)
        ->and($rows->firstWhere('kind', 'collections')['label'])->toBe('Black Friday Deals');
});

it('finds an order by its reference and a customer by email', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $order = Order::factory()->create(['reference' => 'ORD-9182']);

    $rows = collect(searchAs($staff, ['q' => 'ORD-9182'])->assertOk()->json('data'));

    expect($rows->firstWhere('kind', 'orders')['id'])->toBe($order->id);
});

it('finds a product by variant sku', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $product = Product::factory()->create(['name' => collect(['en' => 'Sku Lamp'])]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'LAMP-4471']);

    $rows = collect(searchAs($staff, ['q' => 'LAMP-4471'])->assertOk()->json('data'));

    expect($rows->firstWhere('kind', 'products')['id'])->toBe($product->id);
});

it('returns nothing for a blank term', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Product::factory()->create(['name' => collect(['en' => 'Blank Lamp'])]);

    expect(searchAs($staff, ['q' => '  '])->assertOk()->json('data'))->toBe([]);
});

it('only searches sources the staff member has permission for', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('catalog:manage-products');

    Product::factory()->create(['name' => collect(['en' => 'Gated Lamp'])]);
    Customer::factory()->create(['first_name' => 'Gated', 'last_name' => 'Person']);
    Order::factory()->create(['reference' => 'GATED-1']);

    $rows = collect(searchAs($staff, ['q' => 'gated'])->assertOk()->json('data'));

    expect($rows->pluck('kind')->unique()->all())->toEqual(['products']);
});

it('returns nothing to a staff member with no section permissions', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    Product::factory()->create(['name' => collect(['en' => 'Ungated Lamp'])]);

    expect(searchAs($staff, ['q' => 'ungated'])->assertOk()->json('data'))->toBe([]);
});

it('requires authentication', function () {
    $this->getJson('/panel/search?q=lamp')->assertUnauthorized();
});

it('shares the visited record as a search row for the recently viewed list', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $product = Product::factory()->create(['name' => collect(['en' => 'Visited Lamp'])]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('visitedRecord.kind', 'products')
            ->where('visitedRecord.id', $product->id)
            ->where('visitedRecord.label', 'Visited Lamp'));
});

it('shares no visited record on a listing page', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('visitedRecord', null));
});

it('shares the visited record for a non-admin with the matching permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-customers');

    $customer = Customer::factory()->create(['first_name' => 'Hidden', 'last_name' => 'Person']);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('visitedRecord.kind', 'customers'));
});

it('shares the quick actions and kind chips on every page', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('searchCommands', 6)
            ->where('searchCommands.0.key', 'products.create')
            ->has('searchSources', 5)
            ->where('searchSources.0.key', 'orders'));
});
