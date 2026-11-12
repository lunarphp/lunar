<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\CouponBatch;

interface CouponCodeGenerator
{
    /**
     * Generate one code for the batch. Uniqueness is not required here: the
     * caller replaces a code that collides with an existing one.
     */
    public function generate(CouponBatch $batch): string;
}
