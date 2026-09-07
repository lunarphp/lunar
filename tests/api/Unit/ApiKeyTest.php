<?php

use Illuminate\Support\Facades\Gate;
use Lunar\Api\Models\ApiKey;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

test('generating a key stores only the hash and exposes the plaintext once', function (): void {
    $staff = Staff::factory()->create();
    $issued = ApiKey::generate('Sync', ['catalog:read', 'catalog:read'], $staff, now()->addMonth());

    expect($issued->plainTextToken)->toHaveLength(ApiKey::TOKEN_LENGTH);
    expect($issued->key->token_hash)->toBe(hash('sha256', $issued->plainTextToken));
    expect($issued->key->token_prefix)->toBe(substr($issued->plainTextToken, 0, 8));
    expect($issued->key->abilities)->toBe(['catalog:read']);
    expect($issued->key->staff->is($staff))->toBeTrue();
    expect($issued->key->toArray())->not->toHaveKey('token_hash');

    expect(ApiKey::findByToken($issued->plainTextToken)?->is($issued->key))->toBeTrue();
    expect(ApiKey::findActiveByToken($issued->plainTextToken)?->is($issued->key))->toBeTrue();
    expect(ApiKey::findActiveByToken('nope'))->toBeNull();
});

test('revoked and expired keys are inactive', function (): void {
    $revoked = ApiKey::factory()->revoked()->create();
    $expired = ApiKey::factory()->expired()->create();
    $active = ApiKey::factory()->create();

    expect($revoked->isActive())->toBeFalse();
    expect($expired->isActive())->toBeFalse();
    expect($active->isActive())->toBeTrue();
    expect(ApiKey::query()->active()->pluck('id')->all())->toBe([$active->id]);
});

test('abilities answer the gate directly and through the panel vocabulary', function (): void {
    $reader = ApiKey::factory()->abilities(['catalog:read'])->create();
    $admin = ApiKey::factory()->abilities(['*'])->create();

    expect($reader->hasAbility('catalog:read'))->toBeTrue();
    expect($reader->hasAbility('sales:read'))->toBeFalse();
    expect($reader->hasPermissionTo('catalog:read'))->toBeTrue();
    expect($reader->admin)->toBeFalse();

    expect($admin->hasAbility('anything'))->toBeTrue();
    expect($admin->admin)->toBeTrue();

    expect(Gate::forUser($reader)->allows('catalog:read'))->toBeTrue();
    expect(Gate::forUser($reader)->allows('catalog:manage-products'))->toBeFalse();
    expect(Gate::forUser($admin)->allows('catalog:manage-products'))->toBeTrue();
});

test('use is recorded at most once a minute', function (): void {
    $key = ApiKey::factory()->create();

    $key->markUsed();
    $first = $key->fresh()->last_used_at;

    $this->travel(30)->seconds();
    $key->markUsed();

    expect($key->fresh()->last_used_at->equalTo($first))->toBeTrue();

    $this->travel(31)->seconds();
    $key->markUsed();

    expect($key->fresh()->last_used_at->gt($first))->toBeTrue();
});
