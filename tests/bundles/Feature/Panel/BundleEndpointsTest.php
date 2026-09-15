<?php

use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Bundles\PanelTestCase;
use Lunar\Tests\Bundles\Support\Catalogue;

uses(PanelTestCase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
    $this->variant = ProductVariant::factory()->create(['sku' => 'KIT-1']);
});

function actingAsAdmin(): Staff
{
    $staff = Staff::factory()->create(['admin' => true]);
    test()->actingAs($staff, 'staff');

    return $staff;
}

it('redirects guests and forbids staff without the products permission', function () {
    $this->getJson(route('panel.bundles.variants.show', $this->variant))->assertUnauthorized();
    $this->get(route('panel.bundles.variants.show', $this->variant))->assertRedirect(route('panel.login'));

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->getJson(route('panel.bundles.variants.show', $this->variant))->assertForbidden();
    $this->getJson(route('panel.bundles.search-variants'))->assertForbidden();
    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'fixed'])->assertForbidden();
    $this->deleteJson(route('panel.bundles.variants.destroy', $this->variant))->assertForbidden();
    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => []])->assertForbidden();
    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => []])->assertForbidden();
    $this->getJson(route('panel.bundles.products.show', $this->variant->product))->assertForbidden();
    $this->getJson(route('panel.bundles.products.included-in', $this->variant->product))->assertForbidden();
});

it('summarises an ordinary variant as undefined', function () {
    actingAsAdmin();

    $this->getJson(route('panel.bundles.variants.show', $this->variant))
        ->assertOk()
        ->assertJsonPath('defined', false)
        ->assertJsonPath('bundle', null)
        ->assertJsonPath('variant.sku', 'KIT-1')
        ->assertJsonPath('default_language', 'en')
        ->assertJsonPath('urls.define', route('panel.bundles.variants.update', $this->variant));
});

it('defines, redefines and removes a bundle', function () {
    actingAsAdmin();

    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'fixed'])
        ->assertOk()
        ->assertJsonPath('defined', true)
        ->assertJsonPath('bundle.pricing', 'fixed')
        ->assertJsonPath('bundle.components', [])
        ->assertJsonPath('bundle.availability.available', 0);

    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'components', 'discount_percentage' => 12.5])
        ->assertOk()
        ->assertJsonPath('bundle.pricing', 'components')
        ->assertJsonPath('bundle.discount_percentage', 12.5);

    expect(Bundle::query()->where('product_variant_id', $this->variant->id)->count())->toBe(1);

    $this->deleteJson(route('panel.bundles.variants.destroy', $this->variant))
        ->assertOk()
        ->assertJsonPath('defined', false);

    expect(Bundle::query()->count())->toBe(0);
});

it('validates the definition payload', function () {
    actingAsAdmin();

    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'magic'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pricing']);

    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'components', 'discount_percentage' => 120])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['discount_percentage']);
});

it('refuses to define a component variant as a bundle with a translated 422', function () {
    actingAsAdmin();

    $other = Bundle::factory()->create();
    BundleComponent::factory()->create(['bundle_id' => $other->id, 'product_variant_id' => $this->variant->id]);

    $this->putJson(route('panel.bundles.variants.update', $this->variant), ['pricing' => 'fixed'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.bundle.0', __('bundles::bundles.nesting.is_component'));
});

it('syncs components and reports derived availability and prices', function () {
    actingAsAdmin();

    $body = Catalogue::variant($this->currency, 10000, 10, ['sku' => 'BODY']);
    $bag = Catalogue::variant($this->currency, 2500, 4, ['sku' => 'BAG']);

    Bundle::factory()->componentPriced(10)->create(['product_variant_id' => $this->variant->id]);

    $response = $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $body->id, 'quantity' => 1],
        ['variant_id' => $bag->id, 'quantity' => 2],
    ]])
        ->assertOk()
        ->assertJsonCount(2, 'bundle.components')
        ->assertJsonPath('bundle.components.0.variant.sku', 'BODY')
        ->assertJsonPath('bundle.components.1.variant.sku', 'BAG')
        ->assertJsonPath('bundle.components.1.quantity', 2)
        ->assertJsonPath('bundle.availability.available', 2)
        ->assertJsonPath('bundle.prices.0.currency', 'GBP')
        ->assertJsonPath('bundle.prices.0.missing', []);

    // 10000 + 2 x 2500 = 15000, less 10% = 13500.
    expect(Price::query()->where('priceable_id', $this->variant->id)->value('price'))->toBe(13500)
        ->and($response->json('bundle.prices.0.price'))->toContain('135');

    // Reordering and removal go through the same complete-list sync.
    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $bag->id, 'quantity' => 1, 'position' => 0],
    ]])
        ->assertOk()
        ->assertJsonCount(1, 'bundle.components')
        ->assertJsonPath('bundle.components.0.variant.sku', 'BAG');
});

