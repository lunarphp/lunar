<?php

namespace GetCandy\Hub\Tests\Unit\Jobs\Products;

use GetCandy\Hub\Exceptions\InvalidProductValuesException;
use GetCandy\Hub\Jobs\Products\GenerateVariants;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Product;
use GetCandy\Models\ProductOption;
use GetCandy\Models\ProductOptionValue;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

/**
 * @group getcandyhub.jobs
 */
class GenerateVariantsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_handle_empty_array_of_values()
    {
        $product = Product::factory()->create();

        GenerateVariants::dispatchSync($product, []);

        $this->assertCount(0, $product->variants()->get());
    }

    /** @test */
    public function can_generate_from_one_set_of_option_values()
    {
        Config::set('getcandy-hub.products.sku.unique', true);

        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $option = ProductOption::factory()->create();

        $option->values()->createMany(
            ProductOptionValue::factory(2)->make()->toArray()
        );

        GenerateVariants::dispatchSync($product, ProductOptionValue::get()->pluck('id'));

        $this->assertCount(2, $product->variants()->get());
    }

    /** @test */
    public function can_handle_passing_collection_as_value_parameter()
    {
        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        GenerateVariants::dispatchSync($product, collect([]));

        $this->assertCount(0, $product->variants()->get());
    }

    /** @test */
    public function check_correct_number_of_values_are_passed()
    {
        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        ProductOption::factory(2)->create()->each(function ($option) {
            $option->values()->createMany(
                ProductOptionValue::factory(2)->make()->toArray()
            );
        });

        $this->expectException(InvalidProductValuesException::class);
        GenerateVariants::dispatchSync($product, [1, 9999999]);
    }

    /** @test */
    public function check_only_flattened_array_can_be_passed()
    {
        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $this->expectException(ValidationException::class);
        GenerateVariants::dispatchSync($product, [[1], [2]]);
    }

    /** @test */
    public function check_only_numeric_values_can_be_passed_as_value_ids()
    {
        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $this->expectException(ValidationException::class);
        GenerateVariants::dispatchSync($product, [1, 'foo']);
    }

    /** @test */
    public function can_generate_variants()
    {
        Config::set('getcandy-hub.products.sku.unique', true);

        $product = Product::factory()
            ->has(ProductVariant::factory(), 'variants')
            ->create();

        // Generate specific options/values here so we can consistently
        // test that they have been generated.
        $colour = ProductOption::factory()->create([
            'name' => [
                'en' => 'Colour',
            ],
        ]);

        $blue = ProductOptionValue::factory()->create([
            'product_option_id' => $colour->id,
            'name' => [
                'en' => 'Blue',
            ],
        ]);

        $red = ProductOptionValue::factory()->create([
            'product_option_id' => $colour->id,
            'name' => [
                'en' => 'Red',
            ],
        ]);

        $size = ProductOption::factory()->create([
            'name' => [
                'en' => 'Size',
            ],
        ]);

        $small = ProductOptionValue::factory()->create([
            'product_option_id' => $size->id,
            'name' => [
                'en' => 'Small',
            ],
        ]);

        $medium = ProductOptionValue::factory()->create([
            'product_option_id' => $size->id,
            'name' => [
                'en' => 'Small',
            ],
        ]);

        $values = ProductOptionValue::get();

        GenerateVariants::dispatchSync($product, $values->pluck('id'));

        $variants = $product->variants()->get();

        $this->assertCount(4, $variants);

        $valuesToCheck = [
            [$blue->id, $small->id],
            [$blue->id, $medium->id],
            [$red->id, $small->id],
            [$red->id, $medium->id],
        ];

        // Make sure we have the correct values in the database.
        foreach ($valuesToCheck as $values) {
            $exists = $product->variants()->whereHas('values', function ($query) use ($values) {
                $query->whereIn('value_id', $values);
            })->first();
            $this->assertNotNull($exists);
        }
    }
}
