<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Tag;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('tag values are stored uppercased regardless of input casing', function () {
    $tag = Tag::create(['value' => 'mixedCase']);

    expect($tag->value)->toBe('MIXEDCASE');

    expect(Tag::find($tag->id)->value)->toBe('MIXEDCASE');
});

test('null tag values are not coerced', function () {
    $tag = new Tag(['value' => null]);

    expect($tag->value)->toBeNull();
});
