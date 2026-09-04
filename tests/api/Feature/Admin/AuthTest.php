<?php

use Lunar\Api\Models\ApiKey;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
});

test('requests without a valid bearer token are unauthenticated', function (): void {
    $this->getJson('/api/admin/v1/products')
        ->assertUnauthorized()
        ->assertJsonPath('errors.0.code', 'unauthenticated');

    $this->withHeader('Authorization', 'Bearer nope')
        ->getJson('/api/admin/v1/products')
        ->assertUnauthorized();
});

test('revoked and expired keys are unauthenticated', function (): void {
    $revoked = $this->apiKey();
    $revoked['key']->revoke();

    $this->withHeaders($revoked['headers'])->getJson('/api/admin/v1/products')->assertUnauthorized();

    $expiring = ApiKey::generate('Expiring', ['*'], null, now()->addDay());

    $this->withHeader('Authorization', 'Bearer '.$expiring->plainTextToken)->getJson('/api/admin/v1/products')->assertOk();

    $this->travel(2)->days();

    $this->withHeader('Authorization', 'Bearer '.$expiring->plainTextToken)->getJson('/api/admin/v1/products')->assertUnauthorized();
});

test('abilities gate each endpoint and the wildcard grants everything', function (): void {
    $reader = $this->apiKey(['catalog:read']);

    $this->withHeaders($reader['headers'])->getJson('/api/admin/v1/products')->assertOk();

    $this->withHeaders($reader['headers'])
        ->getJson('/api/admin/v1/api-keys')
        ->assertForbidden()
        ->assertJsonPath('errors.0.code', 'forbidden');

    $admin = $this->apiKey(['*']);

    $this->withHeaders($admin['headers'])->getJson('/api/admin/v1/api-keys')->assertOk();
});

test('use is recorded on the key', function (): void {
    $key = $this->apiKey(['catalog:read']);

    expect($key['key']->last_used_at)->toBeNull();

    $this->withHeaders($key['headers'])->getJson('/api/admin/v1/products')->assertOk();

    expect($key['key']->fresh()->last_used_at)->not->toBeNull();
});
