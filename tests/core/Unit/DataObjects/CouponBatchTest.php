<?php

use Illuminate\Support\Str;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('a quantity batch has the documented defaults', function () {
    $batch = CouponBatch::ofQuantity(50);

    expect($batch->quantity)->toBe(50)
        ->and($batch->emails)->toBe([])
        ->and($batch->isForEmails())->toBeFalse()
        ->and($batch->length)->toBe(10)
        ->and($batch->prefix)->toBe('')
        ->and($batch->maxUses)->toBe(1)
        ->and(Str::isUlid($batch->id))->toBeTrue();
});

test('an email batch keeps one normalized entry per address', function () {
    $batch = CouponBatch::forEmails([' Jo@Example.com', 'jo@example.com ', '', null, 'sam@example.com']);

    expect($batch->emails)->toBe(['jo@example.com', 'sam@example.com'])
        ->and($batch->quantity)->toBe(2)
        ->and($batch->isForEmails())->toBeTrue();
});

test('a quantity batch needs at least one code', function () {
    CouponBatch::ofQuantity(0);
})->throws(InvalidArgumentException::class);

test('an email batch needs at least one address', function () {
    CouponBatch::forEmails(['', ' ']);
})->throws(InvalidArgumentException::class);
