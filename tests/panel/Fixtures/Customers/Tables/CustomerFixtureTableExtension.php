<?php

namespace Lunar\Tests\Panel\Fixtures\Customers\Tables;

use Lunar\Panel\Tables\TableExtension;

class CustomerFixtureTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [PublicIdColumn::class, AddressCountColumn::class];
    }

    public function filters(): array
    {
        return [CompanyFilter::class];
    }
}
