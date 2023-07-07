<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\TestCase;

/**
 * @group shipping-manifest
 */
class RemoveSearchableAttributesTest extends TestCase
{
    use RefreshDatabase;

    private ?Collection $collection;
    private ?Product $product;
    private ?ProductOption $product_option;
    private ?Order $order;
    private ?Customer $customer;
    private ?Brand $brand;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create();

        $this->collection = Collection::factory()->create();

        $this->product_option = ProductOption::factory()->create();

        $this->order = Order::factory()->create();

        $this->customer = Customer::factory()->create();

        $this->brand = Brand::factory()->create();


        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxClass->taxRateAmounts()->create(
            TaxRateAmount::factory()->make([
                'percentage' => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        $this->product = $purchasable->product;
    }

    /** @test */
    public function can_exclude_searchable_attributes()
    {
        $this->assertNotEmpty(Config::get('lunar.search.exclude_model_attributes'));

        $modelTypes = ['collection', 'product', 'product_option', 'order', 'customer', 'brand'];

        foreach($modelTypes as $type) {

            $model = $this->$type;

            $this->assertNotEmpty( $model );

            //get first array key from searchable array
            $key = key( $model->getSearchableAttributes() );

            //set that key to be excluded
            Config::set('lunar.search.exclude_model_attributes.'.$type, [$key]);

            $attributes = $model->getSearchableAttributes();

            $this->assertFalse( isset($attributes[$key]) );
        }
    }
}
