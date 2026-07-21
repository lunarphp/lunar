<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
});

it('renders the edit page with type, picker payloads and mapped id sets', function () {
    $productType = ProductType::factory()->create(['name' => 'Stationery']);

    $productAttribute = Attribute::factory()->modelType('product')->create();
    Attribute::factory()->modelType('product_variant')->create();

    $productType->attributeMapping()->sync([$productAttribute->id]);

    $this->get(route('panel.product-types.edit', $productType))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('product-types/Edit')
            ->where('productType.name', 'Stationery')
            ->hasAll(['productType.handle', 'productType.status', 'productType.description', 'productType.default_tax_class_id', 'productType.products_count'])
            ->has('productAttributeGroups', 1)
            ->has('variantAttributeGroups', 1)
            ->where('productAttributeIds', [$productAttribute->id])
            ->where('variantAttributeIds', [])
            ->has('taxClasses')
            ->has('languages')
            ->where('draft', null)
            ->hasAll(['urls.update', 'urls.destroy', 'urls.draft', 'urls.draftCommit', 'urls.mediaStore', 'urls.mediaReorder'])
        );
});

it('updates a product type through the update endpoint', function () {
    $productType = ProductType::factory()->create(['name' => 'Old Name']);
    $taxClass = TaxClass::factory()->create();

    $this->put(route('panel.product-types.update', $productType), [
        'name' => 'New Name',
        'handle' => 'new-name',
        'status' => 'draft',
        'description' => 'Everything for the desk.',
        'default_tax_class_id' => $taxClass->id,
    ])->assertRedirect()->assertSessionHas('success', 'Product type updated.');

    $productType->refresh();

    expect($productType->name)->toBe('New Name')
        ->and($productType->handle)->toBe('new-name')
        ->and($productType->status->getValue())->toBe('draft')
        ->and($productType->description)->toBe('Everything for the desk.')
        ->and($productType->default_tax_class_id)->toBe($taxClass->id);
});

it('syncs the attribute mapping from the two id sets', function () {
    $productType = ProductType::factory()->create();

    $productAttribute = Attribute::factory()->modelType('product')->create();
    $variantAttribute = Attribute::factory()->modelType('product_variant')->create();

    $this->put(route('panel.product-types.update', $productType), [
        'name' => $productType->name,
        'handle' => $productType->handle,
        'product_attribute_ids' => [$productAttribute->id],
        'variant_attribute_ids' => [$variantAttribute->id],
    ])->assertRedirect()->assertSessionDoesntHaveErrors();

    expect($productType->productAttributes()->get()->modelKeys())->toBe([$productAttribute->id])
        ->and($productType->variantAttributes()->get()->modelKeys())->toBe([$variantAttribute->id]);
});

it('keeps the untouched surface when only one id set is posted', function () {
    $productType = ProductType::factory()->create();

    $productAttribute = Attribute::factory()->modelType('product')->create();
    $variantAttribute = Attribute::factory()->modelType('product_variant')->create();
    $productType->attributeMapping()->sync([$productAttribute->id, $variantAttribute->id]);

    $this->put(route('panel.product-types.update', $productType), [
        'name' => $productType->name,
        'handle' => $productType->handle,
        'product_attribute_ids' => [],
    ])->assertRedirect();

    expect($productType->productAttributes()->get()->modelKeys())->toBe([])
        ->and($productType->variantAttributes()->get()->modelKeys())->toBe([$variantAttribute->id]);
});

it('rejects a product attribute id posted in the variant list', function () {
    $productType = ProductType::factory()->create();

    $productAttribute = Attribute::factory()->modelType('product')->create();

    $this->put(route('panel.product-types.update', $productType), [
        'name' => $productType->name,
        'handle' => $productType->handle,
        'variant_attribute_ids' => [$productAttribute->id],
    ])->assertSessionHasErrors('variant_attribute_ids.0');
});

it('rejects a handle already used by another product type', function () {
    ProductType::factory()->create(['handle' => 'taken']);
    $productType = ProductType::factory()->create(['handle' => 'mine']);

    $this->put(route('panel.product-types.update', $productType), [
        'name' => 'Renamed',
        'handle' => 'taken',
    ])->assertSessionHasErrors('handle');

    $this->put(route('panel.product-types.update', $productType), [
        'name' => 'Renamed',
        'handle' => 'mine',
    ])->assertSessionDoesntHaveErrors('handle');
});

it('deletes a product type without products', function () {
    $productType = ProductType::factory()->create();

    $this->delete(route('panel.product-types.destroy', $productType))
        ->assertRedirect(route('panel.product-types.index'))
        ->assertSessionHas('success', 'Product type deleted.');

    expect(ProductType::find($productType->id))->toBeNull();
});

it('refuses to delete a product type with products', function () {
    $product = Product::factory()->create();
    $productType = $product->productType;

    $this->from(route('panel.product-types.edit', $productType))
        ->delete(route('panel.product-types.destroy', $productType))
        ->assertRedirect(route('panel.product-types.edit', $productType))
        ->assertSessionHas('error');

    expect(ProductType::find($productType->id))->not->toBeNull();
});

it('sets the status on a selection of product types in bulk', function () {
    $productTypes = ProductType::factory()->active()->count(2)->create();
    $untouched = ProductType::factory()->active()->create();

    $this->post(route('panel.product-types.bulk-status', ['status' => 'draft']), [
        'ids' => $productTypes->pluck('id')->all(),
    ])->assertRedirect()->assertSessionHas('success');

    expect($productTypes->map(fn (ProductType $productType) => $productType->refresh()->status->getValue())->unique()->all())->toBe(['draft'])
        ->and($untouched->refresh()->status->getValue())->toBe('active');
});

it('rejects a bulk status outside the state list', function () {
    $productType = ProductType::factory()->create();

    $this->post(route('panel.product-types.bulk-status', ['status' => 'archived']), [
        'ids' => [$productType->id],
    ])->assertNotFound();
});

it('gates product type routes behind the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $productType = ProductType::factory()->create();

    $this->get(route('panel.product-types.index'))->assertForbidden();
    $this->get(route('panel.product-types.edit', $productType))->assertForbidden();
});
