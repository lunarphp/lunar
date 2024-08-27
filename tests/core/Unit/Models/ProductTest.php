<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Facades\DB;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a product', function () {
    $attribute_data = collect([
        'meta_title' => new \Lunar\FieldTypes\Text('I like cake'),
        'pack_qty' => new \Lunar\FieldTypes\Number(12345),
        'description' => new \Lunar\FieldTypes\TranslatedText(collect([
            'en' => new \Lunar\FieldTypes\Text('Blue'),
            'fr' => new \Lunar\FieldTypes\Text('Bleu'),
        ])),
    ]);

    $product = Product::factory()
        ->for(ProductType::factory())
        ->create([
            'attribute_data' => $attribute_data,
        ]);

    expect($product->attribute_data)->toEqual($attribute_data);
});

test('can fetch product options', function () {
    $attribute_data = collect([
        'meta_title' => new \Lunar\FieldTypes\Text('I like cake'),
        'pack_qty' => new \Lunar\FieldTypes\Number(12345),
        'description' => new \Lunar\FieldTypes\TranslatedText(collect([
            'en' => new \Lunar\FieldTypes\Text('Blue'),
            'fr' => new \Lunar\FieldTypes\Text('Bleu'),
        ])),
    ]);

    $product = Product::factory()
        ->for(ProductType::factory())
        ->create([
            'attribute_data' => $attribute_data,
        ]);

    $productOptions = \Lunar\Models\ProductOption::factory(2)->create();

    foreach ($productOptions as $index => $productOption) {
        $product->productOptions()->attach($productOption, ['position' => $index + 1]);
    }

    expect($product->refresh()->productOptions)->toHaveCount(2);

})->group('momo');

test('can fetch using status scope', function () {
    $attribute_data = collect([
        'meta_title' => new \Lunar\FieldTypes\Text('I like cake'),
        'pack_qty' => new \Lunar\FieldTypes\Number(12345),
        'description' => new \Lunar\FieldTypes\TranslatedText(collect([
            'en' => new \Lunar\FieldTypes\Text('Blue'),
            'fr' => new \Lunar\FieldTypes\Text('Bleu'),
        ])),
    ]);

    Product::factory()
        ->for(ProductType::factory())
        ->create([
            'attribute_data' => $attribute_data,
            'status' => 'draft',
        ]);

    expect(Product::status('published')->get())->toHaveCount(0);

    expect(Product::status('draft')->get())->toHaveCount(1);
});

test('takes scout prefix into account', function () {
    $expected = config('scout.prefix').'products';

    expect((new Product)->searchableAs())->toEqual($expected);
});

test('new product has channel associations', function () {
    Channel::factory(4)->create();

    $product = Product::factory()->create();

    expect($product->channels)->not->toBeEmpty();

    // Make sure nothing is enabled by default
    expect($product->channels->filter(fn ($channel) => $channel->enabled || $channel->published_at))->toBeEmpty();
});

test('product can be scheduled', function () {
    $channel = Channel::factory()->create();

    $brand = Brand::factory()->create();

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    $publishDate = now()->addDays(1);

    $product->scheduleChannel($channel, $publishDate);

    $this->assertDatabaseHas(
        'lunar_channelables',
        [
            'channel_id' => $channel->id,
            'channelable_type' => Product::class,
            'channelable_id' => $product->id,
            'enabled' => '1',
            'starts_at' => $publishDate->toDateTimeString(),
        ],
    );

    expect(DB::table('lunar_channelables')->get())->toHaveCount(1);
})->group('products');

test('customer groups can be enabled', function () {
    $product = Product::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $product->scheduleCustomerGroup($customerGroup);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => '1',
            'ends_at' => null,
        ],
    );
});

test('customer groups can be scheduled always available', function () {
    $product = Product::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $product->scheduleCustomerGroup($customerGroup);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => 1,
            'visible' => 1,
            'starts_at' => null,
            'ends_at' => null,
        ],
    );
});

test('customer groups can be scheduled with start and end', function () {
    $product = Product::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $start = now();
    $end = now();

    $product->scheduleCustomerGroup($customerGroup, $start);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => 1,
            'starts_at' => $start,
            'ends_at' => null,
        ],
    );

    $product = Product::factory()->create();

    $product->scheduleCustomerGroup($customerGroup, $start, $end);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => 1,
            'starts_at' => $start,
            'ends_at' => $end,
        ],
    );
});

test('customer groups can be scheduled with pivot data', function () {
    $product = Product::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $start = now();
    $end = now();

    $product->scheduleCustomerGroup($customerGroup, null, null, [
        'visible' => 0,
        'purchasable' => 0,
    ]);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => 0,
            'visible' => 0,
            'ends_at' => null,
        ],
    );

    $product = Product::factory()->create();

    $product->scheduleCustomerGroup($customerGroup, $start, $end);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'purchasable' => 1,
            'starts_at' => $start,
            'ends_at' => $end,
        ],
    );
});

