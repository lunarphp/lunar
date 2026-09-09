<?php

namespace Lunar\Checkout\Elements;

/**
 * Captures a customer purchase-order reference and order notes (spec 0011
 * §G): the two fields every B2B checkout asks for, and the first thing in
 * this package that persists through the element bag rather than the cart.
 *
 * Both fields are optional. Requiring a reference for on-account payment would
 * branch on the selected payment method, which static rules (spec 0001 §B)
 * forbid; that belongs at the pay boundary.
 *
 * Opt-in like every element: a host registers it, and registering nothing is
 * how a host declines it. The package does not project the captured values
 * onto the order; hosts listen to OrderPlacing, because what these mean to a
 * merchant's downstream systems is theirs to decide.
 */
class OrderDetails extends AbstractCheckoutElement
{
    public function handle(): string
    {
        return 'order-details';
    }

    public function title(): string
    {
        return 'Order details';
    }

    public function component(): string
    {
        return 'order-details';
    }

    public function region(): string
    {
        return 'main';
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
