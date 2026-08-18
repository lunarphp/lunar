<?php

namespace Lunar\Tests\Panel\Fixtures\Channels;

use Illuminate\Support\ServiceProvider;
use Lunar\Panel\Facades\Panel;

class ChannelFixtureServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Panel::section(new ChannelFixtureSection);
    }
}
