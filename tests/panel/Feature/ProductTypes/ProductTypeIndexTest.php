<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('redirects guests to the login screen', function () {
    $this->get(route('panel.product-types.index'))->assertRedirect(route('panel.login'));
});

it('renders the product types index with rows', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    ProductType::factory()->count(3)->create();

    $this->get(route('panel.product-types.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('product-types/Index')
            ->has('productTypes.data', 3)
            ->has('productTypes.data.0', fn (Assert $row) => $row
                ->hasAll(['id', 'name', 'handle', 'description', 'product_attributes_count', 'variant_attributes_count', 'products_count', 'status', 'status_label', 'edit_url', '_actions'])
                ->etc()
            )
            ->has('columns')
            ->has('tableBulkActions', 2)
            ->has('urls.create')
        );
});

it('counts product and variant attributes per row', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $productType = ProductType::factory()->create();
    $productAttributes = Attribute::factory()->modelType('product')->count(2)->create();
    $variantAttribute = Attribute::factory()->modelType('product_variant')->create();

    $productType->attributeMapping()->sync([...$productAttributes->pluck('id'), $variantAttribute->id]);

    $this->get(route('panel.product-types.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('productTypes.data.0.product_attributes_count', 2)
            ->where('productTypes.data.0.variant_attributes_count', 1)
        );
});

it('omits the delete action for types with products', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);

    $product = Product::factory()->create();
    ProductType::factory()->create(['name' => 'Empty type']);

    $this->get(route('panel.product-types.index', ['sort' => 'products_count', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('productTypes.data.0.id', $product->product_type_id)
            ->missing('productTypes.data.0._actions.delete')
            ->has('productTypes.data.1._actions.delete')
        );
});

it('searches by name and handle', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    ProductType::factory()->create(['name' => 'Stationery', 'handle' => 'stationery']);
    ProductType::factory()->create(['name' => 'Apparel', 'handle' => 'apparel']);

    $this->get(route('panel.product-types.index', ['q' => 'Stationery']))
        ->assertInertia(fn (Assert $page) => $page->has('productTypes.data', 1));

    $this->get(route('panel.product-types.index', ['q' => 'apparel']))
        ->assertInertia(fn (Assert $page) => $page->has('productTypes.data', 1));
});

it('filters by status', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    ProductType::factory()->active()->count(2)->create();
    ProductType::factory()->draft()->create();

    $this->get(route('panel.product-types.index', ['status' => 'draft']))
        ->assertInertia(fn (Assert $page) => $page->has('productTypes.data', 1));

    $this->get(route('panel.product-types.index', ['status' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page->has('productTypes.data', 3));
});

it('sorts by the allow-listed columns and falls back on unknown sorts', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $alpha = ProductType::factory()->create(['name' => 'Alpha']);
    ProductType::factory()->create(['name' => 'Zulu']);

    $this->get(route('panel.product-types.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page->where('productTypes.data.0.id', $alpha->id));

    $this->get(route('panel.product-types.index', ['sort' => 'evil_column']))
        ->assertOk();
});

it('paginates fifteen per page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    ProductType::factory()->count(16)->create();

    $this->get(route('panel.product-types.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('productTypes.data', 15)
            ->where('productTypes.total', 16)
        );
});
