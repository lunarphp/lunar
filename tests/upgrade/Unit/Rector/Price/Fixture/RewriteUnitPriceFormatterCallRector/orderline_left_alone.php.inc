<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Price\Fixture\RewriteUnitPriceFormatterCallRector;

function order_line_unit_call_is_left_for_manual_review(\Lunar\Core\Models\OrderLine $line): mixed
{
    // unitDecimal on OrderLine was a latent v1 bug (double divide) — the
    // rule deliberately doesn't auto-rewrite. Upgrade docs flag this as
    // a manual review.
    return $line->unit_price->unitDecimal();
}

?>
