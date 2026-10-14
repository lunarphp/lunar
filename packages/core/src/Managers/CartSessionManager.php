<?php

namespace Lunar\Core\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cookie\QueueingFactory;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;

class CartSessionManager implements CartSession
{
    protected ?Channel $channel = null;

    protected ?Currency $currency = null;

    /**
     * The plaintext remember token this request holds — read from the incoming
     * cookie, then replaced whenever a new one is queued.
     */
    protected ?string $rememberToken = null;

    protected bool $rememberTokenResolved = false;

    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
        protected ResolvesStorefrontContext $resolveStorefrontContext,
        protected QueueingFactory $cookies,
        protected Request $request,
        public ?Cart $cart = null,
    ) {
        //
    }

    public function allowsMultipleOrdersPerCart(): bool
    {
        return config('lunar.cart_session.allow_multiple_orders_per_cart', false);
    }

    /**
     * {@inheritDoc}
     */
    public function current(bool $estimateShipping = false, bool $calculate = true): ?Cart
    {
        return $this->fetchOrCreate(
            config('lunar.cart_session.auto_create', false),
            estimateShipping: $estimateShipping,
            calculate: $calculate,
        );
    }

    /**
     * Set the criteria to use when estimating shipping costs.
     *
     * @return $this
     */
    public function estimateShippingUsing(array $meta): self
    {
        $this->sessionManager->put('shipping_estimate_meta', $meta);

        return $this;
    }

    /**
     * Return the shipping estimate meta.
     */
    public function getShippingEstimateMeta(): array
    {
        return $this->sessionManager->get('shipping_estimate_meta', []);
    }

    /**
     * {@inheritDoc}
     */
    public function forget(?bool $delete = null): void
    {
        $delete = is_null($delete) ? config('lunar.cart_session.delete_on_forget', true) : $delete;

        $cartId = $this->sessionManager->get(
            $this->getSessionKey()
        );

        if ($cartId && $this->rememberEnabled()) {
            $this->storeRememberToken($cartId, null);
        }

        $this->forgetRememberCookie();

        if ($delete) {
            Cart::destroy($cartId);
        }

        $this->cart = null;

        $this->sessionManager->forget('shipping_estimate_meta');
        $this->sessionManager->forget(
            $this->getSessionKey()
        );

    }

    /**
     * {@inheritDoc}
     */
    public function manager(): ?Cart
    {
        if (! $this->cart?->exists) {
            $this->fetchOrCreate(create: true);
        }

        return $this->cart;
    }

    /**
     * {@inheritDoc}
     */
    public function associate(Cart $cart, Authenticatable $user, $policy): void
    {
        /** @var Cart $cart */
        $this->use(
            $cart->associate($user, $policy)
        );
    }

    /**
     * Set the cart to be used for the session.
     */
    public function use(Cart $cart): Cart
    {
        /** @var Cart $cart */
        $this->sessionManager->put(
            $this->getSessionKey(),
            $cart->id
        );

        $this->remember($cart);

        return $this->cart = $cart;
    }

    /**
     * Fetches a cart and optionally creates one if it doesn't exist.
     */
    protected function fetchOrCreate(bool $create = false, bool $estimateShipping = false, bool $calculate = true): ?Cart
    {
        $cartId = $this->sessionManager->get(
            $this->getSessionKey()
        );

        $restored = false;

        if (! $cartId && $cartId = $this->findRememberedCartId()) {
            $restored = true;
        }

        if (! $cartId && $user = $this->authManager->user()) {
            $cartId = $user->carts()->unmerged()->active()->latest('id')->value('id');
        }

        if (! $cartId) {
            return $create ? $this->cart = $this->createNewCart() : null;
        }

        $cart = $this->cart?->exists ? $this->cart : Cart::with(
            config('lunar.cart.eager_load', [])
        )->find($cartId);

        if (! $cart) {
            return $create ? $this->createNewCart() : null;
        }

        if ($cart->hasCompletedOrders() && ! $this->allowsMultipleOrdersPerCart()) {
            return $this->createNewCart();
        }

        if ($restored) {
            $this->refreshRememberCookie();
            $cart->touch();
        }

        $this->cart = $cart;
        if ($calculate) {
            $this->cart->calculate();
        }

        if ($estimateShipping) {
            $this->estimateShipping();
        }

        return $this->use($this->cart);
    }

    public function estimateShipping(): void
    {
        if (! $this->cart?->exists) {
            return;
        }

        // Some shipping drivers might require sub-totals to be present
        // before they can estimate a shipping cost, doing this in the driver
        // itself can lead to infinite loops, so we calculate before.
        $this->cart->calculate();
        $this->cart->getEstimatedShipping(
            $this->getShippingEstimateMeta(),
            setOverride: true
        );
        $this->cart->calculate(force: true);
    }

    /**
     * Get the cart session key.
     */
    public function getSessionKey(): string
    {
        return config('lunar.cart_session.session_key');
    }

    protected function rememberEnabled(): bool
    {
        return (bool) config('lunar.cart_session.remember.enabled', false);
    }

    protected function rememberCookieName(): string
    {
        return config('lunar.cart_session.remember.cookie', 'lunar_cart_remember');
    }

    protected function currentRememberToken(): ?string
    {
        if (! $this->rememberTokenResolved) {
            $token = $this->request->cookie($this->rememberCookieName());

            $this->rememberToken = is_string($token) && $token !== '' ? $token : null;
            $this->rememberTokenResolved = true;
        }

        return $this->rememberToken;
    }

    /**
     * Find the guest cart the remember cookie points to, forgetting the cookie
     * when it no longer points to a restorable cart.
     */
    protected function findRememberedCartId(): ?int
    {
        if (! $this->rememberEnabled() || ! $token = $this->currentRememberToken()) {
            return null;
        }

        $cartId = Cart::query()
            ->where('remember_token', hash('sha256', $token))
            ->whereNull('user_id')
            ->unmerged()
            ->active()
            ->value('id');

        if (! $cartId) {
            $this->forgetRememberCookie();
        }

        return $cartId;
    }

    /**
     * Point the remember cookie at the given cart, or clear it for a cart that
     * belongs to a user — those resolve through the user, never a cookie.
     */
    protected function remember(Cart $cart): void
    {
        if (! $this->rememberEnabled()) {
            return;
        }

        if ($cart->user_id) {
            if ($cart->remember_token) {
                $this->storeRememberToken($cart, null);
            }

            $this->forgetRememberCookie();

            return;
        }

        $token = $this->currentRememberToken();

        if ($token && $cart->remember_token && hash_equals($cart->remember_token, hash('sha256', $token))) {
            return;
        }

        $token = Str::random(40);

        $this->storeRememberToken($cart, hash('sha256', $token));
        $this->queueRememberCookie($token);
    }

    /**
     * Write the token hash without touching `updated_at` or firing model events,
     * so issuing a token does not count as cart activity.
     */
    protected function storeRememberToken(Cart|int $cart, ?string $hash): void
    {
        Cart::withTrashed()
            ->whereKey($cart instanceof Cart ? $cart->getKey() : $cart)
            ->toBase()
            ->update(['remember_token' => $hash]);

        if ($cart instanceof Cart) {
            $cart->setAttribute('remember_token', $hash);
            $cart->syncOriginalAttribute('remember_token');
        }
    }

    /**
     * Re-send the current token with a fresh expiry. The token is not rotated:
     * parallel requests at the start of a session all carry the old cookie, and
     * rotating would make the others fail to match and forget it.
     */
    protected function refreshRememberCookie(): void
    {
        if ($token = $this->currentRememberToken()) {
            $this->queueRememberCookie($token);
        }
    }

    protected function queueRememberCookie(string $token): void
    {
        $this->cookies->queue($this->cookies->make(
            name: $this->rememberCookieName(),
            value: $token,
            minutes: (int) config('lunar.cart_session.remember.lifetime', 60 * 24 * 30),
            path: config('session.path'),
            domain: config('session.domain'),
            secure: config('session.secure'),
            httpOnly: true,
            sameSite: config('session.same_site'),
        ));

        $this->rememberToken = $token;
        $this->rememberTokenResolved = true;
    }

    protected function forgetRememberCookie(): void
    {
        if (! $this->rememberEnabled() || ! $this->currentRememberToken()) {
            return;
        }

        $this->cookies->queue($this->cookies->forget(
            $this->rememberCookieName(),
            config('session.path'),
            config('session.domain'),
        ));

        $this->rememberToken = null;
    }

    /**
     * Set the current channel.
     */
    public function setChannel(Channel $channel): void
    {
        /** @var Channel $channel */
        $this->channel = $channel;

        if ($this->current() && $this->current()->channel_id != $channel->id) {
            $this->cart->update([
                'channel_id' => $channel->id,
            ]);
        }
    }

    /**
     * Set the current currency.
     */
    public function setCurrency(Currency $currency): void
    {
        /** @var Currency $currency */
        $this->currency = $currency;

        if ($this->current() && $this->current()->currency_id != $currency->id) {
            $this->cart->update([
                'currency_id' => $currency->id,
            ]);

            // Prices are resolved from the cart's loaded relations, and the
            // lines carry their own copy of the cart (see lunar.cart.eager_load).
            // Without dropping these the next calculate() prices in the old
            // currency.
            $this->cart->unsetRelation('currency');
            $this->cart->unsetRelation('lines');
        }
    }

    /**
     * Return the current currency.
     */
    public function getCurrency(): Currency
    {
        return $this->currency?->exists ? $this->currency : Currency::getDefault();
    }

    /**
     * Return the current channel.
     */
    public function getChannel(): Channel
    {
        return $this->channel?->exists ? $this->channel : Channel::getDefault();
    }

    /**
     * Return available shipping options for the current cart.
     */
    public function getShippingOptions(): Collection
    {
        return ShippingManifest::getOptions(
            $this->current()
        );
    }

    /**
     * Create an order from a cart instance.
     */
    public function createOrder(bool $forget = true): Order
    {
        $order = $this->manager()->createOrder(
            allowMultipleOrders: $this->allowsMultipleOrdersPerCart()
        );

        if ($forget) {
            $this->forget();
        }

        return $order;
    }

    /**
     * Create a new cart instance.
     */
    protected function createNewCart(): Cart
    {
        $user = $this->authManager->user();
        $customer = optional($user)->latestCustomer();

        // An explicit setChannel()/setCurrency() override wins; otherwise the
        // resolver supplies channel/currency/region from the default region.
        $context = $this->resolveStorefrontContext->execute(
            channel: $this->channel?->exists ? $this->channel : null,
            currency: $this->currency?->exists ? $this->currency : null,
            customer: $customer,
        );

        $cart = Cart::create([
            'currency_id' => $context->currency->id,
            'channel_id' => $context->channel->id,
            'region_id' => $context->region?->id,
            'user_id' => optional($user)->id,
            'customer_id' => optional($customer)->id,
        ]);

        return $this->use($cart);
    }

    public function __call($method, $args)
    {
        if (! $this->cart?->exists) {
            $this->fetchOrCreate(create: true, calculate: false);
        }

        return $this->cart->{$method}(...$args);
    }
}
