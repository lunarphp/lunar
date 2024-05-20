<?php

namespace Lunar\Tests\Admin\Feature\Filament;

use Lunar\Tests\Admin\TestCase as BaseTestCase;

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
