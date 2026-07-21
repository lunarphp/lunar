<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('renders the create form with active types only', function () {
    ProductType::factory()->active()->create(['name' => 'Stationery']);
    ProductType::factory()->draft()->create(['name' => 'Hidden']);

    $this->get(route('panel.products.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Create')
            ->has('typeOptions', 1)
            ->where('typeOptions.0.label', 'Stationery')
            ->has('urls.store')
        );
});

it('creates a product with its initial variant and redirects to edit', function () {
    $taxClass = TaxClass::factory()->create();
    $type = ProductType::factory()->active()->create(['default_tax_class_id' => $taxClass->id]);

    $response = $this->post(route('panel.products.store'), [
        'name' => 'Coffee Grinder',
        'product_type_id' => $type->id,
        'status' => 'draft',
    ]);

    $product = Product::sole();

    $response->assertRedirect(route('panel.products.edit', $product))
        ->assertSessionHas('success');

    expect($product->translate('name'))->toBe('Coffee Grinder')
        ->and((string) $product->status)->toBe('draft')
        ->and($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->tax_class_id)->toBe($taxClass->id);
});

it('rejects draft product types in the create flow', function () {
    $type = ProductType::factory()->draft()->create();

    $this->post(route('panel.products.store'), [
        'name' => 'Coffee Grinder',
        'product_type_id' => $type->id,
    ])->assertSessionHasErrors('product_type_id');
});

it('validates the payload', function () {
    $type = ProductType::factory()->active()->create();

    $this->post(route('panel.products.store'), ['name' => '', 'product_type_id' => $type->id])
        ->assertSessionHasErrors('name');

    $this->post(route('panel.products.store'), ['name' => 'Ok', 'product_type_id' => $type->id, 'status' => 'archived'])
        ->assertSessionHasErrors('status');
});