it('names the component missing a price in a currency', function () {
    actingAsAdmin();

    $unpriced = ProductVariant::factory()->create(['sku' => 'NOPRICE']);
    Bundle::factory()->componentPriced()->create(['product_variant_id' => $this->variant->id]);

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $unpriced->id, 'quantity' => 1],
    ]])
        ->assertOk()
        ->assertJsonPath('bundle.prices.0.price', null)
        ->assertJsonCount(1, 'bundle.prices.0.missing');
});

it('rejects invalid component syncs with a 422', function () {
    actingAsAdmin();

    Bundle::factory()->create(['product_variant_id' => $this->variant->id]);
    $other = Bundle::factory()->create();

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => []])
        ->assertUnprocessable()
        ->assertJsonPath('errors.components.0', __('bundles::bundles.definition.empty'));

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $other->product_variant_id, 'quantity' => 1],
    ]])
        ->assertUnprocessable()
        ->assertJsonPath('errors.components.0', __('bundles::bundles.nesting.is_bundle'));

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $this->variant->id, 'quantity' => 1],
    ]])
        ->assertUnprocessable()
        ->assertJsonPath('errors.components.0', __('bundles::bundles.nesting.self'));

    config()->set('lunar.bundles.max_components', 1);
    $parts = ProductVariant::factory()->count(2)->create();

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => $parts
        ->map(fn (ProductVariant $part) => ['variant_id' => $part->id, 'quantity' => 1])
        ->all()])
        ->assertUnprocessable()
        ->assertJsonPath('errors.components.0', __('bundles::bundles.definition.too_many_components', ['max' => 1]));

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => 999999, 'quantity' => 0],
    ]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['components.0.variant_id', 'components.0.quantity']);
});

it('refuses component and group syncs on an undefined variant', function () {
    actingAsAdmin();

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => []])
        ->assertUnprocessable()
        ->assertJsonPath('errors.bundle.0', __('bundles::bundles.panel.not_defined'));

    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => []])
        ->assertUnprocessable()
        ->assertJsonPath('errors.bundle.0', __('bundles::bundles.panel.not_defined'));
});

it('syncs groups with their options and defaults', function () {
    actingAsAdmin();

    $bundle = Bundle::factory()->create(['product_variant_id' => $this->variant->id]);
    $body = Catalogue::variant($this->currency, 10000, 10, ['sku' => 'BODY']);
    $lensA = Catalogue::variant($this->currency, 3000, 5, ['sku' => 'LENS-A']);
    $lensB = Catalogue::variant($this->currency, 4000, 1, ['sku' => 'LENS-B']);

    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => [
        ['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1],
    ]])
        ->assertOk()
        ->assertJsonCount(1, 'bundle.groups')
        ->assertJsonPath('bundle.groups.0.label', 'Lens')
        ->assertJsonPath('bundle.configurable', true);

    $group = BundleGroup::query()->where('bundle_id', $bundle->id)->firstOrFail();

    $this->putJson(route('panel.bundles.variants.components', $this->variant), ['components' => [
        ['variant_id' => $body->id, 'quantity' => 1],
        ['variant_id' => $lensA->id, 'quantity' => 1, 'group_id' => $group->id, 'default' => true],
        ['variant_id' => $lensB->id, 'quantity' => 1, 'group_id' => $group->id],
    ]])
        ->assertOk()
        ->assertJsonCount(3, 'bundle.components')
        ->assertJsonPath('bundle.components.1.group_id', $group->id)
        ->assertJsonPath('bundle.components.1.default', true)
        // Body x10, best-stocked lens x5: five complete bundles.
        ->assertJsonPath('bundle.availability.available', 5);

    // Rename and widen the group in place, keyed by id.
    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => [
        ['id' => $group->id, 'name' => ['en' => 'Lenses'], 'min_selections' => 1, 'max_selections' => 2],
    ]])
        ->assertOk()
        ->assertJsonPath('bundle.groups.0.id', $group->id)
        ->assertJsonPath('bundle.groups.0.label', 'Lenses')
        ->assertJsonPath('bundle.groups.0.max_selections', 2);

    // Dropping the group drops its options; the fixed component stays.
    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => []])
        ->assertOk()
        ->assertJsonCount(0, 'bundle.groups')
        ->assertJsonCount(1, 'bundle.components')
        ->assertJsonPath('bundle.configurable', false);
});

