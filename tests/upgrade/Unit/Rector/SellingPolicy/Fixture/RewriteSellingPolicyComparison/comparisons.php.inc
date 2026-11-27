<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\SellingPolicy\Fixture\RewriteSellingPolicyComparison;

use Lunar\Core\Enums\SellingPolicy;

function policies(\Lunar\Core\Models\ProductVariant $variant, \Lunar\Core\Models\OrderLine $line, $untyped): array
{
    return [
        $variant->purchasable === 'always',
        'in_stock' == $variant->purchasable,
        $variant->purchasable !== 'in_stock_or_on_backorder',
        $variant->purchasable == 'in_stock_or_backorder',
        $variant->selling_policy === SellingPolicy::InStock,
        $variant->selling_policy != SellingPolicy::Always,
        $variant->purchasable === 'rogue',
        $line->purchasable === 'always',
        $untyped->purchasable === 'always',
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\SellingPolicy\Fixture\RewriteSellingPolicyComparison;

use Lunar\Core\Enums\SellingPolicy;

function policies(\Lunar\Core\Models\ProductVariant $variant, \Lunar\Core\Models\OrderLine $line, $untyped): array
{
    return [
        $variant->continue_selling,
        !$variant->continue_selling && !$variant->sell_against_incoming,
        !(!$variant->continue_selling && $variant->sell_against_incoming),
        !$variant->continue_selling && $variant->sell_against_incoming,
        !$variant->continue_selling && !$variant->sell_against_incoming,
        !$variant->continue_selling,
        $variant->purchasable === 'rogue',
        $line->purchasable === 'always',
        $untyped->purchasable === 'always',
    ];
}

?>
