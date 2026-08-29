<?php

namespace Lunar\Tests\Panel\Fixtures\Discounts;

use Illuminate\Support\ServiceProvider;
use Lunar\Core\Facades\Discounts;
use Lunar\Panel\Facades\Panel;

class DiscountFixtureServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Discounts::addType(FixtureDiscountType::class);

        Panel::section(new DiscountFixtureSection);
    }
}
