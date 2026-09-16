<?php

use Lunar\Api\Models\ApiKey;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->store = $this->setUpStore();
    $this->admin = $this->apiKey(['settings:manage-api-keys']);
});

test('issuing a key returns the plaintext token once', function (): void {
    $staff = Staff::factory()->create();

    $response = $this->withHeaders($this->admin['headers'])->postJson('/api/admin/v1/api-keys', [
        'name' => 'ERP sync',
        'abilities' => ['catalog:read', 'sales:read'],
        'staff_id' => $staff->public_id,
        'expires_at' => now()->addYear()->toIso8601String(),
    ])->assertCreated();

    $token = $response->json('data.token');
    $key = ApiKey::query()->where('name', 'ERP sync')->firstOrFail();

    expect($token)->toHaveLength(ApiKey::TOKEN_LENGTH);
    expect($response->json('data.token_prefix'))->toBe(substr($token, 0, 8));
    expect($response->json('data.abilities'))->toBe(['catalog:read', 'sales:read']);
    expect($response->json('data.staff_id'))->toBe($staff->public_id);
    expect($response->json('data.active'))->toBeTrue();
    expect($response->json('data'))->not->toHaveKey('token_hash');
    expect($key->token_hash)->toBe(ApiKey::hashToken($token));

    // The issued key works and is scoped to its abilities.
    $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/admin/v1/products')->assertOk();
    $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/admin/v1/api-keys')->assertForbidden();

    // Listing never exposes the token.
    $list = $this->withHeaders($this->admin['headers'])->getJson('/api/admin/v1/api-keys?filter[name]=ERP sync')->assertOk();

    expect($list->json('data.0.id'))->toBe($key->public_id);
    expect($list->json('data.0'))->not->toHaveKey('token')->not->toHaveKey('token_hash');
});

test('unknown abilities and staff are rejected', function (): void {
    $response = $this->withHeaders($this->admin['headers'])->postJson('/api/admin/v1/api-keys', [
        'name' => 'Bad',
        'abilities' => ['catalog:read', 'launch:rockets'],
        'staff_id' => 'nope',
    ])->assertStatus(422);

    expect($response->json('errors.*.source.pointer'))->toContain('/abilities/1', '/staff_id');
    expect($response->json('errors.0.code'))->toBe('validation_failed');
});

test('revoking a key keeps it listed but stops it authenticating', function (): void {
    $victim = $this->apiKey(['catalog:read']);

    $this->withHeaders($this->admin['headers'])
        ->deleteJson("/api/admin/v1/api-keys/{$victim['key']->public_id}")
        ->assertNoContent();

    expect($victim['key']->fresh()->revoked_at)->not->toBeNull();

    $this->withHeaders($victim['headers'])->getJson('/api/admin/v1/products')->assertUnauthorized();

    $this->withHeaders($this->admin['headers'])
        ->getJson("/api/admin/v1/api-keys/{$victim['key']->public_id}")
        ->assertOk()
        ->assertJsonPath('data.active', false);

    expect($this->withHeaders($this->admin['headers'])->getJson('/api/admin/v1/api-keys?filter[active]=1')->json('data.*.id'))
        ->toBe([$this->admin['key']->public_id]);
});

test('the console command creates, lists and revokes keys', function (): void {
    $staff = Staff::factory()->create(['email' => 'ops@example.com']);

    $this->artisan('lunar:api:key', ['action' => 'create', '--name' => 'Console key', '--ability' => ['catalog:read'], '--staff' => 'ops@example.com', '--expires' => 30])
        ->expectsOutputToContain('Token (shown once)')
        ->assertSuccessful();

    $key = ApiKey::query()->where('name', 'Console key')->firstOrFail();

    expect($key->abilities)->toBe(['catalog:read']);
    expect($key->staff_id)->toBe($staff->id);
    expect($key->expires_at)->not->toBeNull();

    $this->artisan('lunar:api:key', ['action' => 'list'])
        ->expectsOutputToContain('Console key')
        ->assertSuccessful();

    $this->artisan('lunar:api:key', ['action' => 'create', '--name' => 'Bad', '--ability' => ['launch:rockets']])
        ->assertFailed();

    $this->artisan('lunar:api:key', ['action' => 'revoke', '--key' => $key->public_id])
        ->assertSuccessful();

    expect($key->fresh()->revoked_at)->not->toBeNull();
});
