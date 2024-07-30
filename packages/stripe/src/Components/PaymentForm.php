<?php

namespace Lunar\Stripe\Components;

use Livewire\Component;
use Lunar\Models\Cart;
use Lunar\Stripe\Facades\Stripe;
use Stripe\Stripe as StripeClient;

class PaymentForm extends Component
{
    /**
     * The instance of the order.
     *
     * @var Order
     */
    public Cart $cart;

    /**
     * The return URL on a successful transaction
     *
     * @var string
     */
    public $returnUrl;

    /**
     * The policy for handling payments.
     *
     * @var string
     */
    public $policy;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cardDetailsSubmitted',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        StripeClient::setApiKey(config('services.stripe.key'));
        $this->policy = config('stripe.policy', 'capture');
    }

    /**
     * Return the client secret for Payment Intent
     *
     * @return void
     */
    public function getClientSecretProperty()
    {
        $intent = Stripe::createIntent($this->cart);

        return $intent->client_secret;
    }

    /**
     * Return the carts billing address.
     *
     * @return void
     */
    public function getBillingProperty()
    {
        return $this->cart->billingAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('lunar::stripe.components.payment-form');
    }
}
