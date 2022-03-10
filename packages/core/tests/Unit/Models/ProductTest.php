<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Channel;
use GetCandy\Models\Collection;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductAssociation;
use GetCandy\Models\ProductType;
use GetCandy\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * @group associations
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product()
    {
        $attribute_data = collect([
            'meta_title'  => new \GetCandy\FieldTypes\Text('I like cake'),
            'pack_qty'    => new \GetCandy\FieldTypes\Number(12345),
            'description' => new \GetCandy\FieldTypes\TranslatedText(collect([
                'en' => new \GetCandy\FieldTypes\Text('Blue'),
                'fr' => new \GetCandy\FieldTypes\Text('Bleu'),
            ])),
        ]);

        $product = Product::factory()
            ->for(ProductType::factory())
            ->create([
                'attribute_data' => $attribute_data,
            ]);

        $this->assertEquals($attribute_data, $product->attribute_data);
    }

    /**
     * @test
     * */
    public function takes_scout_prefix_into_account()
    {
        $expected = config('scout.prefix').'_products';

        $this->assertEquals($expected, (new Product)->searchableAs());
    }

    /** @test */
    public function has_image_transformations_loaded_from_config()
    {
        $collection = Product::factory()->create();
        $collection->registerAllMediaConversions();

        $conversions = $collection->mediaConversions;

        $this->assertIsArray($conversions);

        $transforms = config('getcandy.media.transformations');

        $this->assertCount(count($transforms), $conversions);
    }

    /** @test */
    public function new_product_has_channel_associations()
    {
        Channel::factory(4)->create();

        $product = Product::factory()->create();

        $this->assertNotEmpty($product->channels);

        // Make sure nothing is enabled by default
        $this->assertEmpty($product->channels->filter(fn ($channel) => $channel->enabled || $channel->published_at));
    }

    /**
     * @test
     * @group products
     * */
    public function product_can_be_scheduled()
    {
        $channel = Channel::factory()->create();

        $product = Product::factory()->create();

        $publishDate = now()->addDays(1);

        $product->scheduleChannel($channel, $publishDate);

        $this->assertDatabaseHas(
            'getcandy_channelables',
            [
                'channel_id'       => $channel->id,
                'channelable_type' => Product::class,
                'channelable_id'   => $product->id,
                'enabled'          => '1',
                'starts_at'        => $publishDate->toDateTimeString(),
            ],
        );

        $this->assertCount(1, DB::table('getcandy_channelables')->get());
    }

    /** @test */
    public function customer_groups_can_be_enabled()
    {
        $product = Product::factory()->create();

        $this->assertCount(0, $product->customerGroups);

        $customerGroup = CustomerGroup::factory()->create();

        $product->scheduleCustomerGroup($customerGroup);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'purchasable'       => '1',
                'ends_at'           => null,
            ],
        );
    }

    /** @test */
    public function customer_groups_can_be_scheduled_with_start_and_end()
    {
        $product = Product::factory()->create();

        $customerGroup = CustomerGroup::factory()->create();

        $start = now();
        $end = now();

        $product->scheduleCustomerGroup($customerGroup, $start);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'purchasable'       => 1,
                'starts_at'         => $start,
                'ends_at'           => null,
            ],
        );

        $product = Product::factory()->create();

        $product->scheduleCustomerGroup($customerGroup, $start, $end);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'purchasable'       => 1,
                'starts_at'         => $start,
                'ends_at'           => $end,
            ],
        );
    }

    /** @test */
    public function customer_groups_can_be_scheduled_with_pivot_data()
    {
        $product = Product::factory()->create();

        $customerGroup = CustomerGroup::factory()->create();

        $start = now();
        $end = now();

        $product->scheduleCustomerGroup($customerGroup, null, null, [
            'visible'     => 0,
            'purchasable' => 0,
        ]);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'purchasable'       => 0,
                'visible'           => 0,
                'ends_at'           => null,
            ],
        );

        $product = Product::factory()->create();

        $product->scheduleCustomerGroup($customerGroup, $start, $end);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'purchasable'       => 1,
                'starts_at'         => $start,
                'ends_at'           => $end,
            ],
        );
    }

    /**
     * @test
     * @group mosh
     * */
    public function customer_groups_can_be_unscheduled()
    {
        $product = Product::factory()->create();

        $customerGroup = CustomerGroup::factory()->create();

        $start = now();
        $end = now();

        $product->scheduleCustomerGroup($customerGroup, $start, $end);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 1,
                'visible'           => 1,
                'purchasable'       => 1,
                'ends_at'           => $end,
                'starts_at'         => $start,
            ],
        );

        $product->unscheduleCustomerGroup($customerGroup, [
            'visible'     => 0,
            'purchasable' => 0,
        ]);

        $this->assertDatabaseHas(
            'getcandy_customer_group_product',
            [
                'customer_group_id' => $customerGroup->id,
                'enabled'           => 0,
                'starts_at'         => null,
                'ends_at'           => null,
                'purchasable'       => 0,
                'visible'           => 0,
            ],
        );
    }

    /** @test */
    public function product_can_sync_tags()
    {
        $channel = Channel::factory()->create();

        $product = Product::factory()->create();

        $this->assertCount(0, $product->tags);

        $tags = collect(['foo', 'bar', 'char']);

        $product->syncTags($tags);

        $this->assertCount(3, $product->load('tags')->tags);
    }

    /** @test */
    public function product_can_have_associations()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'cross-sell',
        ]);

        $this->assertCount(1, $parent->refresh()->associations);
    }

    /** @test */
    public function product_can_get_core_associations_with_helpers()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'cross-sell',
        ]);

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'up-sell',
        ]);

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'alternate',
        ]);

        $crossSell = $parent->associations()->crossSell()->get();

        $this->assertCount(1, $crossSell);
        $this->assertEquals(ProductAssociation::CROSS_SELL, $crossSell->first()->type);

        $upsell = $parent->associations()->upSell()->get();

        $this->assertCount(1, $upsell);
        $this->assertEquals(ProductAssociation::UP_SELL, $upsell->first()->type);

        $alternate = $parent->associations()->alternate()->get();

        $this->assertCount(1, $alternate);
        $this->assertEquals(ProductAssociation::ALTERNATE, $alternate->first()->type);
    }

    /** @test */
    public function product_can_get_all_associations()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory(5)->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'cross-sell',
        ]);

        $this->assertCount(5, $parent->refresh()->associations);
    }

    /** @test */
    public function product_can_have_custom_association_types()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'custom-type',
        ]);

        $assoc = $parent->associations()->type('custom-type')->get();

        $this->assertCount(1, $assoc);
        $this->assertEquals('custom-type', $assoc->first()->type);
    }

    /** @test */
    public function can_associate_products_via_relation()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        $parent->associations()->create([
            'product_parent_id' => $parent->id,
            'product_target_id' => $target->id,
            'type'              => 'custom-type',
        ]);

        $assoc = $parent->associations()->type('custom-type')->get();

        $this->assertCount(1, $assoc);
        $this->assertEquals('custom-type', $assoc->first()->type);
    }

    /** @test */
    public function can_associate_multiple_products()
    {
        $parent = Product::factory()->create();
        $targetA = Product::factory()->create();
        $targetB = Product::factory()->create();

        $parent->associate([$targetA, $targetB], ProductAssociation::UP_SELL);

        $assoc = $parent->associations()->type(ProductAssociation::UP_SELL)->get();

        $this->assertCount(2, $assoc);
    }

    /** @test */
    public function can_associate_products_via_helper()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        $parent->associate($target, 'custom-type');

        $assoc = $parent->associations()->type('custom-type')->get();

        $this->assertCount(1, $assoc);
        $this->assertEquals('custom-type', $assoc->first()->type);
    }

    /** @test */
    public function can_remove_all_associations()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'cross-sell',
        ]);

        $this->assertCount(1, $parent->refresh()->associations);

        $parent->dissociate($target);

        $this->assertCount(0, $parent->refresh()->associations);
    }

    /** @test */
    public function can_only_remove_associations_of_a_certain_type()
    {
        $parent = Product::factory()->create();
        $target = Product::factory()->create();

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'cross-sell',
        ]);

        ProductAssociation::factory()->create([
            'product_parent_id' => $parent,
            'product_target_id' => $target,
            'type'              => 'up-sell',
        ]);

        $this->assertCount(2, $parent->refresh()->associations);

        $parent->dissociate($target, 'cross-sell');

        $this->assertCount(1, $parent->refresh()->associations);
        $this->assertEquals('up-sell', $parent->refresh()->associations->first()->type);
    }

    /** @test */
    public function can_have_collections_relationship()
    {
        $collection = Collection::factory()->create();
        $product = Product::factory()->create();
        $product->collections()->sync($collection);

        $this->assertInstanceOf(EloquentCollection::class, $product->collections);
        $this->assertCount(1, $product->collections);
        $this->assertInstanceOf(Collection::class, $product->collections->first());
        $this->assertNotNull($product->collections->first()->pivot);
        $this->assertNotNull($product->collections->first()->pivot->position);
    }
}
