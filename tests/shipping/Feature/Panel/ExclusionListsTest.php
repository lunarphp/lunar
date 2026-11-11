<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Shipping\Models\ShippingExclusionList;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\PanelTestCase;

uses(PanelTestCase::class)->group('shipping', 'shipping-panel');

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

test('the exclusion lists index lists lists with their counts', function () {
    $list = ShippingExclusionList::factory()->create(['name' => 'Bulky']);
    $product = Product::factory()->create();
    $list->exclusions()->create(['purchasable_type' => $product->getMorphClass(), 'purchasable_id' => $product->id]);
    ShippingZone::factory()->create()->shippingExclusions()->sync([$list->id]);

    $this->get(route('panel.settings.shipping.exclusion-lists.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/exclusion-lists/Index', false)
            ->has('lists.data', 1)
            ->where('lists.data.0.name', 'Bulky')
            ->where('lists.data.0.exclusions_count', 1)
            ->where('lists.data.0.zones_count', 1)
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
        );
});

test('a list is created by name and names are unique', function () {
    $this->post(route('panel.settings.shipping.exclusion-lists.store'), ['name' => 'Hazardous'])
        ->assertRedirect(route('panel.settings.shipping.exclusion-lists.edit', ShippingExclusionList::first()));

    $this->post(route('panel.settings.shipping.exclusion-lists.store'), ['name' => 'Hazardous'])
        ->assertSessionHasErrors('name');
});

test('the edit screen carries the excluded products as chips and the zones it applies to', function () {
    $list = ShippingExclusionList::factory()->create();
    $product = Product::factory()->create(['name' => ['en' => 'Blue Widget']]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'BLUE-1']);
    $list->exclusions()->create(['purchasable_type' => $product->getMorphClass(), 'purchasable_id' => $product->id]);
    $zone = ShippingZone::factory()->create(['name' => 'UK']);
    $zone->shippingExclusions()->sync([$list->id]);

    $this->get(route('panel.settings.shipping.exclusion-lists.edit', $list))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shipping::settings/shipping/exclusion-lists/Edit', false)
            ->where('products.0.id', $product->id)
            ->where('products.0.label', 'Blue Widget')
            ->where('products.0.hint', 'BLUE-1')
            ->where('list.zones.0.name', 'UK')
            ->has('urls.search')
        );
});

test('updating a list replaces its products', function () {
    $list = ShippingExclusionList::factory()->create();
    [$a, $b] = Product::factory()->count(2)->create();
    $list->exclusions()->create(['purchasable_type' => $a->getMorphClass(), 'purchasable_id' => $a->id]);

    $this->put(route('panel.settings.shipping.exclusion-lists.update', $list), ['name' => 'Renamed', 'products' => [$b->id]])
        ->assertRedirect()->assertSessionHas('success');

    expect($list->refresh()->name)->toBe('Renamed')
        ->and($list->exclusions()->pluck('purchasable_id')->all())->toBe([$b->id]);
});

test('the product search matches names and SKUs and skips products already on the list', function () {
    $list = ShippingExclusionList::factory()->create();
    $blue = Product::factory()->create(['name' => ['en' => 'Blue Widget']]);
    $red = Product::factory()->create(['name' => ['en' => 'Red Widget']]);
    ProductVariant::factory()->create(['product_id' => $red->id, 'sku' => 'RED-99']);
    $list->exclusions()->create(['purchasable_type' => $blue->getMorphClass(), 'purchasable_id' => $blue->id]);

    $this->getJson(route('panel.settings.shipping.exclusion-lists.products.search', [$list, 'q' => 'Widget']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $red->id)
        ->assertJsonPath('data.0.kind', 'products');

    $this->getJson(route('panel.settings.shipping.exclusion-lists.products.search', [$list, 'q' => 'RED-9']))
        ->assertJsonPath('data.0.id', $red->id);
});

test('a list can be deleted', function () {
    $list = ShippingExclusionList::factory()->create();

    $this->delete(route('panel.settings.shipping.exclusion-lists.destroy', $list))
        ->assertRedirect(route('panel.settings.shipping.exclusion-lists.index'));

    expect(ShippingExclusionList::find($list->id))->toBeNull();
});
