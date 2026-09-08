<?php

namespace Lunar\Checkout\PaymentMethods;

use Closure;
use Lunar\Checkout\Contracts\ExclusivePaymentMethod;
use Lunar\Checkout\Contracts\GuardsPayment;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Cart;

/**
 * Orders placed against a trade account and invoiced on terms (spec 0014).
 * Nothing is paid at the checkout: the session completes synchronously like
 * any Offline method and the order is placed with no transaction.
 *
 * Lunar does not know what qualifies a customer for an account. The host
 * says, once, in a service provider:
 *
 *     OnAccount::eligibleWhen(fn (Cart $cart): bool => filled($cart->customer?->account_ref));
 *
 * With no closure registered the method is never available. When it is
 * available it is the only method offered (ExclusivePaymentMethod). A host
 * that wants a purchase order reference on every account order turns the
 * pay-time guard on with OnAccount::requireReference().
 */
class OnAccount extends Offline implements ExclusivePaymentMethod, GuardsPayment
{
    protected static ?Closure $eligible = null;

    protected static bool $requireReference = false;

    /**
     * @param  (Closure(Cart): bool)|null  $eligible
     */
    public static function eligibleWhen(?Closure $eligible): void
    {
        static::$eligible = $eligible;
    }

    public static function requireReference(bool $required = true): void
    {
        static::$requireReference = $required;
    }

    /**
     * Back to the shipped defaults: never available, reference optional.
     */
    public static function reset(): void
    {
        static::$eligible = null;
        static::$requireReference = false;
    }

    public function handle(): string
    {
        return 'on-account';
    }

    public function label(): string
    {
        return __('lunar-checkout::checkout.payments.on_account.label');
    }

    public function component(): string
    {
        return 'on-account-notice';
    }

    public function isAvailable(Cart $cart): bool
    {
        return static::$eligible !== null && (bool) (static::$eligible)($cart);
    }

    public function paymentBlocker(CheckoutSession $session, Cart $cart): ?string
    {
        if (! static::$requireReference) {
            return null;
        }

        $reference = trim((string) (($session->getElementData('order-details') ?? [])['reference'] ?? ''));

        return $reference === ''
            ? __('lunar-checkout::checkout.payments.on_account.guard')
            : null;
    }
}
