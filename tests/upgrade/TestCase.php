<?php

declare(strict_types=1);

namespace Lunar\Tests\Upgrade;

use Lunar\Tests\TestCase as BaseTestCase;
use Lunar\Upgrade\UpgradeServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            UpgradeServiceProvider::class,
        ];
    }
}
