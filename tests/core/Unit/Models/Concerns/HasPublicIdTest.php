<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a public_id is minted on create', function () {
    $brand = Brand::factory()->create(['public_id' => null]);

    expect($brand->public_id)->not->toBeNull();
    expect(Str::isUlid($brand->public_id))->toBeTrue();
});

test('an explicitly supplied public_id is preserved', function () {
    $ulid = (string) Str::ulid();

    $brand = Brand::factory()->create(['public_id' => $ulid]);

    expect($brand->public_id)->toBe($ulid);
});

test('the wherePublicId scope resolves by external id', function () {
    $brand = Brand::factory()->create();
    Brand::factory()->create();

    expect(Brand::wherePublicId($brand->public_id)->pluck('id')->all())
        ->toBe([$brand->id]);

    expect(Brand::wherePublicId([$brand->public_id])->count())->toBe(1);
});

test('a replica receives a fresh public_id', function () {
    $brand = Brand::factory()->create();

    $replica = $brand->replicate();
    $replica->save();

    expect($replica->public_id)->not->toBeNull();
    expect($replica->public_id)->not->toBe($brand->public_id);
});
