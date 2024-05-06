<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\ShippingModifier;
use Lunar\Base\ShippingModifiers;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Tests\TestCase;

class ShippingModifiersTest extends TestCase
{
    use RefreshDatabase;

    private Cart $cart;

    private ShippingModifiers $shippingModifiers;

    private ShippingModifier $class;

    public function setUp(): void
    {
        parent::setUp();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $this->cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $this->class = new class extends ShippingModifier
        {
            public function handle(Cart $cart)
            {
                //
            }
        };

        $this->shippingModifiers = new ShippingModifiers();
    }

    /** @test */
    public function can_add_modifier()
    {
        $this->shippingModifiers->add($this->class::class);

        $this->assertCount(1, $this->shippingModifiers->getModifiers());
    }


    public function can_remove_modifier()
    {
        $this->shippingModifiers->remove($this->class::class);

        $this->assertCount(0, $this->shippingModifiers->getModifiers());
    }

}
