<?php

namespace Lunar\Tests\Panel\Fixtures\Customers;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Facades\Panel;

class CustomerFixtureServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Panel::section(new CustomerFixtureSection);
    }
}
