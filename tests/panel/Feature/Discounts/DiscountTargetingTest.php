<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Creating catalogue records runs the URL generator, which needs a default.
    Language::factory()->create(['default' => true]);

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('sends the current targeting and resolved chips to the edit screen', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $product = Product::factory()->create();
    $collection = Collection::factory()->create();
    $brand = Brand::factory()->create(['name' => 'Stark']);
    $customer = Customer::factory()->create(['title' => null, 'first_name' => 'Ada', 'last_name' => 'Lovelace']);

    $discount->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => $product->id,
        'type' => 'limitation',
    ]);
    $discount->collections()->attach($collection->id, ['type' => 'limitation']);
    $discount->brands()->attach($brand->id, ['type' => 'limitation']);
    $discount->customers()->attach($customer->id);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('targets.target:limitation.products', [$product->id])
            ->where('targets.target:limitation.collections', [$collection->id])
            ->where('targets.target:limitation.brands', [$brand->id])
            ->where('targets.target:limitation.customers', [$customer->id])
            ->where('targetChips.target:limitation.brands.0.label', 'Stark')
            ->where('targetChips.target:limitation.customers.0.label', 'Ada Lovelace')
            ->has('urls.targetSearch')
        );
});

it('reads condition and reward collections from the morph table', function () {
    // The bucket decides the table: a limitation collection is on the pivot, a
    // condition collection is a discountable.
    $discount = Discount::factory()->create(['type' => BuyXGetY::class]);
    $collection = Collection::factory()->create();

    $discount->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collection->id,
        'type' => 'condition',
    ]);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('targets.target:condition.collections', [$collection->id])
            ->where('targets.target:limitation.collections', [])
        );
});

it('commits drafted targeting through the update action', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();

    $this->patchJson(route('panel.discounts.draft.update', $discount), [
        'data' => [
            'target:limitation' => [
                'products' => [$product->id],
                'collections' => [$collection->id],
            ],
        ],
    ])->assertOk();

    $this->postJson(route('panel.discounts.draft.commit', $discount), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($discount->collections()->wherePivot('type', 'limitation')->get()->pluck('id')->all())
        ->toBe([$collection->id]);
    expect(Discountable::whereDiscountId($discount->id)->whereType('limitation')->pluck('discountable_id')->all())
        ->toBe([$product->id]);
});

it('leaves targeting alone when a draft never touches it', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);
    $brand = Brand::factory()->create();

    $discount->brands()->attach($brand->id, ['type' => 'limitation']);

    $this->patchJson(route('panel.discounts.draft.update', $discount), [
        'data' => ['name' => 'Renamed'],
    ])->assertOk();

    $this->postJson(route('panel.discounts.draft.commit', $discount), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($discount->brands()->count())->toBe(1);
});

it('says where a collection lives, since its name alone is ambiguous', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $group = CollectionGroup::factory()->create(['name' => 'Main']);
    $parent = Collection::factory()->create(['collection_group_id' => $group->id, 'name' => ['en' => 'Seasonal']]);
    $child = Collection::factory()->create(['collection_group_id' => $group->id, 'name' => ['en' => 'Sale']]);
    $child->appendToNode($parent)->save();

    $response = $this->getJson(
        route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=Sale&kinds[]=collections'
    );

    expect($response->json('data.0.label'))->toBe('Sale');
    expect($response->json('data.0.hint'))->toBe('Main / Seasonal');
});

it('falls back to the group alone for a top-level collection', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $group = CollectionGroup::factory()->create(['name' => 'Main']);
    Collection::factory()->create(['collection_group_id' => $group->id, 'name' => ['en' => 'Clearance']]);

    $response = $this->getJson(
        route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=Clearance&kinds[]=collections'
    );

    expect($response->json('data.0.hint'))->toBe('Main');
});

it('carries the same collection context onto the chips', function () {
    // The picker and the chips share one row builder, so a target reads the
    // same before and after it is selected.
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $group = CollectionGroup::factory()->create(['name' => 'Main']);
    $parent = Collection::factory()->create(['collection_group_id' => $group->id, 'name' => ['en' => 'Seasonal']]);
    $child = Collection::factory()->create(['collection_group_id' => $group->id, 'name' => ['en' => 'Sale']]);
    $child->appendToNode($parent)->save();

    $discount->collections()->attach($child->id, ['type' => 'limitation']);

    $this->get(route('panel.discounts.edit', $discount))
        ->assertInertia(fn (Assert $page) => $page
            ->where('targetChips.target:limitation.collections.0.label', 'Sale')
            ->where('targetChips.target:limitation.collections.0.hint', 'Main / Seasonal')
        );
});

it('searches every kind the bucket can target', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    Product::factory()->create(['name' => ['en' => 'Widget']]);
    Brand::factory()->create(['name' => 'Widget Co']);
    Collection::factory()->create(['name' => ['en' => 'Widgets']]);

    $response = $this->getJson(route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=Widget');

    $response->assertOk();

    expect(collect($response->json('data'))->pluck('kind')->unique()->sort()->values()->all())
        ->toBe(['brands', 'collections', 'products']);
});

it('narrows the search to the requested kinds', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    Product::factory()->create(['name' => ['en' => 'Widget']]);
    Brand::factory()->create(['name' => 'Widget Co']);

    $response = $this->getJson(
        route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=Widget&kinds[]=brands'
    );

    expect(collect($response->json('data'))->pluck('kind')->unique()->all())->toBe(['brands']);
});

it('refuses a kind the bucket cannot target', function () {
    $discount = Discount::factory()->create(['type' => BuyXGetY::class]);

    Brand::factory()->create(['name' => 'Widget Co']);

    // The condition bucket has no brands, so asking for them yields nothing
    // rather than offering a target the action would reject.
    $response = $this->getJson(
        route('panel.discounts.targets.search', $discount).'?bucket=condition&q=Widget&kinds[]=brands'
    );

    expect($response->json('data'))->toBe([]);
});

it('leaves out targets the bucket already holds', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $taken = Brand::factory()->create(['name' => 'Widget Co']);
    $free = Brand::factory()->create(['name' => 'Widget Ltd']);

    $discount->brands()->attach($taken->id, ['type' => 'limitation']);

    $response = $this->getJson(route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=Widget&kinds[]=brands');

    expect(collect($response->json('data'))->pluck('id')->all())->toBe([$free->id]);
});

it('finds a variant by sku', function () {
    $discount = Discount::factory()->create(['type' => PercentageOff::class]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WIDGET-01']);

    $response = $this->getJson(route('panel.discounts.targets.search', $discount).'?bucket=limitation&q=WIDGET-01&kinds[]=variants');

    expect($response->json('data.0.id'))->toBe($variant->id);
    expect($response->json('data.0.label'))->toBe('WIDGET-01');
});

it('gates the target search on the manage-discounts permission', function () {
    auth('staff')->logout();

    $discount = Discount::factory()->create();

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff')
        ->get(route('panel.discounts.targets.search', $discount))
        ->assertForbidden();
});
