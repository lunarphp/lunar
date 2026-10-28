<?php

namespace Lunar\Bundles\Panel\Tables;

use Lunar\Panel\Tables\TableExtension;

class ProductsTableExtension extends TableExtension
{
    /** @return array<int, class-string> */
    public function columns(): array
    {
        return [BundleColumn::class];
    }

    /** @return array<int, class-string> */
    public function filters(): array
    {
        return [BundlesOnlyFilter::class];
    }
}
