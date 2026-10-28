<?php

namespace Lunar\Tests\Bundles;

use Lunar\Bundles\BundlesServiceProvider;
use Lunar\Tests\Bundles\Support\MigrationState;
use Lunar\Tests\Panel\TestCase as PanelBaseTestCase;

/** Boots the panel with the bundles add-on registered, for its panel extension tests. */
class PanelTestCase extends PanelBaseTestCase
{
    protected function setUp(): void
    {
        MigrationState::ensureFor(static::class);

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            BundlesServiceProvider::class,
        ];
    }
}
