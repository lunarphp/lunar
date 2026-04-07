<?php

namespace Lunar\Shipping\DiscountTypes;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Lunar\Admin\Base\LunarPanelDiscountInterface;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\DiscountTypes\AbstractDiscountType;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Currency;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingDiscount extends AbstractDiscountType implements LunarPanelDiscountInterface
{
    /**
     * Return the name of the discount type.
     */
    public function getName(): string
    {
        return __('lunarpanel.shipping::discounts.shipping_discount.name');
    }

    /**
     * Apply the shipping discount to the cart.
     */
    public function apply(CartContract $cart): CartContract
    {
        if (! $this->checkDiscountConditions($cart)) {
            return $cart;
        }

        $data = $this->discount->data;
        $currency = $cart->currency;

        if (! isset($data['prices'][$currency->code])) {
            return $cart;
        }

        $discountedPrice = (int) $data['prices'][$currency->code];

        if (! $cart->shippingBreakdown || $cart->shippingBreakdown->items->isEmpty()) {
            return $cart;
        }

        $shippingMethodId = $data['shipping_method_id'] ?? null;
        $methodCode = null;

        if ($shippingMethodId) {
            $method = ShippingMethod::find($shippingMethodId);
            if (! $method) {
                return $cart;
            }
            $methodCode = $method->code;
        }

        $breakdown = $cart->shippingBreakdown;
        $originalTotal = $breakdown->items->sum('price.value');
        $newTotal = 0;
        $discountApplied = false;

        foreach ($breakdown->items as $identifier => $item) {
            if ($methodCode && $identifier !== $methodCode) {
                $newTotal += $item->price->value;
                continue;
            }

            $breakdown->items->put($identifier, new ShippingBreakdownItem(
                name: $item->name,
                identifier: $identifier,
                price: new Price($discountedPrice, $currency, 1),
            ));

            $newTotal += $discountedPrice;
            $discountApplied = true;
        }

        if (! $discountApplied) {
            return $cart;
        }

        $cart->shippingBreakdown = $breakdown;
        $cart->shippingSubTotal = new Price($newTotal, $currency, 1);

        if (! $cart->discounts) {
            $cart->discounts = collect();
        }

        $cart->discounts->push($this);

        $savingAmount = $originalTotal - $newTotal;

        if ($savingAmount > 0) {
            $this->addDiscountBreakdown($cart, new DiscountBreakdown(
                price: new Price($savingAmount, $currency, 1),
                lines: collect(),
                discount: $this->discount,
            ));
        }

        return $cart;
    }

    /**
     * Return the Filament form schema for the admin panel.
     */
    public function lunarPanelSchema(): array
    {
        $currencies = Currency::enabled()->get();

        $priceFields = $currencies->map(fn ($currency) =>
            TextInput::make("data.prices.{$currency->code}")
                ->label($currency->name)
                ->helperText($currency->code)
                ->numeric()
                ->minValue(0)
                ->required()
        )->toArray();

        return [
            Select::make('data.shipping_method_id')
                ->label(__('lunarpanel.shipping::discounts.shipping_discount.form.shipping_method_id.label'))
                ->placeholder(__('lunarpanel.shipping::discounts.shipping_discount.form.shipping_method_id.placeholder'))
                ->options(fn () => ShippingMethod::get()->pluck('name', 'id'))
                ->nullable(),
            ...$priceFields,
        ];
    }

    /**
     * Mutate form data before filling (convert stored integer prices to decimals).
     */
    public function lunarPanelOnFill(array $data): array
    {
        $currencies = Currency::enabled()->get();

        foreach ($currencies as $currency) {
            $stored = $data['data']['prices'][$currency->code] ?? null;
            if ($stored !== null) {
                $data['data']['prices'][$currency->code] = $stored / $currency->factor;
            }
        }

        return $data;
    }

    /**
     * Mutate form data before saving (convert decimal prices to integers and handle min_prices).
     */
    public function lunarPanelOnSave(array $data): array
    {
        $currencies = Currency::enabled()->get();

        foreach ($currencies as $currency) {
            $minPrice = $data['data']['min_prices'][$currency->code] ?? null;
            if ($minPrice !== null) {
                $data['data']['min_prices'][$currency->code] = (int) round($minPrice * $currency->factor);
            }

            $price = $data['data']['prices'][$currency->code] ?? null;
            if ($price !== null) {
                $data['data']['prices'][$currency->code] = (int) round($price * $currency->factor);
            }
        }

        return $data;
    }

    /**
     * No additional relation managers required.
     */
    public function lunarPanelRelationManagers(): array
    {
        return [];
    }
}
