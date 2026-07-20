<?php

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);
    // A sort-rule change re-sorts the pivot by price, which needs a default currency.
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    $this->collection = Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
    ]);
});

function attachProducts(Collection $collection, int $count): Illuminate\Support\Collection
{
    $products = Product::factory()->count($count)->create();

    $collection->products()->attach(
        $products->values()->mapWithKeys(fn (Product $product, int $index) => [
            $product->id => ['position' => $index + 1],
        ])->all()
    );

    return $products;
}

it('attaches picked products at the end of the position sequence', function () {
    $existing = attachProducts($this->collection, 1);
    $new = Product::factory()->count(2)->create();

    $this->post(route('panel.collections.products.attach', $this->collection), [
        'ids' => $new->pluck('id')->all(),
    ])->assertRedirect()->assertSessionHas('success');

    $pivot = $this->collection->products()->get()->mapWithKeys(
        fn (Product $product) => [$product->id => (int) $product->pivot->position]
    );

    expect($pivot[$existing->first()->id])->toBe(1)
        ->and($pivot[$new[0]->id])->toBe(2)
        ->and($pivot[$new[1]->id])->toBe(3);
});

it('ignores already-attached products on attach', function () {
    $products = attachProducts($this->collection, 2);

    $this->post(route('panel.collections.products.attach', $this->collection), [
        'ids' => [$products->first()->id],
    ])->assertRedirect();

    expect($this->collection->products()->count())->toBe(2)
        ->and((int) $this->collection->products()->find($products->first()->id)->pivot->position)->toBe(1);
});

it('detaches a product', function () {
    $products = attachProducts($this->collection, 2);

    $this->delete(route('panel.collections.products.detach', [$this->collection, $products->first()]))
        ->assertRedirect()->assertSessionHas('success');

    expect($this->collection->products()->count())->toBe(1);
});

it('reorders the given page window when the sort rule is custom', function () {
    $products = attachProducts($this->collection, 3);

    $this->post(route('panel.collections.products.reorder', $this->collection), [
        'ids' => [$products[2]->id, $products[0]->id, $products[1]->id],
        'offset' => 0,
    ])->assertRedirect()->assertSessionHas('success');

    $ordered = $this->collection->products()->get()->pluck('id')->all();

    expect($ordered)->toBe([$products[2]->id, $products[0]->id, $products[1]->id]);
});

it('refuses to reorder while the sort rule is not custom', function () {
    $products = attachProducts($this->collection, 2);
    $this->collection->update(['sort' => 'min_price:asc']);

    $this->post(route('panel.collections.products.reorder', $this->collection), [
        'ids' => [$products[1]->id, $products[0]->id],
    ])->assertSessionHas('error');

    expect((int) $this->collection->products()->find($products[0]->id)->pivot->position)->toBe(1);
});

it('searches products by name and sku', function () {
    $product = Product::factory()->create(['name' => ['en' => 'Rain Jacket']]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'RJ-001']);
    Product::factory()->create(['name' => ['en' => 'Sun Hat']]);

    $this->getJson(route('panel.catalog.products.search', ['q' => 'Rain']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Rain Jacket');

    $this->getJson(route('panel.catalog.products.search', ['q' => 'RJ-001']))
        ->assertJsonCount(1, 'data');
});

it('gates product curation behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.collections.products.attach', $this->collection), [
        'ids' => [1],
    ])->assertForbidden();
});
