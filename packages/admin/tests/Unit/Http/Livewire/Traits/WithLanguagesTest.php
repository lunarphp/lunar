<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Products\ProductShow;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

/**
 * @group livewire.traits
 */
class WithLanguagesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);

        Language::factory()->create([
            'default' => false,
            'code' => 'fr',
        ]);

        Currency::factory()->create([
            'default' => true,
        ]);
    }

    /** @test */
    public function trait_boots_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $defaultLanguage = Language::factory()->create([
            'default' => true,
        ]);

        Language::factory()->create([
            'default' => false,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        foreach (Currency::get() as $currency) {
            Price::factory()->create([
                'priceable_type' => ProductVariant::class,
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'tier' => 1,
            ]);
        }

        LiveWire::actingAs($staff, 'staff')
            ->test(ProductShow::class, [
                'product' => $product,
            ])->assertSet('defaultLanguage.id', $defaultLanguage->id)
                ->assertCount('languages', Language::count());
    }
}
