<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Tag;
use Lunar\Core\Models\Url;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true, 'code' => 'en']);
});

function actingAsAdmin(): Staff
{
    $staff = Staff::factory()->create(['admin' => true]);
    test()->actingAs($staff, 'staff');

    return $staff;
}

it('redirects guests to the login screen', function () {
    $this->get(route('panel.products.index'))->assertRedirect(route('panel.login'));
});

it('gates product routes behind the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.products.index'))->assertForbidden();
    $this->get(route('panel.products.create'))->assertForbidden();
});

it('renders the products index with rows and KPIs', function () {
    actingAsAdmin();

    $product = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WIDGET-1', 'stock_available' => 4]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WIDGET-2', 'stock_available' => 3]);

    $this->get(route('panel.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/Index')
            ->has('products.data', 1)
            ->has('products.data.0', fn (Assert $row) => $row
                ->where('name', 'Widget')
                ->where('sku', 'WIDGET-1')
                ->where('extra_sku_count', 1)
                ->where('stock', 7)
                ->hasAll(['id', 'thumbnail', 'status', 'status_label', 'brand', 'product_type', 'tags', 'edit_url', '_actions'])
                ->etc()
            )
            ->has('kpis', fn (Assert $kpis) => $kpis
                ->where('total', 1)
                ->where('published', 1)
                ->where('draft', 0)
                ->where('outOfStock', 0)
            )
            ->has('columns')
            ->has('tableBulkActions', 2)
            ->has('brandOptions')
            ->has('typeOptions')
            ->has('urls.create')
        );
});

it('searches by name, variant sku and url slug', function () {
    actingAsAdmin();

    $widget = Product::factory()->create(['name' => collect(['en' => 'Widget'])]);
    ProductVariant::factory()->create(['product_id' => $widget->id, 'sku' => 'WID-1']);
    Url::factory()->create([
        'element_type' => $widget->getMorphClass(),
        'element_id' => $widget->id,
        'slug' => 'super-widget',
        'default' => true,
    ]);

    $gadget = Product::factory()->create(['name' => collect(['en' => 'Gadget'])]);
    ProductVariant::factory()->create(['product_id' => $gadget->id, 'sku' => 'GAD-1']);

    $this->get(route('panel.products.index', ['q' => 'Widget']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));

    $this->get(route('panel.products.index', ['q' => 'GAD-1']))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.name', 'Gadget')->has('products.data', 1));

    $this->get(route('panel.products.index', ['q' => 'super-widget']))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.name', 'Widget')->has('products.data', 1));
});

it('filters by status, brand, type, tag and stock state', function () {
    actingAsAdmin();

    $brand = Brand::factory()->create();
    $type = ProductType::factory()->create();

    $published = Product::factory()->create(['status' => 'published', 'brand_id' => $brand->id, 'product_type_id' => $type->id]);
    ProductVariant::factory()->create(['product_id' => $published->id, 'stock_available' => 5]);
    $published->tags()->attach(Tag::create(['value' => 'sale']));

    $draft = Product::factory()->create(['status' => 'draft']);
    ProductVariant::factory()->create(['product_id' => $draft->id, 'stock_available' => 0]);

    $this->get(route('panel.products.index', ['status' => 'draft']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $draft->id));

    $this->get(route('panel.products.index', ['brand' => $brand->id]))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $published->id));

    $this->get(route('panel.products.index', ['type' => $type->id]))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $published->id));

    $this->get(route('panel.products.index', ['tag' => 'SALE']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $published->id));

    $this->get(route('panel.products.index', ['stock_state' => 'out']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $draft->id));

    $this->get(route('panel.products.index', ['stock_state' => 'in']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1)->where('products.data.0.id', $published->id));
});

it('sorts by the allow-listed columns and falls back on unknown sorts', function () {
    actingAsAdmin();

    $low = Product::factory()->create(['name' => collect(['en' => 'Alpha'])]);
    ProductVariant::factory()->create(['product_id' => $low->id, 'stock_available' => 1]);

    $high = Product::factory()->create(['name' => collect(['en' => 'Zulu'])]);
    ProductVariant::factory()->create(['product_id' => $high->id, 'stock_available' => 9]);

    $this->get(route('panel.products.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.id', $low->id));

    $this->get(route('panel.products.index', ['sort' => 'stock', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.id', $high->id));

    $this->get(route('panel.products.index', ['sort' => 'evil_column']))->assertOk();
});

it('paginates fifteen per page', function () {
    actingAsAdmin();

    Product::factory()->count(16)->create();

    $this->get(route('panel.products.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 15)
            ->where('products.total', 16)
        );
});

it('omits the delete action for products with order history', function () {
    actingAsAdmin();

    $ordered = Product::factory()->create(['name' => collect(['en' => 'Alpha'])]);
    $variant = ProductVariant::factory()->create(['product_id' => $ordered->id]);
    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    Product::factory()->create(['name' => collect(['en' => 'Zulu'])]);

    $this->get(route('panel.products.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.id', $ordered->id)
            ->missing('products.data.0._actions.delete')
            ->has('products.data.1._actions.delete')
        );
});

it('sets bulk status on a selection', function () {
    actingAsAdmin();

    $products = Product::factory()->count(2)->create(['status' => 'draft']);
    $untouched = Product::factory()->create(['status' => 'draft']);

    $this->post(route('panel.products.bulk-status', ['status' => 'published']), [
        'ids' => $products->pluck('id')->all(),
    ])->assertRedirect();

    $products->each(fn (Product $product) => expect((string) $product->fresh()->status)->toBe('published'));
    expect((string) $untouched->fresh()->status)->toBe('draft');
});

it('rejects bulk status outside the allowed states', function () {
    actingAsAdmin();

    $product = Product::factory()->create(['status' => 'draft']);

    $this->post(route('panel.products.bulk-status', ['status' => 'archived']), [
        'ids' => [$product->id],
    ])->assertNotFound();
});
