<?php

namespace Lunar\Opayo\Components;

use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Opayo\Facades\Opayo;

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
     * The card identifier token.
     *
     * @var string
     */
    public $identifier;

    /**
     * The session key
     *
     * @var [type]
     */
    public $sessionKey;

    /**
     * Information regarding the browser.
     */
    public array $browser = [];

    /**
     * The ThreeDSecure information
     *
     * @var array
     */
    public $threeDSecure = [
        'acsUrl' => null,
        'acsTransId' => null,
        'dsTransId' => null,
        'paReq' => null,
        'cReq' => null,
        'transactionId' => null,
    ];

    /**
     * Whether we are processing the payment.
     */
    public bool $processing = false;

    /**
     * Whether to show the ThreeDSecure challenge.
     */
    public bool $showChallenge = false;

    /**
     * The payment processing error.
     */
    public ?string $error = null;

    public $merchantKey = null;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cardDetailsSubmitted',
        'opayoThreedSecureResponse',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->policy = config('opayo.policy', 'capture');
        $this->refreshMerchantKey();
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'identifier' => 'string|required',
        ];
    }

    /**
     * Return the client secret for Payment Intent
     *
     * @return void
     */
    public function refreshMerchantKey()
    {
        $this->merchantKey = Opayo::getMerchantKey();
    }

    public function updatedShowChallenge($value)
    {
        if (! $value) {
            $this->resetState();
        }
    }

    /**
     * Process the transaction
     *
     * @return void
     */
    public function process()
    {
        $result = Payments::driver('opayo')->cart($this->cart)->withData(array_merge([
            'card_identifier' => $this->identifier,
            'merchant_key' => $this->sessionKey,
            'ip' => app()->request->ip(),
            'accept' => app()->request->header('Accept'),
        ], $this->browser))->authorize();

        if ($result->success) {
            if ($result->status == Opayo::THREED_AUTH) {
                $this->threeDSecure['acsUrl'] = $result->acsUrl;
                $this->threeDSecure['acsTransId'] = $result->acsTransId;
                $this->threeDSecure['dsTransId'] = $result->dsTransId;
                $this->threeDSecure['cReq'] = $result->cReq;
                $this->threeDSecure['paReq'] = $result->paReq;
                $this->threeDSecure['transactionId'] = $result->transactionId;
                $this->showChallenge = true;

                return;
            }
        }

        if ($result->status == Opayo::ALREADY_PLACED) {
            CartSession::forget();
        }

        if ($result->status == Opayo::AUTH_SUCCESSFUL) {
            $this->emit('opayoAuthorizationSuccessful');

            return;
        }

        if ($result->status == Opayo::AUTH_FAILED) {
            $this->emit('opayoAuthorizationFailed');

            return;
        }
    }

    /**
     * Process the ThreeDSecure response
     *
     * @param  array  $params
     * @return void
     */
    public function processThreed($params)
    {
        $result = Payments::driver('opayo')->cart($this->cart)->withData([
            'cres' => $params['cres'] ?? null,
            'pares' => $params['pares'] ?? null,
            'transaction_id' => $this->threeDSecure['transactionId'],
        ])->threedsecure();

        if (! $result->success) {
            if ($result->status == Opayo::THREED_SECURE_FAILED) {
                $this->error = 'You must complete the extra authentication';
                $this->resetState();

                return;
            }

            if ($result->status == Opayo::AUTH_FAILED) {
                $this->error = 'Payment failed, please check details and try again';
                $this->resetState();

                return;
            }
        }

        if ($result->status == Opayo::AUTH_SUCCESSFUL) {
            $this->emit('opayoAuthorizationSuccessful');

            return;
        }
    }

    /**
     * Reset the ThreeDSecure state.
     *
     * @return void
     */
    protected function resetState()
    {
        $this->processing = false;
        $this->showChallenge = false;
        $this->threedSecure = [
            'acsUrl' => null,
            'acsTransId' => null,
            'dsTransId' => null,
            'cReq' => null,
            'transactionId' => null,
        ];
        $this->refreshMerchantKey();
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
        return view('lunar::opayo.components.payment-form');
    }
}
