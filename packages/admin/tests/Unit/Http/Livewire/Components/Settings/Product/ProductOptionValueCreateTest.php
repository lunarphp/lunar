<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Product\Options\OptionEdit;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Language;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

class ProductOptionValueCreateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);
    }

    /**
     * @test
     * */
    public function can_populate_product_option_data()
    {
        ProductOption::factory(1)->create()->each(function ($option) {
            $staff = Staff::factory()->create([
                'admin' => true,
            ]);

            LiveWire::actingAs($staff, 'staff')
                ->test(OptionEdit::class, ['productOption' => $option])
                ->set('newProductOptionValue.name.'.Language::getDefault()->code, 'Size')
                ->call('createOptionValue');

            $this->assertDatabaseHas((new ProductOptionValue())->getTable(), [
                'product_option_id' => $option->id,
                'name' => json_encode([Language::getDefault()->code => 'Size']),
            ]);
        });
    }
}
