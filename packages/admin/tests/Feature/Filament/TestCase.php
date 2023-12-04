<?php

namespace Lunar\Admin\Tests\Feature\Filament;

use Lunar\Admin\Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            \Barryvdh\DomPDF\ServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
