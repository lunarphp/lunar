<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages\Settings\Products;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

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
        $this->setupRolesPermissions();

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
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->givePermissionTo('catalogue:manage-products');

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
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'tier' => 1,
            ]);
        }

        $this->get(route('hub.products.show', $product->id))
            ->assertSeeLivewire('hub.components.products.show');
    }

    /** @test */
    public function cant_view_soft_deleted_products()
    {
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->givePermissionTo('catalogue:manage-products');

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->create([
            'deleted_at' => now(),
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

        $this->get(route('hub.products.show', $product->id))
            ->assertSeeLivewire('hub.components.products.show');
    }

    /** @test */
    public function product_with_one_variant_has_variant_components_visible()
    {
        $this->setupRolesPermissions();

        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->givePermissionTo('catalogue:manage-products');

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        Price::factory()->create([
            'currency_id' => $currency->id,
            'priceable_type' => ProductVariant::class,
            'priceable_id' => $variant->id,
        ]);

        $this->get(route('hub.products.show', $product->id))
            ->assertSeeText('Pricing')
            ->assertSeeText('Inventory')
            ->assertSeeText('Shipping');
    }

    /** @test */
    public function product_with_variants_does_not_have_variant_components_visible()
    {
        $this->setupRolesPermissions();
        $staff = Staff::factory()->create([
            'admin' => false,
        ]);

        $staff->givePermissionTo('catalogue:manage-products');

        $this->actingAs($staff, 'staff');

        $product = Product::factory()->has(ProductVariant::factory(), 'variants')->create();

        ProductVariant::factory(4)->create([
            'product_id' => $product->id,
        ]);

        $this->get(route('hub.products.show', $product->id))
            ->assertDontSeeText('Pricing');
    }
}
