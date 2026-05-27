<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Catalogue\Fixture\RewriteTranslatedFieldCallRector;

function read(\Lunar\Core\Models\Product $product): array
{
    return [
        $product->translateAttribute('name'),
        $product->attr('description'),
        $product->translateAttribute('name', 'fr'),
        $product->attr('custom_handle'),
    ];
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Catalogue\Fixture\RewriteTranslatedFieldCallRector;

function read(\Lunar\Core\Models\Product $product): array
{
    return [
        $product->translate('name'),
        $product->translate('description'),
        $product->translate('name', 'fr'),
        $product->attr('custom_handle'),
    ];
}

?>
