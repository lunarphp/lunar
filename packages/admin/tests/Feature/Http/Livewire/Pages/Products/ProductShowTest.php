<?php

namespace GetCandy\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use GetCandy\Hub\Models\Staff;
use GetCandy\Hub\Tests\TestCase;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
            'default' => true,
        ]);
    }

    /** @test */
    public function cant_view_page_as_guest()
    {
        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $this->get(route('hub.products.show', $product->id))
            ->assertRedirect(
                route('hub.login')
            );
    }

    /** @test */
    public function cant_view_page_without_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        $this->get(route('hub.products.show', $product->id))
            ->assertStatus(403);
    }

    /** @test */
    public function can_view_page_with_correct_permission()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-products',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->create([
            'status' => 'published',
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

        $this->get(route('hub.products.show', $product->id))
            ->assertSeeLivewire('hub.components.products.show');
    }

    /** @test */
    public function product_with_one_variant_has_variant_components_visible()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-products',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        Price::factory()->create([
            'currency_id'    => $currency->id,
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
        ]);

        $this->get(route('hub.products.show', $product->id))
            ->assertSeeText('Pricing')
            ->assertSeeText('Inventory')
            ->assertSeeText('Shipping');
    }

    /** @test */
    public function product_with_variants_does_not_have_variant_components_visible()
    {
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->permissions()->createMany([
            [
                'handle' => 'catalogue:manage-products',
            ],
        ]);

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        ProductVariant::factory(4)->create([
            'product_id' => $product->id,
        ]);

        $this->get(route('hub.products.show', $product->id))
            ->assertDontSeeText('Pricing');
    }
}
