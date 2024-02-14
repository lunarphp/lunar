<?php

namespace Lunar\Tests\Stripe\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Tests\Stripe\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }
}
