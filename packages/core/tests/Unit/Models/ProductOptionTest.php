<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Tests\TestCase;

/**
 * @group products
 */
class ProductOptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_option_with_translations()
    {
        $productOption = ProductOption::factory()->create();

        $this->assertDatabaseHas((new ProductOption)->getTable(), [
            'id' => $productOption->id,
            'name' => json_encode($productOption->name),
            'handle' => $productOption->handle,
            'position' => $productOption->position,
        ]);

        $this->assertDatabaseCount((new ProductOption)->getTable(), 1);
    }

    /** @test */
    public function handle_matches_name_default_locale()
    {
        /** @var ProductOption $productOption */
        $productOption = ProductOption::factory()->create();

        $this->assertEquals($productOption->handle, Str::slug($productOption->translate('name')));
    }

    /** @test */
    public function handle_if_not_unique_throw_exception()
    {
        $productOption = ProductOption::factory()->create();

        $this->expectException(QueryException::class);
        $this->expectWarningMessage('UNIQUE constraint failed');
        ProductOption::factory()->create([
            'handle' => $productOption->handle,
        ]);

        $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

        ProductOption::factory()->create([
            'handle' => $productOption->handle.'-unique',
        ]);

        $this->assertDatabaseCount((new ProductOption)->getTable(), 2);
    }

    /** @test */
    public function can_update_all_product_option_positions()
    {
        $productOptions = ProductOption::factory(10)->create()->each(function ($productOption) {
            $productOption->update([
                'position' => $productOption->id,
            ]);
        });

        $this->assertEquals(range(1, 10), $productOptions->pluck('position')->toArray());

        $position = 10;
        foreach ($productOptions as $productOption) {
            $productOption->position = $position;
            $productOption->save();
            $position--;
        }

        $this->assertEquals(array_reverse(range(1, 10)), $productOptions->pluck('position')->toArray());
    }

    /** @test */
    public function can_delete_product_option()
    {
        $productOption = ProductOption::factory()->create();
        $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

        $productOption->delete();
        $this->assertDatabaseCount((new ProductOption)->getTable(), 0);
    }

    /** @test */
    public function can_delete_product_option_by_handle()
    {
        $productOption = ProductOption::factory()->create();
        $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

        ProductOption::where('handle', $productOption->handle)->delete();
        $this->assertDatabaseCount((new ProductOption)->getTable(), 0);
    }

    /** @test */
    public function can_create_option_value()
    {
        $productOption = ProductOption::factory()->create();
        $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

        $productOption->values()->create([
            'name' => collect([
                'en' => 'Option Value 1 (EN)',
                'fr' => 'Option Value 1 (FR)',
            ]),
        ]);

        $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 1);
        $this->assertCount(1, ProductOptionValue::whereRelation(
            'option',
            'product_option_id',
            $productOption->id)->get(),
        );
    }

    /**
     * @test
     * */
    public function takes_scout_prefix_into_account()
    {
        $expected = config('scout.prefix').'product_options';

        $this->assertEquals($expected, (new ProductOption)->searchableAs());
    }
}
