<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id, 'sku' => 'WID-1']);
});

it('renders the edit page with product, availability and option payloads', function () {
    CustomerGroup::factory()->create();

    $this->get(route('panel.products.edit', $this->product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Edit')
            ->where('product.display_name', 'Widget')
            ->where('product.sku', 'WID-1')
            ->where('product.variants_count', 1)
            ->where('product.has_order_history', false)
            ->has('availability.channels')
            ->has('availability.customer_groups')
            ->has('brandOptions')
            ->has('typeOptions')
            ->has('associationTypes')
            ->has('associationProducts')
            ->has('urls.draft')
        );
});

it('serves the simple shape with the sole variant payload', function () {
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shape', 'simple')
            ->where('variant.id', $this->variant->id)
            ->where('formSliceValues.variant:sku', 'WID-1')
            ->has('variant.stock.levels')
            ->has('currencies')
            ->has('taxClasses')
            ->has('measurements.length')
        );
});

it('serves the multi shape without a sole variant payload', function () {
    ProductVariant::factory()->create(['product_id' => $this->product->id]);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shape', 'multi')
            ->where('variant', null)
        );
});

it('keeps a drafted product type selectable on its own products', function () {
    $this->product->productType->update(['status' => 'draft']);

    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('typeOptions.0.value', $this->product->product_type_id)
        );
});

it('rejects a draft product type on commit', function () {
    $draftType = ProductType::factory()->draft()->create();

    $this->postJson(route('panel.products.draft.commit', $this->product), [
        'data' => ['product_type_id' => $draftType->id],
        'rebase' => [],
    ])->assertJsonValidationErrors('product_type_id');
});

it('deletes a product without order history', function () {
    $this->delete(route('panel.products.destroy', $this->product))
        ->assertRedirect(route('panel.products.index'));

    $this->assertDatabaseMissing('lunar_products', ['id' => $this->product->id]);
});

it('refuses to delete a product with order history', function () {
    OrderLine::factory()->create([
        'purchasable_type' => $this->variant->getMorphClass(),
        'purchasable_id' => $this->variant->id,
    ]);

    $this->delete(route('panel.products.destroy', $this->product))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('lunar_products', ['id' => $this->product->id]);
});

it('duplicates the product through the page action endpoint', function () {
    $this->post(route('panel.products.duplicate', $this->product))
        ->assertRedirect();

    expect(Product::count())->toBe(2);

    $duplicate = Product::query()->whereKeyNot($this->product->id)->sole();

    expect($duplicate->translate('name'))->toContain('Widget')
        ->and((string) $duplicate->status)->toBe('draft')
        ->and($duplicate->variants()->count())->toBe(1);
});

it('serves the duplicate page action on the edit page', function () {
    $this->get(route('panel.products.edit', $this->product))
        ->assertInertia(function (Assert $page) {
            $actions = collect($page->toArray()['props']['pageActions']);

            expect($actions->firstWhere('key', 'duplicate'))->not->toBeNull()
                ->and($actions->firstWhere('key', 'duplicate')['url'])
                ->toBe(route('panel.products.duplicate', $this->product));
        });
});

it('gates the edit surface behind the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.products.edit', $this->product))->assertForbidden();
    $this->delete(route('panel.products.destroy', $this->product))->assertForbidden();
    $this->post(route('panel.products.duplicate', $this->product))->assertForbidden();
});
