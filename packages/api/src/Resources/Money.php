<?php

namespace Lunar\Api\Resources;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Price;

/**
 * The wire shape of a monetary amount: minor units plus the currency, so a
 * consumer never has to know a currency's decimal places to do arithmetic.
 */
final class Money
{
    /**
     * @return array{amount: int, currency: string, decimal_places: int, formatted: string|null}
     */
    public static function from(PriceValue|Price $price, ?string $locale = null): array
    {
        $value = $price instanceof Price
            ? new PriceValue($price->price, $price->currency)
            : $price;

        $currency = $value->resolveCurrency();

        return [
            'amount' => $value->value,
            'currency' => $currency->code,
            'decimal_places' => (int) $currency->decimal_places,
            'formatted' => $value->format($locale),
        ];
    }
}
