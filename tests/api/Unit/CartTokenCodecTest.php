<?php

use Lunar\Api\Support\HmacCartTokenCodec;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Tests\Api\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);

    $this->codec = new HmacCartTokenCodec('base64:'.base64_encode(random_bytes(32)), 7);
    $this->cart = Cart::factory()->create();
});

test('a token round-trips to the cart public id', function (): void {
    $token = $this->codec->encode($this->cart);

    expect($token)->not->toContain('=', '+', '/');
    expect($this->codec->decode($token))->toBe($this->cart->public_id);
});

test('a tampered or foreign token does not decode', function (): void {
    $token = $this->codec->encode($this->cart);
    $other = new HmacCartTokenCodec('other-key', 7);

    expect($other->decode($token))->toBeNull();
    expect($this->codec->decode(substr($token, 0, -2).'zz'))->toBeNull();
    expect($this->codec->decode('garbage'))->toBeNull();
    expect($this->codec->decode(''))->toBeNull();

    // The signature covers the id, so swapping the id in a valid token fails.
    $decoded = base64_decode(strtr($token, '-_', '+/'));
    [$id, $expiry, $signature] = explode('.', $decoded);
    $forged = rtrim(strtr(base64_encode("01J000000000000000000000FORGED.{$expiry}.{$signature}"), '+/', '-_'), '=');

    expect($this->codec->decode($forged))->toBeNull();
});

test('an expired token does not decode', function (): void {
    $token = $this->codec->encode($this->cart);

    $this->travel(8)->days();

    expect($this->codec->decode($token))->toBeNull();
});
