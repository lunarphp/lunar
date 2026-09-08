<?php

namespace Lunar\Shipping\DiscountTypes;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingDiscount extends AbstractDiscountType
{
    /**
     * Return the name of the discount type.
     */
    public function getName(): string
    {
        return __('shipping::discounts.shipping_discount.name');
    }

    /**
     * Apply the shipping discount to the cart.
     */
    public function apply(Cart $cart): Cart
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        $data = $this->discount->data;
        $currency = $cart->currency;
        $methods = $data['methods'] ?? [];

        if (empty($methods)) {
            return $cart;
        }

        if (! $cart->shippingBreakdown || $cart->shippingBreakdown->items->isEmpty()) {
            return $cart;
        }

        // Build a map of method_code => rule, and find any catch-all rule (null shipping_method_id).
        $codeRules = [];
        $catchAllRule = null;

        foreach ($methods as $methodRule) {
            $methodId = $methodRule['shipping_method_id'] ?? null;
            if ($methodId) {
                $method = ShippingMethod::find($methodId);
                if ($method) {
                    $codeRules[$method->code] = $methodRule;
                }
            } else {
                $catchAllRule = $methodRule;
            }
        }

        $breakdown = $cart->shippingBreakdown;
        $originalTotal = $breakdown->items->sum('price.value');
        $newTotal = 0;
        $discountApplied = false;

        foreach ($breakdown->items as $identifier => $item) {
            $rule = $codeRules[$identifier] ?? $catchAllRule;

            if (! $rule) {
                $newTotal += $item->price->value;

                continue;
            }

            $type = $rule['type'] ?? 'fixed';

            if ($type === 'percentage') {
                $percentage = (float) ($rule['percentage'] ?? 0);
                $saving = PriceCalculator::percentage($item->price->value, $percentage / 100, $currency);
                $discountedPrice = max(0, $item->price->value - $saving);
            } else {
                if (! isset($rule['prices'][$currency->code])) {
                    $newTotal += $item->price->value;

                    continue;
                }
                $discountedPrice = (int) $rule['prices'][$currency->code];
            }

            $breakdown->items->put($identifier, new ShippingBreakdownItem(
                name: $item->name,
                identifier: $identifier,
                price: new PriceValue($discountedPrice, $currency),
            ));

            $newTotal += $discountedPrice;
            $discountApplied = true;
        }

        if (! $discountApplied) {
            return $cart;
        }

        $cart->shippingBreakdown = $breakdown;
        $cart->shippingSubTotal = new PriceValue($newTotal, $currency);

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        $savingAmount = $originalTotal - $newTotal;

        if ($savingAmount > 0) {
            $this->addDiscountBreakdown($cart, new DiscountBreakdown(
                price: new PriceValue($savingAmount, $currency),
                lines: collect(),
                discount: $this->discount,
            ));
        }

        return $cart;
    }
}
