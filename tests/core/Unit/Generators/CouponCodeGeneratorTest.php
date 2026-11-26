<?php

use Lunar\Core\Contracts\CouponCodeGenerator;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('generates codes of the batch length from an alphabet without look-alikes', function () {
    $generator = app(CouponCodeGenerator::class);
    $batch = CouponBatch::ofQuantity(1, length: 12);

    $codes = collect(range(1, 500))->map(fn () => $generator->generate($batch));

    $codes->each(fn (string $code) => expect($code)
        ->toHaveLength(12)
        ->toMatch('/^[2-9A-HJKMNP-Z]+$/'));

    expect($codes->implode(''))->not->toMatch('/[01OIL]/')
        ->and($codes->unique())->toHaveCount(500);
});

test('puts the prefix, uppercased, before the generated characters', function () {
    $code = app(CouponCodeGenerator::class)->generate(CouponBatch::ofQuantity(1, length: 6, prefix: ' spring- '));

    expect($code)->toStartWith('SPRING-')
        ->and(strlen($code))->toBe(strlen('SPRING-') + 6);
});
