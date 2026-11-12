<?php

namespace Lunar\Core\Generators;

use Lunar\Core\Contracts\CouponCodeGenerator as CouponCodeGeneratorContract;
use Lunar\Core\DataObjects\CouponBatch;

/**
 * Uppercase letters and digits, leaving out 0, O, 1, I and L so a code read
 * aloud or off paper can't be mistyped.
 */
class CouponCodeGenerator implements CouponCodeGeneratorContract
{
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function generate(CouponBatch $batch): string
    {
        $code = $batch->prefix;
        $last = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $batch->length; $i++) {
            $code .= self::ALPHABET[random_int(0, $last)];
        }

        return $code;
    }
}
