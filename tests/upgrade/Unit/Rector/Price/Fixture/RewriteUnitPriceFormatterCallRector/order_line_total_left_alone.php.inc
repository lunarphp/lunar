<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function order_line_total_left_alone(\Lunar\Core\Models\OrderLine $line): mixed
{
    // A line total has no per-unit meaning, so the rule leaves it for manual review.
    return $line->total->unitDecimal();
}

?>
