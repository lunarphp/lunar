<?php

namespace Lunar\Tests\Panel\Fixtures;

use Lunar\Tests\Panel\Fixtures\Channels\ChannelFixtureServiceProvider;
use Lunar\Tests\Panel\TestCase;

class ChannelFixtureTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ChannelFixtureServiceProvider::class,
        ];
    }
}
