<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Components\Products;

use GetCandy\FieldTypes\Text;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductShow;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Attribute;
use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductAssociation;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductOptionValue;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

/**
 * @group hub.products
 */
class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code'    => 'en',
        ]);

        Language::factory()->create([
            'default' => false,
            'code'    => 'fr',
        ]);

        Currency::factory()->create([
            'default'        => true,
            'decimal_places' => 2,
        ]);
    }

    /** @test  */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('images', [])
            ->assertSet('options', collect())
            ->assertSet('variantsEnabled', false);
    }

    /** @test */
    public function correct_product_is_loaded()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('product.id', $product->id);
    }

    /** @test */
    public function can_set_product_properties()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('product.brand', 'BAR')
            ->assertSet('product.status', 'published')
            ->set('product.status', 'draft')
            ->set('product.brand', 'FOOBRAND')
            ->assertSet('product.brand', 'FOOBRAND')
            ->assertSet('product.status', 'draft');
    }

    /** @test */
    public function can_set_singular_variant_identifiers()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'price'          => 1.99,
                'tier'           => 1,
            ]);
        }

        $productB = Product::factory()->has(ProductVariant::factory(), 'variants')->create();
        $variantB = $productB->variants->first();

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('variant.sku', $variant->sku)
            ->assertSet('variant.ean', $variant->ean)
            ->assertSet('variant.gtin', $variant->gtin)
            ->assertSet('variant.mpn', $variant->mpn)
            ->assertSet('vartian.tax_ref', $variant->tax_ref)
            ->set('variant.sku', 'FOOBAR')
            ->set('variant.ean', 'NEWEAN')
            ->set('variant.gtin', 'NEWGTIN')
            ->set('variant.mpn', 'NEWMPN')
            ->set('variant.tax_ref', 'CUSTOMTAXREF')
            ->assertSet('variant.ean', 'NEWEAN')
            ->assertSet('variant.gtin', 'NEWGTIN')
            ->assertSet('variant.mpn', 'NEWMPN')
            ->assertSet('variant.sku', 'FOOBAR')
            ->assertSet('variant.tax_ref', 'CUSTOMTAXREF')
            ->call('save')
            ->assertHasNoErrors([
                'variant.sku',
                'variant.ean',
                'variant.mpn',
                'variant.gtin',
            ]);
    }

    /** @test */
    public function can_set_singular_variant_identifiers_to_null()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'price'          => 1.99,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('variant.sku', $variant->sku)
            ->set('variant.sku', null)
            ->set('variant.ean', null)
            ->set('variant.mpn', null)
            ->set('variant.gtin', null)
            ->call('save')
            ->assertHasNoErrors([
                'variant.ean',
                'variant.mpn',
                'variant.gtin',
            ]);
    }

    /** @test */
    public function can_set_product_attribute_data()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        // Need some attributes...
        $name = Attribute::factory()->create([
            'handle' => 'name',
        ]);
        $description = Attribute::factory()->create([
            'handle' => 'description',
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $product->productType->mappedAttributes()->attach(Attribute::get());

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product->refresh(),
            ])->set('attributeMapping.'.'a_'.$name->id.'.data', 'nouseforaname')
            ->set('attributeMapping.'.'a_'.$description->id.'.data', 'nouseforadescription')
            ->call('addUrl')
            ->set('urls.0.slug', 'foo-bar');

        $component->call('save')->assertHasNoErrors();

        $newData = $product->refresh()->attribute_data;

        $name = $newData['name'];
        $description = $newData['description'];

        $this->assertInstanceOf(Text::class, $name);
        $this->assertInstanceOf(Text::class, $description);

        $this->assertEquals('nouseforaname', $name->getValue());
        $this->assertEquals('nouseforadescription', $description->getValue());
    }

    /** @test */
    public function variants_are_disabled_on_mount_if_product_only_has_one()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('variantsEnabled', false);
    }

    /** @test */
    public function variants_are_enabled_on_mount_if_product_has_more_than_one()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $currency = Currency::getDefault();

        ProductVariant::factory()
            ->count(2)
            ->for($product)
            ->create();

        foreach ($product->variants as $variant) {
            Price::factory()->create([
                'currency_id'    => $currency->id,
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('variantsEnabled', true);
    }

    /** @test */
    public function product_options_can_be_set_via_an_event()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $options = ProductOption::factory(4)->create();

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertCount('options', 0)
            ->emit('useProductOptions', $options->pluck('id'))
            ->assertCount('options', $options->count());
    }

    /** @test */
    public function variants_are_generated_from_two_options()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        ProductOption::factory(2)->create()->each(function ($option) {
            $option->values()->createMany(
                ProductOptionValue::factory(2)->make()->toArray()
            );
        });

        $values = ProductOptionValue::get();

        Config::set('getcandy-hub.products.sku.unique', true);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->set('optionValues', $values->pluck('id')->toArray())
            ->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(4, $product->variants()->count());
    }

    /** @test */
    public function product_options_can_be_set_manually()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $options = ProductOption::factory(4)->create();

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertCount('options', 0)
            ->set('options', $options->pluck('id'))
            ->assertCount('options', $options->count());
    }

    /** @test */
    public function product_option_can_be_removed()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $options = ProductOption::factory(4)->create();

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertCount('options', 0)
            ->set('options', $options)
            ->assertCount('options', $options->count())
            ->call('removeOption', 0)
            ->assertCount('options', $options->count() - 1);
    }

    /** @test */
    public function can_set_product_collections()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $collection = Collection::factory()->create();

        $this->assertDatabaseMissing((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $product->productType->mappedAttributes()->attach(Attribute::get());

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product->refresh(),
            ])->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->assertCount('collections', 0)
            ->set('collections', collect([[
                'id' => $collection->id,
                'name' => $collection->translateAttribute('name'),
                'group_id' => $collection->collection_group_id,
                'group_name' => $collection->group->name,
                'thumbnail' => null,
                'breadcrumb' => ['Foo', 'Bar'],
                'position' => 1,
            ]]))->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas((new Product)->collections()->getTable(), [
            'collection_id' => $collection->id,
            'product_id' => $component->get('product.id'),
        ]);
    }

    /** @test */
    public function can_set_product_associations()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        // Need some attributes...
        $name = Attribute::factory()->create([
            'handle' => 'name',
        ]);
        $description = Attribute::factory()->create([
            'handle' => 'description',
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $productB = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAZ',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $product->productType->mappedAttributes()->attach(Attribute::get());

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product->refresh(),
            ])->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->assertCount('associations', 0)
            ->set('associations', collect([
                [
                    'inverse' => false,
                    'target_id' => $productB->id,
                    'thumbnail' => optional($productB->thumbnail)->getUrl('small'),
                    'name' => $productB->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
            ]))->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_parent_id' => $product->id,
            'product_target_id' => $productB->id,
            'type' => 'cross-sell',
        ]);
    }

    /** @test */
    public function can_set_inverse_associations()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        // Need some attributes...
        $name = Attribute::factory()->create([
            'handle' => 'name',
        ]);
        $description = Attribute::factory()->create([
            'handle' => 'description',
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $productB = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAZ',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        $product->productType->mappedAttributes()->attach(Attribute::get());

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product->refresh(),
            ])->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->assertCount('associations', 0)
            ->set('associations', collect([
                [
                    'inverse' => true,
                    'target_id' => $productB->id,
                    'thumbnail' => optional($productB->thumbnail)->getUrl('small'),
                    'name' => $productB->translateAttribute('name'),
                    'type' => 'cross-sell',
                ],
            ]))->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas((new ProductAssociation)->getTable(), [
            'product_parent_id' => $productB->id,
            'product_target_id' => $product->id,
            'type' => 'cross-sell',
        ]);
    }

    /**
     * @test
     * */
    public function variants_are_enabled_by_default()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSeeHtml('Variants');
    }

    /**
     * @test
     * */
    public function variants_can_be_disabled()
    {
        Config::set('getcandy-hub.products.disable_variants', true);

        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertDontSeeHtml('Variants');
    }

    /**
     * @test
     * */
    public function variants_arent_generated_when_disabled()
    {
        Config::set('getcandy-hub.products.disable_variants', true);

        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'brand'  => 'BAR',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id'   => $variant->id,
                'currency_id'    => $currency->id,
                'tier'           => 1,
            ]);
        }

        ProductOption::factory(2)->create()->each(function ($option) {
            $option->values()->createMany(
                ProductOptionValue::factory(2)->make()->toArray()
            );
        });

        $values = ProductOptionValue::get();

        Config::set('getcandy-hub.products.sku.unique', true);

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->set('optionValues', $values->pluck('id')->toArray())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(1, $product->variants()->count());
    }
}
