<?php

namespace Lunar\Shipping\DiscountTypes;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Lunar\Admin\Base\LunarPanelDiscountInterface;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdownItem;
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

    /**
     * Return the Filament form schema for the admin panel.
     */
    public function lunarPanelSchema(): array
    {
        $currencies = Currency::enabled()->get();

        $priceFields = $currencies->map(fn ($currency) => TextInput::make("prices.{$currency->code}")
            ->label($currency->name)
            ->helperText($currency->code)
            ->numeric()
            ->minValue(0)
            ->visible(fn (Get $get) => ($get('type') ?? 'fixed') === 'fixed')
        )->toArray();

        return [
            Repeater::make('data.methods')
                ->label(__('lunarpanel.shipping::discounts.shipping_discount.form.methods.label'))
                ->addActionLabel(__('lunarpanel.shipping::discounts.shipping_discount.form.methods.add_label'))
                ->schema([
                    Select::make('shipping_method_id')
                        ->label(__('lunarpanel.shipping::discounts.shipping_discount.form.shipping_method_id.label'))
                        ->placeholder(__('lunarpanel.shipping::discounts.shipping_discount.form.shipping_method_id.placeholder'))
                        ->options(fn () => ShippingMethod::get()->pluck('name', 'id'))
                        ->nullable(),
                    Select::make('type')
                        ->label(__('lunarpanel.shipping::discounts.shipping_discount.form.type.label'))
                        ->options([
                            'fixed' => __('lunarpanel.shipping::discounts.shipping_discount.form.type.options.fixed'),
                            'percentage' => __('lunarpanel.shipping::discounts.shipping_discount.form.type.options.percentage'),
                        ])
                        ->default('fixed')
                        ->live()
                        ->required(),
                    TextInput::make('percentage')
                        ->label(__('lunarpanel.shipping::discounts.shipping_discount.form.percentage.label'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->visible(fn (Get $get) => $get('type') === 'percentage'),
                    Group::make()
                        ->columnSpanFull()
                        ->columns(3)
                        ->schema($priceFields)
                        ->visible(fn (Get $get) => ($get('type') ?? 'fixed') === 'fixed'),
                ])
                ->columns(2),
        ];
    }

    /**
     * Mutate form data before filling (convert stored integer prices to decimals).
     */
    public function lunarPanelOnFill(array $data): array
    {
        $currencies = Currency::enabled()->get();

        foreach ($data['data']['methods'] ?? [] as $i => $method) {
            if (($method['type'] ?? 'fixed') !== 'fixed') {
                continue;
            }
            foreach ($currencies as $currency) {
                $stored = $method['prices'][$currency->code] ?? null;
                if ($stored !== null) {
                    $data['data']['methods'][$i]['prices'][$currency->code] = $stored / $currency->factor;
                }
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
                $data['data']['min_prices'][$currency->code] = PriceCalculator::toMinor($minPrice, $currency);
            }
        }

        foreach ($data['data']['methods'] ?? [] as $i => $method) {
            if (($method['type'] ?? 'fixed') !== 'fixed') {
                continue;
            }
            foreach ($currencies as $currency) {
                $price = $method['prices'][$currency->code] ?? null;
                if ($price !== null) {
                    $data['data']['methods'][$i]['prices'][$currency->code] = PriceCalculator::toMinor($price, $currency);
                }
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
