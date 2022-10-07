<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Tests\TestCase;

class ProductOptionValueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_product_option_value_with_translations()
    {
        $optionValue = ProductOptionValue::factory()
            ->for(ProductOption::factory(), 'option')
            ->create();

        $this->assertDatabaseHas((new ProductOptionValue)->getTable(), [
            'id' => $optionValue->id,
            'product_option_id' => $optionValue->option->id,
            'name' => json_encode($optionValue->name),
        ]);

        $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 1);
    }

    /** @test */
    public function can_edit_translated_product_option_value()
    {
        /** @var ProductOptionValue $optionValue */
        $optionValue = ProductOptionValue::factory()
            ->for(ProductOption::factory(), 'option')
            ->create();

        $optionValue->update(['name' => $updatedName = collect([
            'en' => $optionValue->translate('name').'-edited',
        ])]);

        $this->assertDatabaseHas((new ProductOptionValue)->getTable(), [
            'id' => $optionValue->id,
            'product_option_id' => $optionValue->option->id,
            'name' => $updatedName->toJson(),
        ]);
    }

    /** @test */
    public function can_delete_product_option_value()
    {
        /** @var ProductOptionValue $optionValue */
        $optionValue = ProductOptionValue::factory()
            ->for(ProductOption::factory(), 'option')
            ->create();

        $optionValue->delete();

        $this->assertDatabaseMissing((new ProductOptionValue)->getTable(), [
            'id' => $optionValue->id,
        ]);
    }

    /** @test */
    public function can_update_all_product_option_value_positions()
    {
        $optionValues = ProductOptionValue::factory(10)
            ->for(ProductOption::factory(), 'option')
            ->create()
            ->each(function ($optionValue) {
                $optionValue->update([
                    'position' => $optionValue->id,
                ]);
            });

        $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 10);
        $this->assertEquals(range(1, 10), $optionValues->pluck('position')->toArray());
    }
}
