<?php

use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

test('the customer area is not registered without a storefront guard', function (): void {
    $this->setUpStore();

    $this->getJson('/api/storefront/v1/me')
        ->assertNotFound()
        ->assertJsonPath('errors.0.code', 'not_found');
});