it('rejects group limits the bundle cannot satisfy', function () {
    actingAsAdmin();

    Bundle::factory()->create(['product_variant_id' => $this->variant->id]);

    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => [
        ['name' => ['en' => 'Lens'], 'min_selections' => 3, 'max_selections' => 1],
    ]])
        ->assertUnprocessable()
        ->assertJsonPath('errors.groups.0', __('bundles::bundles.definition.group_selections', ['group' => 'Lens']));

    $this->putJson(route('panel.bundles.variants.groups', $this->variant), ['groups' => [
        ['name' => [], 'min_selections' => -1, 'max_selections' => 0],
    ]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['groups.0.name', 'groups.0.min_selections', 'groups.0.max_selections']);
});

it('searches variants by product name and sku, excluding bundle variants and the bundle itself', function () {
    actingAsAdmin();

    $camera = Product::factory()->create(['name' => collect(['en' => 'Camera body'])]);
    $body = ProductVariant::factory()->create(['product_id' => $camera->id, 'sku' => 'BODY-1']);
    $bag = ProductVariant::factory()->create(['sku' => 'BAG-9']);
    $other = Bundle::factory()->create(['product_variant_id' => ProductVariant::factory()->create(['sku' => 'OTHER-KIT'])->id]);

    $this->getJson(route('panel.bundles.search-variants', ['q' => 'camera']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $body->id)
        ->assertJsonPath('data.0.kind', 'variants')
        ->assertJsonPath('data.0.label', 'Camera body')
        ->assertJsonPath('data.0.hint', 'BODY-1')
        ->assertJsonPath('data.0.is_bundle', false);

    $this->getJson(route('panel.bundles.search-variants', ['q' => 'BAG']))
        ->assertOk()
        ->assertJsonPath('data.0.id', $bag->id);

    $ids = $this->getJson(route('panel.bundles.search-variants', ['q' => '', 'exclude' => $this->variant->id, 'bucket' => 'components', 'kinds' => ['variants']]))
        ->assertOk()
        ->json('data.*.id');

    expect($ids)->not->toContain($other->product_variant_id)
        ->not->toContain($this->variant->id)
        ->toContain($body->id, $bag->id);
});

it('summarises every variant of a product for the product page card', function () {
    actingAsAdmin();

    $product = $this->variant->product;
    $sibling = ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'KIT-2']);
    Bundle::factory()->create(['product_variant_id' => $sibling->id]);

    $this->getJson(route('panel.bundles.products.show', $product))
        ->assertOk()
        ->assertJsonCount(2, 'variants')
        ->assertJsonPath('variants.0.variant.id', $this->variant->id)
        ->assertJsonPath('variants.0.defined', false)
        ->assertJsonPath('variants.1.variant.id', $sibling->id)
        ->assertJsonPath('variants.1.defined', true)
        ->assertJsonPath('variants.1.variant.edit_url', route('panel.products.variants.edit', [$product, $sibling]));
});

it('lists the bundles a product is included in', function () {
    actingAsAdmin();

    $part = ProductVariant::factory()->create(['sku' => 'PART']);
    $bundle = Bundle::factory()->create(['product_variant_id' => $this->variant->id]);
    BundleComponent::factory()->create(['bundle_id' => $bundle->id, 'product_variant_id' => $part->id, 'quantity' => 3]);

    $this->getJson(route('panel.bundles.products.included-in', $part->product))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $bundle->id)
        ->assertJsonPath('data.0.variant.sku', 'KIT-1')
        ->assertJsonPath('data.0.product_url', route('panel.products.edit', $this->variant->product_id))
        ->assertJsonPath('data.0.components.0.quantity', 3)
        ->assertJsonPath('data.0.components.0.variant.sku', 'PART');

    $this->getJson(route('panel.bundles.products.included-in', $this->variant->product))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('keeps the bundle pricing enum in step with the panel select', function () {
    expect(array_map(fn (BundlePricing $case) => $case->value, BundlePricing::cases()))->toBe(['fixed', 'components']);
});
