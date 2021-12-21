<?php

namespace GetCandy\Hub\Tests\Unit\Http\Livewire\Traits;

use GetCandy\Hub\Http\Livewire\Components\Authentication\LoginForm;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductShow;
use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\Stubs\User;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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
            'brand' => 'BAR',
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