test('customer groups can be unscheduled', function () {
    $product = Product::factory()->create();

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $start = now();
    $end = now();

    $product->scheduleCustomerGroup($customerGroup, $start, $end);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 1,
            'visible' => 1,
            'purchasable' => 1,
            'ends_at' => $end,
            'starts_at' => $start,
        ],
    );

    $product->unscheduleCustomerGroup($customerGroup, [
        'visible' => 0,
        'purchasable' => 0,
    ]);

    $this->assertDatabaseHas(
        'lunar_customer_group_product',
        [
            'customer_group_id' => $customerGroup->id,
            'enabled' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'purchasable' => 0,
            'visible' => 0,
        ],
    );
})->group('mosh');

test('product can sync tags', function () {
    $channel = Channel::factory()->create();

    $product = Product::factory()->create();

    expect($product->tags)->toHaveCount(0);

    $tags = collect(['foo', 'bar', 'char']);

    $product->syncTags($tags);

    expect($product->load('tags')->tags)->toHaveCount(3);
});

test('product can have associations', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'cross-sell',
    ]);

    expect($parent->refresh()->associations)->toHaveCount(1);
});

test('product can get core associations with helpers', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'cross-sell',
    ]);

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'up-sell',
    ]);

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'alternate',
    ]);

    $crossSell = $parent->associations()->crossSell()->get();

    expect($crossSell)->toHaveCount(1);
    expect($crossSell->first()->type)->toEqual(ProductAssociation::CROSS_SELL);

    $upsell = $parent->associations()->upSell()->get();

    expect($upsell)->toHaveCount(1);
    expect($upsell->first()->type)->toEqual(ProductAssociation::UP_SELL);

    $alternate = $parent->associations()->alternate()->get();

    expect($alternate)->toHaveCount(1);
    expect($alternate->first()->type)->toEqual(ProductAssociation::ALTERNATE);
});

test('product can get all associations', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory(5)->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'cross-sell',
    ]);

    expect($parent->refresh()->associations)->toHaveCount(5);
});

test('product can have custom association types', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'custom-type',
    ]);

    $assoc = $parent->associations()->type('custom-type')->get();

    expect($assoc)->toHaveCount(1);
    expect($assoc->first()->type)->toEqual('custom-type');
});

test('can associate products via relation', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    $parent->associations()->create([
        'product_parent_id' => $parent->id,
        'product_target_id' => $target->id,
        'type' => 'custom-type',
    ]);

    $assoc = $parent->associations()->type('custom-type')->get();

    expect($assoc)->toHaveCount(1);
    expect($assoc->first()->type)->toEqual('custom-type');
});

test('can associate multiple products', function () {
    $parent = Product::factory()->create();
    $targetA = Product::factory()->create();
    $targetB = Product::factory()->create();

    $parent->associate([$targetA, $targetB], ProductAssociation::UP_SELL);

    $assoc = $parent->associations()->type(ProductAssociation::UP_SELL)->get();

    expect($assoc)->toHaveCount(2);
});

test('can associate products via helper', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    $parent->associate($target, 'custom-type');

    $assoc = $parent->associations()->type('custom-type')->get();

    expect($assoc)->toHaveCount(1);
    expect($assoc->first()->type)->toEqual('custom-type');
});

test('can remove all associations', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'cross-sell',
    ]);

    expect($parent->refresh()->associations)->toHaveCount(1);

    $parent->dissociate($target);

    expect($parent->refresh()->associations)->toHaveCount(0);
});

test('can only remove associations of a certain type', function () {
    $parent = Product::factory()->create();
    $target = Product::factory()->create();

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'cross-sell',
    ]);

    ProductAssociation::factory()->create([
        'product_parent_id' => $parent,
        'product_target_id' => $target,
        'type' => 'up-sell',
    ]);

    expect($parent->refresh()->associations)->toHaveCount(2);

    $parent->dissociate($target, 'cross-sell');

    expect($parent->refresh()->associations)->toHaveCount(1);
    expect($parent->refresh()->associations->first()->type)->toEqual('up-sell');
});

test('can have collections relationship', function () {
    $collection = Collection::factory()->create();
    $product = Product::factory()->create();
    $product->collections()->sync($collection);

    expect($product->collections)->toBeInstanceOf(EloquentCollection::class);
    expect($product->collections)->toHaveCount(1);
    expect($product->collections->first())->toBeInstanceOf(Collection::class);
    expect($product->collections->first()->pivot)->not->toBeNull();
    expect($product->collections->first()->pivot->position)->not->toBeNull();
});

test('can retrieve prices', function () {
    $attribute_data = collect([
        'meta_title' => new \Lunar\FieldTypes\Text('I like cake'),
        'pack_qty' => new \Lunar\FieldTypes\Number(12345),
        'description' => new \Lunar\FieldTypes\TranslatedText(collect([
            'en' => new \Lunar\FieldTypes\Text('Blue'),
            'fr' => new \Lunar\FieldTypes\Text('Bleu'),
        ])),
    ]);

    $product = Product::factory()
        ->for(ProductType::factory())
        ->create([
            'attribute_data' => $attribute_data,
        ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    Price::factory()->create([
        'priceable_id' => $variant->id,
        'priceable_type' => ProductVariant::class,
    ]);

    expect($product->refresh()->prices)->toHaveCount(1);
});
