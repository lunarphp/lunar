<?php

namespace Lunar\Api\Resources;

use BackedEnum;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Price;
use Spatie\ModelStates\State;
use Stringable;
use UnitEnum;

/**
 * Turns resolved field values into wire values: ISO 8601 UTC dates, enum and
 * state values as strings, money as its wire shape, collections as arrays.
 */
final class Normalizer
{
    public static function normalize(mixed $value, SerializationContext $context): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof PriceValue || $value instanceof Price) {
            return Money::from($value, $context->locale());
        }

        if ($value instanceof DateTimeInterface) {
            $carbon = $value instanceof CarbonInterface ? $value : Carbon::instance($value);

            return $carbon->copy()->utc()->toIso8601ZuluString();
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        if ($value instanceof State) {
            return (string) $value;
        }

        if ($value instanceof Model) {
            return self::normalize($value->attributesToArray(), $context);
        }

        if ($value instanceof Arrayable) {
            return self::normalize($value->toArray(), $context);
        }

        if (is_iterable($value)) {
            $out = [];

            foreach ($value as $key => $item) {
                $out[$key] = self::normalize($item, $context);
            }

            return $out;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return $value;
    }
}
