<?php

namespace Lunar\Core\Enums;

/**
 * The ordered step lists core runs. Each is changed through the Steps facade.
 */
enum StepList: string
{
    case CartCalculation = 'cart.calculation';
    case CartLinePricing = 'cart.line_pricing';
    case PriceMatching = 'pricing.matching';
    case OrderCreation = 'orders.creation';
    case AddToCartValidation = 'cart.validation.add_to_cart';
    case UpdateCartLineValidation = 'cart.validation.update_cart_line';
    case RemoveFromCartValidation = 'cart.validation.remove_from_cart';
    case AddAddressValidation = 'cart.validation.add_address';
    case SetShippingOptionValidation = 'cart.validation.set_shipping_option';
    case OrderCreateValidation = 'cart.validation.order_create';
    case CartPruning = 'cart.pruning';

    /**
     * The config key that defined this list before spec 0093.
     */
    public function legacyConfigKey(): string
    {
        return match ($this) {
            self::CartCalculation => 'lunar.cart.pipelines.cart',
            self::CartLinePricing => 'lunar.cart.pipelines.cart_lines',
            self::PriceMatching => 'lunar.pricing.pipelines',
            self::OrderCreation => 'lunar.orders.pipelines.creation',
            self::AddToCartValidation => 'lunar.cart.validators.add_to_cart',
            self::UpdateCartLineValidation => 'lunar.cart.validators.update_cart_line',
            self::RemoveFromCartValidation => 'lunar.cart.validators.remove_from_cart',
            self::AddAddressValidation => 'lunar.cart.validators.add_address',
            self::SetShippingOptionValidation => 'lunar.cart.validators.set_shipping_option',
            self::OrderCreateValidation => 'lunar.cart.validators.order_create',
            self::CartPruning => 'lunar.cart.prune_tables.pipelines',
        };
    }
}
