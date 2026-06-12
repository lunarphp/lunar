<?php

namespace Lunar\Checkout\Database\Factories;

use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Database\Factories\BaseFactory;

class CheckoutSessionFactory extends BaseFactory
{
    protected $model = CheckoutSession::class;

    public function definition(): array
    {
        $subTotal = $this->faker->numberBetween(200, 25000);

        return [
            'cart_reference' => (string) $this->faker->unique()->numberBetween(1, 100000),
            'channel_handle' => 'webstore',
            'currency_code' => 'GBP',
            'locale' => 'en',
            'cart_fingerprint' => hash('sha256', $this->faker->uuid()),
            'amount_subtotal' => $subTotal,
            'amount_total' => $subTotal,
            'status' => 'open',
            'customer_reference' => null,
            'customer_email' => null,
            'expires_at' => now()->addHours(24),
        ];
    }

    public function forCart(string|int $reference): static
    {
        return $this->state(fn () => ['cart_reference' => (string) $reference]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => Expired::$name,
            'active_cart_reference' => null,
            'expires_at' => now()->subHour(),
        ]);
    }

    public function expirable(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subHour()]);
    }

    public function paymentProcessing(): static
    {
        return $this->state(fn () => [
            'status' => PaymentProcessing::$name,
            'payment_intent_ref' => 'pi_'.$this->faker->unique()->lexify('??????????'),
            'payment_processing_at' => now()->subMinutes(5),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Completed::$name,
            'active_cart_reference' => null,
            'order_reference' => (string) $this->faker->numberBetween(1, 100000),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => Cancelled::$name,
            'active_cart_reference' => null,
            'cancelled_at' => now(),
        ]);
    }
}
